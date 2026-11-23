<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pago;
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
    public function checkout(int $cita_id): View|RedirectResponse
    {
        $cita = Cita::with(['servicios', 'trabajador', 'servicio'])->findOrFail($cita_id);

        // Seguridad: solo el dueño de la cita puede pagarla
        if ($cita->cliente_id !== auth()->id()) {
            abort(403, 'No puedes pagar esta cita.');
        }

        // Si ya fue procesada, redirigir al listado
        if ($cita->estado !== 'pendiente') {
            return redirect()
                ->route('clientes.reserva')
                ->withErrors(['error' => 'Esta cita ya fue procesada.']);
        }

        // ---- Lógica para Múltiples Servicios y Especialistas ----
        $especialistasIds = [];

        if ($cita->relationLoaded('servicios') && $cita->servicios->isNotEmpty()) {
            $servicios = $cita->servicios;
            $nombreServicio = $servicios->pluck('nombre_servicio')->join(' + ');
            $duracionTotal = $servicios->sum('duracion_minutos');
            
            // Recopilar los IDs de los especialistas desde la tabla pivote
            foreach ($servicios as $s) {
                if (isset($s->pivot->trabajador_id) && $s->pivot->trabajador_id) {
                    $especialistasIds[] = $s->pivot->trabajador_id;
                }
            }
        } else {
            $servicios = collect([$cita->servicio]);
            $nombreServicio = $cita->servicio->nombre_servicio ?? 'Servicio Spa';
            $duracionTotal = $cita->servicio->duracion_minutos ?? 60;
        }

        // Si no se encontraron especialistas en el pivote, usamos el principal de la cita
        if (empty($especialistasIds) && $cita->trabajador) {
            $especialistasIds[] = $cita->trabajador->id;
        }

        // Buscamos los nombres de los especialistas en el modelo User
        $especialistas = collect();
        if (!empty($especialistasIds)) {
            $especialistas = User::whereIn('id', array_unique($especialistasIds))->pluck('nombre');
        }

        // ---- Cálculos de montos ----
        $porcentaje     = $cita->servicio->porcentaje_abono ?? 10;
        $montoTotal     = (float) $cita->monto_total;
        $montoAbono     = round($montoTotal * $porcentaje / 100, 2);
        $montoRestante  = round($montoTotal - $montoAbono, 2);
        $fecha          = Carbon::parse($cita->fecha);
        $hora           = Carbon::parse($cita->hora)->format('h:i A');
        $nombreServicio = $cita->servicio->nombre_servicio ?? 'Servicio Spa';

        return view('clientes.checkout', compact(
            'cita',
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
        $data = $request->validate([
            'cita_id'     => 'required|exists:citas,id',
            'metodo_pago' => 'required|in:c2p,transferencia,divisas_efectivo',
            'monto'       => 'required|numeric|min:1',
        ]);

        // Carga el servicio y el cliente para guardar el pago y dirigirle el correo.
        $cita = Cita::with(['servicio', 'cliente'])->findOrFail($data['cita_id']);

        if ($cita->cliente_id !== auth()->id()) {
            abort(403, 'No puedes pagar esta cita.');
        }

        $yaAprobado = Pago::where('cita_id', $cita->id)
            ->where('estado_pago', 'aprobado')
            ->exists();

        if ($yaAprobado) {
            return back()->withErrors([
                'error' => 'Esta cita ya tiene un pago aprobado.',
            ]);
        }

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

        // El numerito queda anotado, pero todavía falta que recepción dé el visto bueno.
        DB::transaction(function () use ($request, $cita, $data) {
            Pago::create([
                'cita_id'          => $cita->id,
                'monto_anticipado' => $data['monto'],
                'metodo_pago'      => $data['metodo_pago'],
                'banco_origen'     => $request->banco ?? null,
                'referencia'       => $data['metodo_pago'] === 'c2p'
                    ? $request->token
                    : ($request->referencia ?? null),
                'telefono_pago'    => $request->telefono ?? null,
                'estado_pago'      => 'revision',
            ]);
        });

        // Envía el aviso una vez guardado el pago correctamente.
        Mail::to($cita->cliente->correo)->send(new CitaPendienteConfirmacion($cita));

        return redirect()
            ->route('clientes.reserva')
            ->with('success', 'Datos enviados. La recepción verificará tu pago en breve.');
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