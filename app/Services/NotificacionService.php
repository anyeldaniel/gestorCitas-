<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NotificacionService
{
    // Helpers

    private static function adminsYRecepcion(): Collection
    {
        return User::whereIn('rol', ['admin', 'recepcion'])->get();
    }

    // destinatarios
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

    //Eventos

   // cita nueva agendada
public static function citaNueva(Cita $cita): void
{
    $trabajadoresPivote = $cita->servicios()
        ->pluck('cita_servicio.trabajador_id')
        ->filter()
        ->unique()
        ->values();

    $trabajadores = User::whereIn('id', $trabajadoresPivote)->get();

    if ($trabajadores->isEmpty() && $cita->trabajador) {
        $trabajadores = collect([$cita->trabajador]);
    }

    $destinatarios = self::adminsYRecepcion()
        ->merge($trabajadores)
        ->filter()
        ->unique('id')
        ->values();

    $clienteNombre = strip_tags($cita->cliente->nombre ?? 'un cliente');

    self::crearPara(
        $destinatarios,
        'cita_nueva',
        'Nueva cita agendada',
        "{$clienteNombre} agendó " . self::formatearCita($cita),
        ['cita_id' => $cita->id]
    );
}

    // cita confirmada
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

   // cita cancelada
public static function citaCancelada(Cita $cita, ?string $motivo = null): void
{
    //multi-servicio
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

// esto es por si un trabajador cancela una cita pagada y toca reembolso
public static function reembolsoPorDefinir(Cita $cita): Collection
{
    $destinatarios = self::adminsYRecepcion();

    self::crearPara(
        $destinatarios,
        'reembolso_por_definir',
        'Reembolso pendiente de definir',
        "El trabajador canceló la cita de "
            . ($cita->cliente->nombre ?? 'un cliente')
            . ' y se debe indicar el importe del reembolso. Motivo: '
            . $cita->motivo_cancelacion,
        ['cita_id' => $cita->id, 'motivo' => $cita->motivo_cancelacion]
    );

    return $destinatarios;
}

    // reembolso completo
    public static function reembolsoCompletado(Pago $pago): void
    {
        $pago->loadMissing('cita.cliente');
        $cliente = $pago->cita?->cliente;

        if (!$cliente) {
            throw new \RuntimeException('No se pudo notificar el reembolso porque la cita no tiene un cliente asociado.');
        }

        $monto = number_format((float) $pago->monto_reembolso, 2);

        self::crearPara(
            collect([$cliente]),
            'reembolso_completado',
            'Reembolso completado',
            "El reembolso de \${$monto} por tu cita fue completado.",
            ['cita_id' => $pago->cita_id, 'pago_id' => $pago->id, 'monto_reembolso' => $pago->monto_reembolso]
        );
    }


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