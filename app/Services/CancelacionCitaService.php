<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelacionCitaService
{
    /**
     * @return array{cita: Cita, recien_cancelada: bool, reembolso_por_definir: bool}
     */
    public function cancelarCita(int $citaId, User $actor, string $motivo, array $reembolsos = []): array
    {
        return DB::transaction(function () use ($citaId, $actor, $motivo, $reembolsos) {
            $cita = Cita::query()->lockForUpdate()->findOrFail($citaId);

            if ($cita->estaCancelada()) {
                throw ValidationException::withMessages([
                    'cita' => 'Esta cita ya estaba cancelada.',
                ]);
            }

            return $this->cancelarBloqueado($cita, $actor, $motivo, $reembolsos);
        });
    }

    /**
     * @return array{cita: Cita, recien_cancelada: bool, reembolso_por_definir: bool}
     */
    public function rechazarPago(int $pagoId, User $actor, string $motivo, array $reembolsos = []): array
    {
        return DB::transaction(function () use ($pagoId, $actor, $motivo, $reembolsos) {
            $pago = Pago::query()->findOrFail($pagoId);
            $cita = Cita::query()->lockForUpdate()->findOrFail($pago->cita_id);
            $pago = Pago::query()->lockForUpdate()->findOrFail($pagoId);

            if ($pago->estado_pago !== 'revision') {
                throw ValidationException::withMessages([
                    'pago' => 'Este pago ya fue procesado.',
                ]);
            }

            return $this->cancelarBloqueado($cita, $actor, $motivo, $reembolsos, true);
        });
    }

    /**
     * @return array{cita: Cita, recien_cancelada: bool, reembolso_por_definir: bool}
     */
    private function cancelarBloqueado(
        Cita $cita,
        User $actor,
        string $motivo,
        array $reembolsos,
        bool $permitirYaCancelada = false
    ): array {
        $recienCancelada = !$cita->estaCancelada();
        if (!$recienCancelada && !$permitirYaCancelada) {
            throw ValidationException::withMessages([
                'cita' => 'Esta cita ya estaba cancelada.',
            ]);
        }

        if ($recienCancelada) {
            $cita->update([
                'estado'             => 'cancelada',
                'motivo_cancelacion' => $motivo,
                'cancelada_por'      => $actor->id,
                'cancelada_en'       => now(),
            ]);
        }

        $pagos = $cita->pagos()->lockForUpdate()->get();
        $pagosQueRequierenImporte = $pagos
            ->where('estado_pago', 'aprobado')
            ->where('estado_reembolso', '!=', 'pendiente');
        $esTrabajador = $actor->rol === 'trabajador';

        if (!$esTrabajador) {
            $idsRequeridos = $pagosQueRequierenImporte->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
            $idsRecibidos = collect(array_keys($reembolsos))->map(fn ($id) => (string) $id)->sort()->values()->all();

            if ($idsRequeridos !== $idsRecibidos) {
                throw ValidationException::withMessages([
                    'reembolsos' => 'Indica el importe del reembolso para cada pago aprobado.',
                ]);
            }
        }

        foreach ($pagos as $pago) {
            if ($pago->estado_pago === 'revision') {
                $pago->update([
                    'estado_pago'    => 'rechazado',
                    'motivo_rechazo' => $motivo,
                ]);
                continue;
            }

            if ($pago->estado_pago !== 'aprobado' || $pago->estado_reembolso === 'pendiente') {
                continue;
            }

            if ($esTrabajador) {
                $pago->update([
                    'estado_reembolso' => 'por_definir',
                    'monto_reembolso'  => null,
                    'motivo_reembolso' => $motivo,
                ]);
                continue;
            }

            $importe = $reembolsos[$pago->id] ?? null;
            if (!is_numeric($importe) || (float) $importe <= 0 || (float) $importe > (float) $pago->monto_anticipado) {
                throw ValidationException::withMessages([
                    "reembolsos.{$pago->id}" => 'El importe debe ser mayor que cero y no superar el pago aprobado.',
                ]);
            }

            $pago->update([
                'estado_reembolso' => 'pendiente',
                'monto_reembolso'  => $importe,
                'motivo_reembolso' => $motivo,
            ]);
        }

        $cita->unsetRelation('pagos');
        $cita->load(['cliente', 'servicio', 'trabajador', 'pagos']);

        return [
            'cita'                    => $cita,
            'recien_cancelada'        => $recienCancelada,
            'reembolso_por_definir'   => $cita->pagos->contains('estado_reembolso', 'por_definir'),
        ];
    }
}
