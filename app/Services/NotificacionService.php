<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Notificacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NotificacionService
{
    /* ============ Helpers ============ */

    private static function adminsYRecepcion(): Collection
    {
        return User::whereIn('rol', ['admin', 'recepcion'])->get();
    }

    /**
     * Destinatarios internos: admin + recepción + trabajador asignado a la cita.
     */
    private static function destinatariosInternos(Cita $cita): Collection
    {
        return self::adminsYRecepcion()
            ->when($cita->trabajador, fn ($c) => $c->push($cita->trabajador))
            ->filter()
            ->unique('id')
            ->values();
    }

    private static function formatearCita(Cita $cita): string
    {
        $servicio = $cita->servicio->nombre_servicio ?? 'el servicio';
        $fecha    = Carbon::parse($cita->fecha)->locale('es')->translatedFormat('d \d\e F');
        $hora     = Carbon::parse($cita->hora)->format('H:i');

        return "{$servicio} · {$fecha} a las {$hora}";
    }

    private static function crearPara(Collection $usuarios, string $tipo, string $titulo, string $mensaje, array $data = []): void
    {
        foreach ($usuarios as $u) {
            Notificacion::create([
                'user_id' => $u->id,
                'tipo'    => $tipo,
                'titulo'  => $titulo,
                'mensaje' => $mensaje,
                'data'    => $data,
            ]);
        }
    }

    /* ============ Eventos ============ */

   /**
 * Nueva cita agendada → todos los trabajadores asignados + admin + recepción.
 */
public static function citaNueva(Cita $cita): void
{
    // Recolecta todos los trabajadores de la tabla pivote cita_servicio
    $trabajadoresPivote = $cita->servicios()
        ->pluck('cita_servicio.trabajador_id')
        ->filter()
        ->unique()
        ->values();

    $trabajadores = User::whereIn('id', $trabajadoresPivote)->get();

    // Si no hay pivote (por si acaso), usa el trabajador principal
    if ($trabajadores->isEmpty() && $cita->trabajador) {
        $trabajadores = collect([$cita->trabajador]);
    }

    $destinatarios = self::adminsYRecepcion()
        ->merge($trabajadores)
        ->filter()
        ->unique('id')
        ->values();

    $clienteNombre = $cita->cliente->nombre ?? 'un cliente';

    self::crearPara(
        $destinatarios,
        'cita_nueva',
        'Nueva cita agendada',
        "{$clienteNombre} agendó " . self::formatearCita($cita),
        ['cita_id' => $cita->id]
    );
}

    /**
     * Cita confirmada → cliente + trabajador.
     */
    public static function citaConfirmada(Cita $cita): void
    {
        $destinatarios = collect([$cita->cliente, $cita->trabajador])
            ->filter()
            ->unique('id');

        self::crearPara(
            $destinatarios,
            'cita_confirmada',
            'Cita confirmada',
            'Tu cita de ' . self::formatearCita($cita) . ' ha sido confirmada.',
            ['cita_id' => $cita->id]
        );
    }

   /**
 * Cita cancelada → cliente + TODOS los trabajadores asignados + admin + recepción.
 */
public static function citaCancelada(Cita $cita, ?string $motivo = null): void
{
    // Todos los trabajadores del pivote (multi-servicio)
    $trabajadoresPivote = $cita->servicios()
        ->pluck('cita_servicio.trabajador_id')
        ->filter()
        ->unique()
        ->values();

    $trabajadores = User::whereIn('id', $trabajadoresPivote)->get();

    // Fallback si no hay pivote
    if ($trabajadores->isEmpty() && $cita->trabajador) {
        $trabajadores = collect([$cita->trabajador]);
    }

    $destinatarios = collect([$cita->cliente])
        ->merge($trabajadores)
        ->merge(self::adminsYRecepcion())
        ->filter()
        ->unique('id')
        ->values();

    $motivoTexto = $motivo ? " Motivo: {$motivo}" : '';

    self::crearPara(
        $destinatarios,
        'cita_cancelada',
        'Cita cancelada',
        'La cita de ' . self::formatearCita($cita) . ' fue cancelada.' . $motivoTexto,
        ['cita_id' => $cita->id, 'motivo' => $motivo]
    );
}

   /**
 * Cambio de estado genérico → cliente + TODOS los trabajadores asignados.
 */
public static function cambioEstado(Cita $cita, string $nuevoEstado): void
{
    $tipo = match ($nuevoEstado) {
        'confirmada' => 'cita_confirmada',
        'en curso'   => 'cita_en_curso',
        'completada' => 'cita_completada',
        default      => null,
    };

    if (!$tipo) return;

    $titulo = match ($nuevoEstado) {
        'confirmada' => 'Cita confirmada',
        'en curso'   => 'Tu cita está en curso',
        'completada' => 'Cita completada',
    };

    // Todos los trabajadores del pivote
    $trabajadoresPivote = $cita->servicios()
        ->pluck('cita_servicio.trabajador_id')
        ->filter()
        ->unique()
        ->values();

    $trabajadores = User::whereIn('id', $trabajadoresPivote)->get();

    if ($trabajadores->isEmpty() && $cita->trabajador) {
        $trabajadores = collect([$cita->trabajador]);
    }

    $destinatarios = collect([$cita->cliente])
        ->merge($trabajadores)
        ->filter()
        ->unique('id')
        ->values();

    self::crearPara(
        $destinatarios,
        $tipo,
        $titulo,
        self::formatearCita($cita),
        ['cita_id' => $cita->id, 'estado' => $nuevoEstado]
    );
}
}