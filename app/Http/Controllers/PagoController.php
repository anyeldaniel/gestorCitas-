<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * ============================================================
     * Panel admin/recepción para verificar pagos.
     * Trae todos los pagos con sus relaciones para mostrarlos.
     * ============================================================
     */
    public function index()
    {
        $pagos = Pago::with(['cita.cliente', 'cita.servicio'])
                     ->orderBy('created_at', 'DESC')
                     ->get();

        return view('recepcion.verificar-pagos', compact('pagos'));
    }

    /**
     * Alias de compatibilidad para panel admin.
     */
    public function mostrarVistaAdmin()
    {
        return $this->index();
    }

    /**
     * Aprobar un pago pendiente desde la recepción.
     */
    public function aprobarPago($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->update(['estado_pago' => 'aprobado']);
        $pago->cita->update(['estado' => 'confirmada']);

        return back()->with('success', 'Pago aprobado y cita confirmada exitosamente.');
    }

    /**
     * Mostrar la pantalla de cobro al cliente.
     */
    public function checkout($cita_id)
    {
        $cita = Cita::with(['servicio', 'trabajador'])->findOrFail($cita_id);

        // Seguridad: solo el dueño de la cita puede pagarla
        if ($cita->cliente_id !== auth()->id()) {
            abort(403, 'No puedes pagar esta cita.');
        }

        // Si ya fue procesada, redirigir al listado
        if ($cita->estado !== 'pendiente') {
            return redirect()->route('clientes.reserva')
                ->withErrors(['error' => 'Esta cita ya fue procesada.']);
        }

        return view('clientes.checkout', compact('cita'));
    }

    /**
     * Procesar el pago enviado por el cliente.
     */
    public function procesarPago(Request $request)
    {
        $request->validate([
            'cita_id'     => 'required|exists:citas,id',
            'metodo_pago' => 'required|in:c2p,transferencia,divisas_efectivo',
            'monto'       => 'required|numeric|min:1',
        ]);

        $cita = Cita::findOrFail($request->cita_id);
        $estado_pago = 'revision';

        // Validación automática: Pago Móvil (C2P)
        if ($request->metodo_pago === 'c2p') {
            $request->validate([
                'banco'    => 'required|string',
                'telefono' => 'required|string',
                'token'    => 'required|string|size:6',
            ]);

            $validacion_banco = $this->simularApiC2P($request->banco, $request->telefono, $request->token);

            if ($validacion_banco['status'] === 'aprobado') {
                $estado_pago = 'aprobado';
            } else {
                return back()->withErrors([
                    'pago_fallido' => 'Transacción C2P rechazada: ' . $validacion_banco['mensaje'],
                ]);
            }
        }
        // Transferencia bancaria: revisión manual
        elseif ($request->metodo_pago === 'transferencia') {
            $request->validate(['referencia' => 'required|string|min:4']);
            $estado_pago = 'revision';
        }
        // Divisas / efectivo: revisión manual
        elseif ($request->metodo_pago === 'divisas_efectivo') {
            $estado_pago = 'revision';
        }

        // Registrar el pago con las columnas de la tabla 'pagos'
        Pago::create([
            'cita_id'          => $cita->id,
            'monto_anticipado' => $request->monto,
            'metodo_pago'      => $request->metodo_pago,
            'banco_origen'     => $request->banco ?? null,
            'referencia'       => $request->referencia ?? ($request->metodo_pago === 'c2p' ? 'C2P-AUTO' : null),
            'telefono_pago'    => $request->telefono ?? null,
            'estado_pago'      => $estado_pago,
        ]);

        if ($estado_pago === 'aprobado') {
            $cita->update(['estado' => 'confirmada']);
            $mensaje = 'Pago C2P aprobado. Tu cita está confirmada.';
        } else {
            $mensaje = 'Datos enviados. La recepción verificará tu pago en breve.';
        }

        return redirect()->route('clientes.reserva')->with('success', $mensaje);
    }

    /**
     * Alias de compatibilidad: flujo anterior con $citaId en la URL.
     */
    public function procesar(Request $request, $citaId)
    {
        $request->merge(['cita_id' => $citaId]);
        return $this->procesarPago($request);
    }

    /**
     * Simulación del "banco falso" para pruebas de C2P.
     * Token "123456" → aprobado. Otros → rechazado.
     */
    private function simularApiC2P($banco, $telefono, $token)
    {
        if ($token === '123456') {
            return ['status' => 'aprobado', 'mensaje' => 'Operación exitosa'];
        }

        return ['status' => 'rechazado', 'mensaje' => 'Fondos insuficientes o token inválido'];
    }
}