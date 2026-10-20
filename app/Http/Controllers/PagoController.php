<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Pago;

class PagoController extends Controller
{
    public function index()
    {
        // Traemos todos los pagos para mostrarlos como historial administrativo
        $pagos = Pago::with(['cita.cliente', 'cita.servicio'])
                    ->orderBy('created_at', 'DESC')
                    ->get();

        return view('recepcion.verificar-pagos', compact('pagos'));
    }

    // Método para aprobar los pagos pendientes de la recepcionista
    public function aprobarPago($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->update(['estado_pago' => 'aprobado']);

        // Actualizamos también el estado de la cita asociada a confirmada
        $pago->cita->update(['estado' => 'confirmada']);

        return back()->with('success', 'Pago aprobado y cita confirmada exitosamente.');
    }

    // Se muestra la pantalla de cobro al cliente.
    public function checkout($cita_id)
    {
        $cita = Cita::findOrFail($cita_id);

        // Evitamos que paguen una cita que ya ha sido pagada o que fue cancelada.
        if ($cita->estado !== 'pendiente') {
            return redirect()->route('clientes.reserva')->withErrors(['error' => 'Esta cita ya fue procesada.']);
        }

        return view('clientes.checkout', compact('cita'));
    }

    public function procesarPago(Request $request)
    {
        $request->validate([
            'cita_id' => 'required|exists:citas,id',
            'metodo_pago' => 'required|in:c2p,transferencia,divisas_efectivo',
            'monto' => 'required|numeric|min:1'
        ]);

        $cita = Cita::findOrFail($request->cita_id);
        $estado_pago = 'revision'; // Estado por defecto según tu BD

        // --- Validación Automática: Pago Móvil ---
        if ($request->metodo_pago === 'c2p') {
            $request->validate([
                'banco' => 'required|string',
                'telefono' => 'required|string',
                'token' => 'required|string|size:6'
            ]);

            $validacion_banco = $this->simularApiC2P($request->banco, $request->telefono, $request->token);

            if ($validacion_banco['status'] === 'aprobado') {
                $estado_pago = 'aprobado'; // Coincide con el ENUM de tu BD
            } else {
                return back()->withErrors(['pago_fallido' => 'Transacción C2P rechazada: ' . $validacion_banco['mensaje']]);
            }
        }

        // --- Validación Manual: Transferencia ---
        elseif ($request->metodo_pago === 'transferencia') {
            $request->validate(['referencia' => 'required|string|min:4']);
            $estado_pago = 'revision';
        }

        // --- Validación Manual: DIVISAS/EFECTIVO ---
        elseif ($request->metodo_pago === 'divisas_efectivo') {
            $estado_pago = 'revision';
        }

        // Registrar el Pago con las columnas exactas de tu tabla 'pagos'
        Pago::create([
            'cita_id' => $cita->id,
            'monto_anticipado' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'banco_origen' => $request->banco ?? null,
            'referencia' => $request->referencia ?? ($request->metodo_pago === 'c2p' ? 'C2P-AUTO' : null),
            'telefono_pago' => $request->telefono ?? null,
            'estado_pago' => $estado_pago
        ]);

        // Actualizar la cita si el pago fue aprobado
        if ($estado_pago === 'aprobado') {
            $cita->update(['estado' => 'confirmada']); // Coincide con el ENUM de tu tabla citas
            $mensaje = 'Pago C2P aprobado. Tu cita está confirmada.';
        } else {
            $mensaje = 'Datos enviados. La recepción verificará tu pago en breve.';
        }

        return redirect()->route('clientes.reserva')->with('success', $mensaje);
    }

    // --- Simulación de la API del "Banco Falso" para pruebas de C2P ---
    private function simularApiC2P($banco, $telefono, $token)
    {
        // Para pruebas: Si el token es "123456", simulamos que el banco aprobó el dinero.
        if ($token === '123456') {
            return ['status' => 'aprobado', 'mensaje' => 'Operación exitosa'];
        }

        // Cualquier otro token lanza un error de fondos insuficientes
        return ['status' => 'rechazado', 'mensaje' => 'Fondos insuficientes o token inválido'];
    }
}