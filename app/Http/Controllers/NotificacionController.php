<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function lista(Request $request)
    {
        $user = $request->user();

        $count = $user->notificacionesNoLeidas()->count();

        $items = $user->notificaciones()
            ->limit(15)
            ->get()
            ->map(fn (Notificacion $n) => [
                'id'      => $n->id,
                'tipo'    => $n->tipo,
                'icono'   => $n->icono(),
                'color'   => $n->color(),
                'titulo'  => $n->titulo,
                'mensaje' => $n->mensaje,
                'leida'   => !is_null($n->leida_at),
                'hace'    => $n->created_at->diffForHumans(),
                'url'     => $this->generarUrl($n, $user),
                'rol'     => $user->rol, // 👈 Enviamos el rol al frontend
            ]);

        return response()->json(['count' => $count, 'items' => $items]);
    }

    
    private function generarUrl(Notificacion $n, $user): ?string
    {
        $fechaParametro = null;
        
        // Extraemos la fecha del mensaje (ej. "27 de noviembre") de forma segura
        if (!empty($n->mensaje) && preg_match('/(\d{1,2}) de (enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)/i', $n->mensaje, $matches)) {
            $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT); // Asegura que el día tenga 2 dígitos (ej. 07)
            $meses = [
                'enero'=>'01', 'febrero'=>'02', 'marzo'=>'03', 'abril'=>'04', 'mayo'=>'05', 'junio'=>'06', 
                'julio'=>'07', 'agosto'=>'08', 'septiembre'=>'09', 'octubre'=>'10', 'noviembre'=>'11', 'diciembre'=>'12'
            ];
            $mes = $meses[strtolower($matches[2])] ?? null;
            
            if ($mes) {
                // Armamos la fecha en formato YYYY-MM-DD (Ej: 2026-11-27)
                $fechaParametro = date('Y') . "-" . $mes . "-" . $dia;
            }
        }

        return match ($n->tipo) {
            'cita_cancelada', 'cita_confirmada', 'cita_en_curso', 'cita_completada', 'cita_nueva' => 
                $user->rol === 'cliente' 
                    ? route('clientes.mis.reservas') 
                    // Si encontramos fecha, se la pasamos a la agenda. Si no, va normal.
                    : ($fechaParametro ? route('agenda', ['fecha' => $fechaParametro]) : route('agenda')),
            
            'reembolso_por_definir' => route('pago.verificar'),
            
            default => null,
        };
    }

    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        abort_unless($notificacion->user_id === $request->user()->id, 403);

        $notificacion->marcarLeida();

        return response()->json(['ok' => true]);
    }

    public function marcarTodas(Request $request)
    {
        $request->user()->notificacionesNoLeidas()->update(['leida_at' => now()]);

        return response()->json(['ok' => true]);
    }
}