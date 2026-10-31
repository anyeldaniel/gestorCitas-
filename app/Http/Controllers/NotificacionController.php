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
            ]);

        return response()->json(['count' => $count, 'items' => $items]);
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