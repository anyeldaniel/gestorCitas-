<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\User;
use App\Mail\CitaCancelada;
use App\Mail\ReembolsoDefinido;
use App\Mail\ReembolsoPorDefinir;
use App\Notifications\CitaCanceladaNotification;
use App\Services\CancelacionCitaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CancelacionPagoTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechazar_pago_cancela_cita_y_guarda_el_motivo(): void
    {
        $admin = $this->crearUsuario('admin');
        $cita = $this->crearCita();
        $pago = $this->crearPago($cita, 'revision');

        $resultado = app(CancelacionCitaService::class)->rechazarPago(
            $pago->id,
            $admin,
            'Comprobante inválido'
        );

        $this->assertSame('cancelada', $resultado['cita']->estado);
        $this->assertSame('Comprobante inválido', $resultado['cita']->motivo_cancelacion);
        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'rechazado',
            'motivo_rechazo' => 'Comprobante inválido',
        ]);
    }

    public function test_cancelar_por_trabajador_deja_reembolso_aprobado_pendiente_de_definir(): void
    {
        $trabajador = $this->crearUsuario('trabajador');
        $cita = $this->crearCita($trabajador);
        $pagoAprobado = $this->crearPago($cita, 'aprobado');
        $pagoPendiente = $this->crearPago($cita, 'revision');

        $resultado = app(CancelacionCitaService::class)->cancelarCita(
            $cita->id,
            $trabajador,
            'No podré atender esta cita'
        );

        $this->assertTrue($resultado['reembolso_por_definir']);
        $this->assertDatabaseHas('pagos', [
            'id' => $pagoAprobado->id,
            'estado_pago' => 'aprobado',
            'estado_reembolso' => 'por_definir',
            'monto_reembolso' => null,
        ]);
        $this->assertDatabaseHas('pagos', [
            'id' => $pagoPendiente->id,
            'estado_pago' => 'rechazado',
            'motivo_rechazo' => 'No podré atender esta cita',
        ]);
    }

    public function test_cancelar_por_admin_registra_importe_de_reembolso(): void
    {
        $admin = $this->crearUsuario('admin');
        $cita = $this->crearCita();
        $pago = $this->crearPago($cita, 'aprobado', '80.00');

        app(CancelacionCitaService::class)->cancelarCita(
            $cita->id,
            $admin,
            'El cliente solicitó cancelar',
            [$pago->id => '35.00']
        );

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'aprobado',
            'estado_reembolso' => 'pendiente',
            'monto_reembolso' => '35.00',
            'motivo_reembolso' => 'El cliente solicitó cancelar',
        ]);
    }

    public function test_admin_debe_indicar_reembolso_antes_de_cancelar_cita_pagada(): void
    {
        $admin = $this->crearUsuario('admin');
        $cita = $this->crearCita();
        $this->crearPago($cita, 'aprobado');

        $this->actingAs($admin)
            ->postJson(route('agenda.cita.cancelar', $cita->id), [
                'motivo' => 'El cliente solicitó cancelar',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reembolsos');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_cancelar_desde_formulario_redirige_a_la_agenda(): void
    {
        Mail::fake();
        Notification::fake();

        $admin = $this->crearUsuario('admin');
        $cita = $this->crearCita();

        $this->actingAs($admin)
            ->from(route('agenda'))
            ->post(route('agenda.cita.cancelar', $cita->id), [
                'motivo' => 'No se podrá atender esta cita',
            ])
            ->assertRedirect(route('agenda'));

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'cancelada',
        ]);
    }

    public function test_cancelacion_por_trabajador_notifica_admin_y_recepcion_sobre_reembolso(): void
    {
        Mail::fake();
        Notification::fake();

        $admin = $this->crearUsuario('admin');
        $recepcion = $this->crearUsuario('recepcion');
        $trabajador = $this->crearUsuario('trabajador');
        $cita = $this->crearCita($trabajador);
        $this->crearPago($cita, 'aprobado');

        $this->actingAs($trabajador)
            ->postJson(route('agenda.cita.cancelar', $cita->id), [
                'motivo' => 'No podré atender esta cita',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('notificaciones', [
            'user_id' => $admin->id,
            'tipo' => 'reembolso_por_definir',
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'user_id' => $recepcion->id,
            'tipo' => 'reembolso_por_definir',
        ]);
        Mail::assertSent(CitaCancelada::class);
        $this->assertCount(
            User::whereIn('rol', ['admin', 'recepcion'])->count(),
            Mail::sent(ReembolsoPorDefinir::class)
        );
        Notification::assertSentTo(
            $cita->cliente,
            CitaCanceladaNotification::class,
            function (CitaCanceladaNotification $notification) use ($cita): bool {
                $mensaje = $notification->toWebPush($cita->cliente, $notification)->toArray()['body'] ?? '';

                return str_contains($mensaje, 'Motivo: No podré atender esta cita.')
                    && str_contains($mensaje, 'Reembolso en revisión.');
            }
        );
    }

    public function test_cancelacion_con_pago_aprobado_mueve_reembolso_a_completado_y_se_muestra_al_cliente(): void
    {
        Mail::fake();
        Notification::fake();

        $admin = $this->crearUsuario('admin');
        $cita = $this->crearCita();
        $cliente = $cita->cliente;
        $pago = $this->crearPago($cita, 'aprobado');

        $this->actingAs($admin)
            ->post(route('agenda.cita.cancelar', $cita->id), [
                'motivo' => 'El cliente solicitó cancelar',
                'reembolsos' => [$pago->id => '35.00'],
            ])
            ->assertRedirect(route('agenda'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'aprobado',
            'estado_reembolso' => 'pendiente',
            'monto_reembolso' => '35.00',
        ]);

        $this->actingAs($admin)
            ->get(route('pago.verificar'))
            ->assertOk()
            ->assertSee('Autorizado <span class="tab-contador">0</span>', false)
            ->assertSee('Reembolso <span class="tab-contador">1</span>', false)
            ->assertSee('Reembolso pendiente')
            ->assertSee('Marcar reembolsado');

        Notification::assertSentTo(
            $cliente,
            CitaCanceladaNotification::class,
            function (CitaCanceladaNotification $notification) use ($cliente): bool {
                $mensaje = $notification->toWebPush($cliente, $notification)->toArray()['body'] ?? '';

                return str_contains($mensaje, 'Motivo: El cliente solicitó cancelar.')
                    && str_contains($mensaje, 'Reembolso pendiente por $35.00.');
            }
        );

        $this->actingAs($admin)
            ->postJson(route('recepcion.marcar.reembolsado', $pago->id))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_reembolso' => 'reembolsado',
            'monto_reembolso' => '35.00',
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'user_id' => $cliente->id,
            'tipo' => 'reembolso_completado',
        ]);

        $this->actingAs($admin)
            ->get(route('pago.verificar'))
            ->assertOk()
            ->assertSee('Reembolsado')
            ->assertSee('Monto devuelto: $35.00');

        $this->actingAs($cliente)
            ->get(route('clientes.mis.reservas'))
            ->assertOk()
            ->assertSee('Cancelada')
            ->assertSee('Motivo: El cliente solicitó cancelar')
            ->assertSee('Reembolso completado: $35.00')
            ->assertSee('No tienes citas próximas agendadas.');
    }

    public function test_rechazar_pago_en_revision_muestra_cancelacion_sin_prometer_reembolso(): void
    {
        Mail::fake();
        Notification::fake();

        $recepcion = $this->crearUsuario('recepcion');
        $cita = $this->crearCita();
        $cliente = $cita->cliente;
        $pago = $this->crearPago($cita, 'revision');

        $this->actingAs($recepcion)
            ->postJson(route('recepcion.rechazar.pago', $pago->id), [
                'motivo' => 'El comprobante no es válido',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'cancelada',
            'motivo_cancelacion' => 'El comprobante no es válido',
        ]);
        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'rechazado',
            'estado_reembolso' => null,
        ]);

        Notification::assertSentTo(
            $cliente,
            CitaCanceladaNotification::class,
            function (CitaCanceladaNotification $notification) use ($cliente): bool {
                $mensaje = $notification->toWebPush($cliente, $notification)->toArray()['body'] ?? '';

                return str_contains($mensaje, 'Motivo: El comprobante no es válido.')
                    && !str_contains($mensaje, 'Reembolso');
            }
        );

        $this->actingAs($cliente)
            ->get(route('clientes.mis.reservas'))
            ->assertOk()
            ->assertSee('Cancelada')
            ->assertSee('Motivo: El comprobante no es válido')
            ->assertDontSee('Reembolso pendiente:')
            ->assertDontSee('Reembolso completado:')
            ->assertSee('No tienes citas próximas agendadas.');
    }

    public function test_definir_reembolso_envia_al_cliente_el_importe_registrado(): void
    {
        Mail::fake();

        $recepcion = $this->crearUsuario('recepcion');
        $cita = $this->crearCita();
        $cita->update([
            'estado' => 'cancelada',
            'motivo_cancelacion' => 'No se pudo prestar el servicio',
        ]);
        $pago = $this->crearPago($cita, 'aprobado');
        $pago->update(['estado_reembolso' => 'por_definir']);

        $this->actingAs($recepcion)
            ->postJson(route('recepcion.definir.reembolso', $pago->id), [
                'monto_reembolso' => '25.00',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        Mail::assertSent(ReembolsoDefinido::class, function (ReembolsoDefinido $mail) use ($pago) {
            return (float) $mail->pago->monto_reembolso === 25.0;
        });
    }

    private function crearUsuario(string $rol): User
    {
        return User::create([
            'nombre' => ucfirst($rol),
            'correo' => $rol . uniqid() . '@example.test',
            'contraseña' => Hash::make('password'),
            'rol' => $rol,
        ]);
    }

    private function crearCita(?User $trabajador = null): Cita
    {
        $cliente = $this->crearUsuario('cliente');
        $trabajador ??= $this->crearUsuario('trabajador');
        $servicio = Servicio::create([
            'nombre_servicio' => 'Masaje',
            'precio' => 80,
            'duracion_minutos' => 60,
        ]);

        return Cita::create([
            'cliente_id' => $cliente->id,
            'trabajador_id' => $trabajador->id,
            'servicio_id' => $servicio->id,
            'monto_total' => 80,
            'fecha' => now()->toDateString(),
            'hora' => '10:00:00',
            'estado' => 'pendiente',
        ]);
    }

    private function crearPago(Cita $cita, string $estado, string $monto = '50.00'): Pago
    {
        return Pago::create([
            'cita_id' => $cita->id,
            'monto_anticipado' => $monto,
            'metodo_pago' => 'transferencia',
            'estado_pago' => $estado,
        ]);
    }
}
