<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Pago;

class PagoController extends Controller
{
    /**
     * ============================================================
     * Endpoint de prueba (ya lo tenías)
     * ============================================================
     */
    public function index()
    {
        return response()->json([
            'status'  => 'success',
            'modulo'  => 'Verificar Pagos',
            'mensaje' => 'El backend de Verificar Pagos responde correctamente',
        ]);
    }

    /**
     * ============================================================
     * Vista del panel admin/recepción para verificar pagos
     * (ya lo tenías)
     * ============================================================
     */
    public function mostrarVistaAdmin()
    {
        return view('compartidas.verificar-pago');
    }

    /**
     * ============================================================
     * FASE A — Checkout de pago (para el cliente)
     * Muestra la pantalla donde el cliente reporta su pago
     * ============================================================
     */
    public function checkout($citaId)
    {
        $cita = Cita::with(['servicio', 'trabajador'])->findOrFail($citaId);

        // Seguridad: solo el dueño de la cita puede pagarla
        if ($cita->cliente_id !== auth()->id()) {
            abort(403, 'No puedes pagar esta cita.');
        }

        // Si ya está confirmada, redirigir al catálogo
        if ($cita->estado === 'confirmada') {
            return redirect()->route('catalogo')
                ->with('success', 'Esta cita ya estaba confirmada.');
        }

        // Calcular el abono (10% del servicio)
        $porcentaje = 10;
        $montoTotal = $cita->monto_total ?? 0;
        $montoAbono = round(($montoTotal * $porcentaje) / 100, 2);

        return view('clientes.pago-checkout', compact('cita', 'montoTotal', 'montoAbono', 'porcentaje'));
    }

    /**
     * ============================================================
     * FASE A — Procesa el pago (placeholder por ahora)
     * En FASE B, aquí guardaremos el pago en la tabla
     * ============================================================
     */
    public function procesar(Request $request, $citaId)
    {
        $request->validate([
            'metodo'     => 'required|in:pago_movil,transferencia',
            'referencia' => 'required|string|min:4|max:50',
            'banco'      => 'nullable|string|max:100',
            'telefono'   => 'nullable|string|max:20',
            'cedula'     => 'nullable|string|max:20',
            'comprobante'=> 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // TODO: FASE B — Guardar el pago en la BD aquí
        // Por ahora solo redirigimos al catálogo con un mensaje

        return redirect()->route('catalogo')
            ->with('success', '✨ Hemos recibido los datos de tu pago. Pronto lo verificaremos.');
    }
}
