<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $pagos = Pago::with(['cita.cliente', 'cita.servicio'])
            ->orderByDesc('created_at')
            ->get();

        return view('recepcion.verificar-pagos', compact('pagos'));
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
        $pago = Pago::findOrFail($id);

        DB::transaction(function () use ($pago) {
            $pago->update(['estado_pago' => 'aprobado']);
            $pago->cita->update(['estado' => 'confirmada']);
        });

        return back()->with('success', 'Pago aprobado y cita confirmada exitosamente.');
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

        $cita = Cita::with('servicio')->findOrFail($data['cita_id']);

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
                'token'    => 'required|string|size:6',
            ]);

            $resultado = $this->simularApiC2P(
                $request->banco,
                $request->telefono,
                $request->token,
            );

            if ($resultado['status'] !== 'aprobado') {
                return back()->withErrors([
                    'pago_fallido' => 'Transacción C2P rechazada: ' . $resultado['mensaje'],
                ]);
            }

            $estadoPago = 'aprobado';
        } elseif ($data['metodo_pago'] === 'transferencia') {
            $request->validate(['referencia' => 'required|string|min:4']);
            $estadoPago = 'revision';
        } else {
            $estadoPago = 'revision';
        }

        DB::transaction(function () use ($request, $cita, $data, $estadoPago) {
            Pago::create([
                'cita_id'          => $cita->id,
                'monto_anticipado' => $data['monto'],
                'metodo_pago'      => $data['metodo_pago'],
                'banco_origen'     => $request->banco ?? null,
                'referencia'       => $request->referencia
                    ?? ($data['metodo_pago'] === 'c2p' ? 'C2P-AUTO' : null),
                'telefono_pago'    => $request->telefono ?? null,
                'estado_pago'      => $estadoPago,
            ]);

            if ($estadoPago === 'aprobado') {
                $cita->update(['estado' => 'confirmada']);
            }
        });

        $mensaje = $estadoPago === 'aprobado'
            ? 'Pago C2P aprobado. Tu cita está confirmada.'
            : 'Datos enviados. La recepción verificará tu pago en breve.';

        return redirect()->route('clientes.reserva')->with('success', $mensaje);
    }

    public function procesar(Request $request, int $citaId): RedirectResponse
    {
        $request->merge(['cita_id' => $citaId]);

        return $this->procesarPago($request);
    }

    private function simularApiC2P(string $banco, string $telefono, string $token): array
    {
        if ($token === '123456') {
            return ['status' => 'aprobado', 'mensaje' => 'Operación exitosa'];
        }

        return ['status' => 'rechazado', 'mensaje' => 'Fondos insuficientes o token inválido'];
    }
}