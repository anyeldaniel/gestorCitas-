<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\User;
use App\Mail\CitaConfirmada;
use App\Mail\CitaCancelada;
use App\Mail\CitaPendienteConfirmacion;
use App\Mail\ReembolsoDefinido;
use App\Mail\ReembolsoPorDefinir;
use App\Notifications\CitaCanceladaNotification;
use App\Services\CancelacionCitaService;
use App\Services\NotificacionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PagoController extends Controller
{
    /**
     * ============================================================
     * Panel admin/recepción para verificar pagos.
     * ============================================================
     */
    public function index(): View
    {
        abort_unless(in_array(auth()->user()->rol, ['admin', 'recepcion']), 403);

        $pagos = Pago::with(['cita.cliente', 'cita.servicio', 'cita.pagos'])
            ->orderByDesc('created_at')
            ->get();

        return view('compartidas.verificar-pago', compact('pagos'));
    }

    /**
     * Alias de compatibilidad para panel admin.
     */
    public function mostrarVistaAdmin(): View
    {
        return $this->index();
    }

    /**
     * Aprobar un pago pendiente desde la recepción.
     */
    public function aprobarPago(int $id): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->rol, ['admin', 'recepcion']), 403);

        // Revisamos el pago y dejamos la cita lista en el mismo paso, sin medias tintas.
        $citaConfirmada = DB::transaction(function () use ($id) {
            $pago = Pago::query()->findOrFail($id);
            $cita = Cita::query()->lockForUpdate()->findOrFail($pago->cita_id);
            $pago = Pago::query()->lockForUpdate()->findOrFail($id);

            // Si ya lo habían revisado, no repetimos la confirmación ni mandamos otro correo.
            if ($pago->estado_pago !== 'revision') {
                return null;
            }

            if ($cita->estaCancelada()) {
                throw ValidationException::withMessages([
                    'pago' => 'No se puede aprobar un pago de una cita cancelada.',
                ]);
            }

            $montoAbonoEsperado = $this->calcularMontoAbono($cita->load('servicio'));
            $montoAbonoRegistradoEnCentimos = (int) round((float) $pago->monto_anticipado * 100);
            $montoAbonoEsperadoEnCentimos = (int) round($montoAbonoEsperado * 100);

            if ($montoAbonoRegistradoEnCentimos !== $montoAbonoEsperadoEnCentimos) {
                throw ValidationException::withMessages([
                    'pago' => 'No se puede aprobar el pago porque el monto del abono no coincide con el esperado.',
                ]);
            }

            $pago->update(['estado_pago' => 'aprobado']);
            $cita->update(['estado' => 'confirmada']);

            return $cita->load(['cliente', 'servicio']);
        });

        // Si ya estaba revisado, avisamos y no volvemos a hacer el proceso.
        if (!$citaConfirmada) {
            return back()->with('success', 'Este pago ya había sido revisado.');
        }

        // El correo sale después de guardar los cambios, para avisar que la cita ya quedó confirmada.
        Mail::to($citaConfirmada->cliente->correo)->send(new CitaConfirmada($citaConfirmada));

        return back()->with('success', 'Pago aprobado y cita confirmada exitosamente.');
    }

    public function rechazarPago(
        Request $request,
        int $id,
        CancelacionCitaService $cancelacionCita
    ): JsonResponse {
        abort_unless(in_array($request->user()->rol, ['admin', 'recepcion']), 403);

        $data = $request->validate([
            'motivo' => 'required|string|min:5|max:500',
            'reembolsos' => 'sometimes|array',
            'reembolsos.*' => 'required|numeric|decimal:0,2|min:0.01',
        ]);

        $resultado = $cancelacionCita->rechazarPago(
            $id,
            $request->user(),
            $data['motivo'],
            $data['reembolsos'] ?? []
        );
        $cita = $resultado['cita'];

        if ($resultado['recien_cancelada']) {
            NotificacionService::citaCancelada($cita, $data['motivo']);
            Mail::to($cita->cliente->correo)->send(new CitaCancelada($cita, $data['motivo']));

            try {
                $cita->cliente?->notify(new CitaCanceladaNotification($cita));
            } catch (\Exception $e) {
                report($e);
            }
        }

        if ($resultado['reembolso_por_definir'] && $resultado['recien_cancelada']) {
            $destinatarios = NotificacionService::reembolsoPorDefinir($cita);
            foreach ($destinatarios as $destinatario) {
                Mail::to($destinatario->correo)->send(new ReembolsoPorDefinir($cita));
            }
        }

        return response()->json([
            'ok' => true,
            'mensaje' => $resultado['recien_cancelada']
                ? 'Pago rechazado y cita cancelada.'
                : 'Pago rechazado. La cita ya estaba cancelada.',
        ]);
    }

    public function definirReembolso(Request $request, int $id): JsonResponse
    {
        abort_unless(in_array($request->user()->rol, ['admin', 'recepcion']), 403);

        $data = $request->validate([
            'monto_reembolso' => 'required|numeric|decimal:0,2|min:0.01',
        ]);

        $pago = DB::transaction(function () use ($id, $data) {
            $pago = Pago::query()->lockForUpdate()->findOrFail($id);

            if ($pago->estado_reembolso !== 'por_definir') {
                throw ValidationException::withMessages([
                    'pago' => 'Este reembolso ya fue definido o no requiere definición.',
                ]);
            }

            if ((float) $data['monto_reembolso'] > (float) $pago->monto_anticipado) {
                throw ValidationException::withMessages([
                    'monto_reembolso' => 'El importe no puede superar el pago aprobado.',
                ]);
            }

            $pago->update([
                'estado_reembolso' => 'pendiente',
                'monto_reembolso' => $data['monto_reembolso'],
            ]);

            return $pago->load(['cita.cliente', 'cita.servicio']);
        });

        Mail::to($pago->cita->cliente->correo)->send(new ReembolsoDefinido($pago));

        return response()->json([
            'ok' => true,
            'mensaje' => 'Importe de reembolso registrado y notificado al cliente por correo.',
        ]);
    }

    /**
     * Mostrar la pantalla de cobro al cliente.
     */
    public function checkout(): View|RedirectResponse
    {
        $reserva = session('reserva_pendiente');
        if (!$this->reservaPerteneceAlUsuario($reserva, (int) auth()->id())) {
            session()->forget('reserva_pendiente');

            return redirect()->route('clientes.reserva')->with(
                'error',
                'No hay una selección de cita pendiente. Selecciona una cita antes de continuar al pago.'
            );
        }

        $servicios = $this->serviciosDeReserva($reserva);
        $servicioPrincipal = $servicios->firstWhere('id', $reserva['servicio_id']);
        $trabajadoresIds = array_values(array_unique(array_map(
            'intval',
            $reserva['trabajadores_por_servicio']
        )));
        $trabajadores = User::whereIn('id', $trabajadoresIds)->get()->keyBy('id');

        if (!$servicioPrincipal || $servicios->count() !== count($reserva['trabajadores_por_servicio'])
            || $trabajadores->count() !== count($trabajadoresIds)) {
            session()->forget('reserva_pendiente');

            return redirect()->route('clientes.reserva')->with(
                'error',
                'La selección de la cita ya no está disponible. Vuelve a elegir los servicios y horarios.'
            );
        }

        $especialistas = collect($reserva['trabajadores_por_servicio'])
            ->map(fn ($trabajadorId) => $trabajadores->get((int) $trabajadorId)?->nombre)
            ->filter()
            ->values();
        $nombreServicio = $servicioPrincipal->nombre_servicio ?? 'Servicio Spa';
        $duracionTotal = $servicios->sum('duracion_minutos');
        $porcentaje = $servicioPrincipal->porcentaje_abono ?? 10;
        $montoTotal = (float) $servicios->sum('precio');
        $montoAbono = $this->calcularMontoAbonoDesdeDatos($montoTotal, $servicioPrincipal);
        $montoRestante = round($montoTotal - $montoAbono, 2);
        $fecha = Carbon::parse($reserva['fecha']);
        $hora = Carbon::parse($reserva['hora'])->format('h:i A');

        return view('clientes.checkout', compact(
            'servicios',
            'nombreServicio',
            'duracionTotal',
            'especialistas',
            'porcentaje',
            'montoTotal',
            'montoAbono',
            'montoRestante',
            'fecha',
            'hora',
        ));
    }

    /**
     * Procesar el pago enviado por el cliente.
     */
    public function procesarPago(Request $request): RedirectResponse
    {
        $reserva = session('reserva_pendiente');
        if (!$this->reservaPerteneceAlUsuario($reserva, (int) auth()->id())) {
            session()->forget('reserva_pendiente');

            return redirect()->route('clientes.reserva')->with(
                'error',
                'La selección de la cita expiró o no es válida. Vuelve a seleccionar una cita.'
            );
        }

        $data = $request->validate([
            'metodo_pago' => 'required|in:c2p,transferencia,divisas_efectivo',
        ]);

        if ($data['metodo_pago'] === 'c2p') {
            $request->validate([
                'banco'    => 'required|string',
                'telefono' => 'required|string',
                'token'    => 'required|digits:6',
            ]);


            // Antes se simulaba la respuesta del banco; ahora recepción revisa el pago manualmente.
            // $resultado = $this->simularApiC2P($request->banco, $request->telefono, $request->token);
            // if ($resultado['status'] !== 'aprobado') {
            //     return back()->withErrors([
            //         'pago_fallido' => 'Transacción C2P rechazada: ' . $resultado['mensaje'],
            //     ]);
            // }
            // $estadoPago = 'aprobado';



        } elseif ($data['metodo_pago'] === 'transferencia') {
            $request->validate(['referencia' => 'required|string|min:4']);
        }

        $servicios = $this->serviciosDeReserva($reserva);
        $servicioPrincipal = $servicios->firstWhere('id', $reserva['servicio_id']);
        $trabajadoresIds = array_values(array_unique(array_map(
            'intval',
            $reserva['trabajadores_por_servicio']
        )));
        $trabajadores = User::whereIn('id', $trabajadoresIds)->get()->keyBy('id');

        if (!$servicioPrincipal || $servicios->count() !== count($reserva['trabajadores_por_servicio'])
            || $trabajadores->count() !== count($trabajadoresIds)) {
            session()->forget('reserva_pendiente');

            return redirect()->route('clientes.reserva')->with(
                'error',
                'La selección de la cita ya no está disponible. Vuelve a elegir los servicios y horarios.'
            );
        }

        $montoTotal = (float) $servicios->sum('precio');
        $montoAbono = $this->calcularMontoAbonoDesdeDatos($montoTotal, $servicioPrincipal);

        try {
            $cita = DB::transaction(function () use (
                $request,
                $reserva,
                $data,
                $montoTotal,
                $montoAbono
            ) {
                User::query()
                    ->whereKey($reserva['trabajador_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $citaOcupada = Cita::query()
                    ->where('fecha', $reserva['fecha'])
                    ->where('hora', $reserva['hora'])
                    ->where('trabajador_id', $reserva['trabajador_id'])
                    ->whereIn('estado', ['pendiente', 'confirmada', 'revision'])
                    ->lockForUpdate()
                    ->first();

                if ($citaOcupada) {
                    throw ValidationException::withMessages([
                        'horario' => 'El horario seleccionado ya no está disponible. Elige otra fecha u hora.',
                    ]);
                }

                $cita = Cita::create([
                    'cliente_id'    => $reserva['cliente_id'],
                    'trabajador_id' => $reserva['trabajador_id'],
                    'servicio_id'   => $reserva['servicio_id'],
                    'cabina_id'     => null,
                    'monto_total'   => $montoTotal,
                    'fecha'         => $reserva['fecha'],
                    'hora'          => $reserva['hora'],
                    'estado'        => 'pendiente',
                ]);

                $serviciosPorTrabajador = [];
                foreach ($reserva['trabajadores_por_servicio'] as $servicioId => $trabajadorId) {
                    $serviciosPorTrabajador[$servicioId] = [
                        'trabajador_id' => $trabajadorId,
                    ];
                }
                $cita->servicios()->attach($serviciosPorTrabajador);

                Pago::create([
                    'cita_id'          => $cita->id,
                    'monto_anticipado' => $montoAbono,
                    'metodo_pago'      => $data['metodo_pago'],
                    'banco_origen'     => $request->banco ?? null,
                    'referencia'       => $data['metodo_pago'] === 'c2p'
                        ? $request->token
                        : ($request->referencia ?? null),
                    'telefono_pago'    => $request->telefono ?? null,
                    'estado_pago'      => 'revision',
                ]);

                return $cita->load(['cliente', 'servicio']);
            });
        } catch (ValidationException $exception) {
            if (!array_key_exists('horario', $exception->errors())) {
                throw $exception;
            }

            session()->forget('reserva_pendiente');

            return redirect()->route('clientes.reserva')->with(
                'error',
                $exception->errors()['horario'][0]
            );
        }

        session()->forget('reserva_pendiente');

        try {
            NotificacionService::citaNueva($cita);
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            $cliente = $cita->cliente;
            $cliente?->notify(new \App\Notifications\NuevaCitaNotification($cita));

            $terapeutasNotificar = array_unique(array_values($reserva['trabajadores_por_servicio']));
            foreach ($terapeutasNotificar as $terapeutaId) {
                $terapeuta = User::find($terapeutaId);
                $terapeuta?->notify(new \App\Notifications\NuevaCitaNotification($cita));
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            Mail::to($cita->cliente->correo)->send(new CitaPendienteConfirmacion($cita));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('clientes.reserva')
            ->with('success', 'Datos enviados. La recepción verificará tu pago en breve.');
    }

    private function calcularMontoAbono(Cita $cita): float
    {
        return $this->calcularMontoAbonoDesdeDatos((float) $cita->monto_total, $cita->servicio);
    }

    private function calcularMontoAbonoDesdeDatos(float $montoTotal, Servicio $servicio): float
    {
        $porcentaje = $servicio->porcentaje_abono ?? 10;

        return round($montoTotal * $porcentaje / 100, 2);
    }

    private function reservaPerteneceAlUsuario(mixed $reserva, int $userId): bool
    {
        return is_array($reserva)
            && (int) ($reserva['cliente_id'] ?? 0) === $userId
            && !empty($reserva['trabajadores_por_servicio'])
            && isset(
                $reserva['fecha'],
                $reserva['hora'],
                $reserva['trabajador_id'],
                $reserva['servicio_id']
            );
    }

    private function serviciosDeReserva(array $reserva)
    {
        $servicioIds = array_map('intval', array_keys($reserva['trabajadores_por_servicio']));

        return Servicio::query()->whereIn('id', $servicioIds)->get();
    }

    public function procesar(Request $request, int $citaId): RedirectResponse
    {
        $request->merge(['cita_id' => $citaId]);

        return $this->procesarPago($request);
    }

    // Esta era solo una prueba local; no confirmaba el pago con el banco.
    // private function simularApiC2P(string $banco, string $telefono, string $token): array
    // {
    //     if ($token === '123456') {
    //         return ['status' => 'aprobado', 'mensaje' => 'Operación exitosa'];
    //     }
    //
    //     return ['status' => 'rechazado', 'mensaje' => 'Fondos insuficientes o token inválido'];
    // }
}