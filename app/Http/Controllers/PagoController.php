<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pago;
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
     *
     * Todos los cálculos viven aquí, la vista solo pinta.
     */
    public function checkout(int $cita_id): View|RedirectResponse
    {
        $cita = Cita::with(['servicio', 'trabajador'])->findOrFail($cita_id);

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

        // ---- Cálculos que antes estaban en la vista ----
        $porcentaje     = $cita->servicio->porcentaje_abono ?? 10;
        $montoTotal     = (float) $cita->monto_total;
        $montoAbono     = round($montoTotal * $porcentaje / 100, 2);
        $montoRestante  = round($montoTotal - $montoAbono, 2);
        $fecha          = Carbon::parse($cita->fecha);
        $hora           = Carbon::parse($cita->hora)->format('h:i A');
        $nombreServicio = $cita->servicio->nombre_servicio ?? 'Servicio Spa';

        return view('clientes.checkout', compact(
            'cita',
            'porcentaje',
            'montoTotal',
            'montoAbono',
            'montoRestante',
            'fecha',
            'hora',
            'nombreServicio',
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

        // Seguridad: el usuario autenticado debe ser dueño de la cita
        if ($cita->cliente_id !== auth()->id()) {
            abort(403, 'No puedes pagar esta cita.');
        }

        // Evitar doble pago (sin tocar el modelo: consultamos Pago directamente)
        $yaAprobado = Pago::where('cita_id', $cita->id)
            ->where('estado_pago', 'aprobado')
            ->exists();

        if ($yaAprobado) {
            return back()->withErrors([
                'error' => 'Esta cita ya tiene un pago aprobado.',
            ]);
        }

        // ---- Validación según método ----
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
            // divisas_efectivo
            $estadoPago = 'revision';
        }

        // ---- Persistencia atómica ----
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

    /**
     * Alias de compatibilidad: flujo anterior con $citaId en la URL.
     */
    public function procesar(Request $request, int $citaId): RedirectResponse
    {
        $request->merge(['cita_id' => $citaId]);

        return $this->procesarPago($request);
    }

    /**
     * Simulación del "banco falso" para pruebas de C2P.
     * Token "123456" → aprobado. Otros → rechazado.
     *
     * @return array{status: string, mensaje: string}
     */
    private function simularApiC2P(string $banco, string $telefono, string $token): array
    {
        if ($token === '123456') {
            return ['status' => 'aprobado', 'mensaje' => 'Operación exitosa'];
        }

        return ['status' => 'rechazado', 'mensaje' => 'Fondos insuficientes o token inválido'];
    }
}