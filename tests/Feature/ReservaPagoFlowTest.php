<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\User;
use App\Notifications\NuevaCitaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservaPagoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_selection_reaches_checkout_and_only_creates_records_when_payment_is_submitted(): void
    {
        Mail::fake();
        Notification::fake();

        $cliente = $this->crearUsuario('cliente');
        $trabajador = $this->crearUsuario('trabajador');
        $servicio = $this->crearServicio();
        $this->seleccionarReserva($cliente, $trabajador, $servicio);

        $this->post(route('checkout.procesar'), [
                'tipo_pago' => 'abono',
                'metodo_pago' => 'transferencia',
                'referencia' => 'REF12345',
                'monto' => '0.01',
            ])
            ->assertRedirect(route('clientes.reserva'))
            ->assertSessionMissing('reserva_pendiente');

        $cita = Cita::query()->sole();
        $pago = Pago::query()->sole();

        $this->assertSame($cliente->id, $cita->cliente_id);
        $this->assertSame($trabajador->id, $cita->trabajador_id);
        $this->assertSame($servicio->id, $cita->servicio_id);
        $this->assertSame('pendiente', $cita->estado);
        $this->assertSame('revision', $pago->estado_pago);
        $this->assertSame(40.0, (float) $pago->monto_anticipado);
        $this->assertSame($cita->id, $pago->cita_id);
        $this->assertClientPushMessage($cliente, 'reporte de tu abono está pendiente de confirmación');
        $this->assertDatabaseHas('cita_servicio', [
            'cita_id' => $cita->id,
            'servicio_id' => $servicio->id,
            'trabajador_id' => $trabajador->id,
        ]);

        $recepcion = $this->crearUsuario('recepcion');
        $pago->update(['monto_anticipado' => '39.99']);
        $this->actingAs($recepcion)
            ->post(route('recepcion.aprobar.pago', $pago->id))
            ->assertSessionHasErrors('pago');

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'revision',
        ]);
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);

        $pago->update(['monto_anticipado' => '40.00']);
        $this->actingAs($recepcion)
            ->post(route('recepcion.aprobar.pago', $pago->id))
            ->assertRedirect();

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'aprobado',
        ]);
    }

    public function test_total_payment_uses_server_total_ignores_client_amount_and_sends_correct_push(): void
    {
        Mail::fake();
        Notification::fake();

        $cliente = $this->crearUsuario('cliente');
        $trabajador = $this->crearUsuario('trabajador');
        $recepcion = $this->crearUsuario('recepcion');
        $servicio = $this->crearServicio();
        $this->seleccionarReserva($cliente, $trabajador, $servicio);

        $this->post(route('checkout.procesar'), [
            'tipo_pago' => 'total',
            'metodo_pago' => 'transferencia',
            'referencia' => 'REFTOTAL',
            'monto' => '0.01',
        ])->assertRedirect(route('clientes.reserva'));

        $cita = Cita::query()->sole();
        $pago = Pago::query()->sole();

        $this->assertSame(80.0, (float) $cita->monto_total);
        $this->assertSame(80.0, (float) $pago->monto_anticipado);
        $this->assertSame('revision', $pago->estado_pago);

        $this->assertClientPushMessage($cliente, 'Solo debes esperar la confirmación');

        $this->actingAs($recepcion)
            ->post(route('recepcion.aprobar.pago', $pago->id))
            ->assertRedirect();

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'estado_pago' => 'aprobado',
            'monto_anticipado' => '80.00',
        ]);
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'confirmada',
        ]);
    }

    public function test_checkout_rejects_cash_payment_method(): void
    {
        $cliente = $this->crearUsuario('cliente');
        $trabajador = $this->crearUsuario('trabajador');
        $servicio = $this->crearServicio();
        $this->seleccionarReserva($cliente, $trabajador, $servicio);

        $this->post(route('checkout.procesar'), [
            'tipo_pago' => 'abono',
            'metodo_pago' => 'divisas_efectivo',
        ])
            ->assertSessionHasErrors('metodo_pago');

        $this->assertDatabaseCount('citas', 0);
        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_mis_reservas_shows_full_payment_review_then_approved_and_updates_balance(): void
    {
        Mail::fake();

        $cliente = $this->crearUsuario('cliente');
        $trabajador = $this->crearUsuario('trabajador');
        $recepcion = $this->crearUsuario('recepcion');
        $servicio = $this->crearServicio();
        $cita = Cita::create([
            'cliente_id' => $cliente->id,
            'trabajador_id' => $trabajador->id,
            'servicio_id' => $servicio->id,
            'monto_total' => 80,
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '10:00',
            'estado' => 'pendiente',
        ]);
        $cita->servicios()->attach($servicio->id, ['trabajador_id' => $trabajador->id]);

        $this->actingAs($cliente)
            ->get(route('clientes.mis.reservas'))
            ->assertOk()
            ->assertDontSee('COMPLETAR PAGO');

        $pago = Pago::create([
            'cita_id' => $cita->id,
            'monto_anticipado' => 80,
            'metodo_pago' => 'transferencia',
            'estado_pago' => 'revision',
        ]);

        $respuestaRevision = $this->actingAs($cliente)
            ->get(route('clientes.mis.reservas'))
            ->assertOk()
            ->assertSee('CONFIRMACIÓN DE PAGO PENDIENTE')
            ->assertSee('PAGO TOTAL REPORTADO')
            ->assertSee('Recibimos tu comprobante. En espera de verificación por recepción.')
            ->assertSee('80.00')
            ->assertSee('<h2>$80</h2>', false)
            ->assertDontSee('PENDIENTE DE PAGO RESTANTE');

        $this->assertSame(
            1,
            substr_count($respuestaRevision->getContent(), 'CONFIRMACIÓN DE PAGO PENDIENTE')
        );

        $this->actingAs($recepcion)
            ->post(route('recepcion.aprobar.pago', $pago->id))
            ->assertRedirect();

        $this->actingAs($cliente)
            ->get(route('clientes.mis.reservas'))
            ->assertOk()
            ->assertSee('Confirmado')
            ->assertSee('TOTAL PAGADO')
            ->assertSee('<h2>$0</h2>', false)
            ->assertDontSee('CONFIRMACIÓN DE PAGO PENDIENTE')
            ->assertDontSee('PAGO TOTAL REPORTADO')
            ->assertDontSee('Recibimos tu comprobante. En espera de verificación por recepción.');
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

    private function crearServicio(): Servicio
    {
        return Servicio::create([
            'nombre_servicio' => 'Masaje',
            'precio' => 80,
            'duracion_minutos' => 60,
        ]);
    }

    private function seleccionarReserva(User $cliente, User $trabajador, Servicio $servicio): void
    {
        $fecha = now()->addDay()->toDateString();
        $checkoutUrl = route('checkout.view');

        $this->actingAs($cliente)
            ->postJson(route('clientes.reserva.store'), [
                'servicios' => [$servicio->id],
                'trabajadores_por_servicio' => [$servicio->id => $trabajador->id],
                'fecha' => $fecha,
                'hora' => '10:00',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect_url', $checkoutUrl)
            ->assertSessionHas('reserva_pendiente', [
                'cliente_id' => $cliente->id,
                'fecha' => $fecha,
                'hora' => '10:00',
                'trabajador_id' => $trabajador->id,
                'servicio_id' => $servicio->id,
                'trabajadores_por_servicio' => [$servicio->id => $trabajador->id],
            ]);

        $this->assertDatabaseCount('citas', 0);
        $this->assertDatabaseCount('pagos', 0);

        $this->get($checkoutUrl)
            ->assertOk()
            ->assertViewIs('clientes.checkout');

        $this->assertDatabaseCount('citas', 0);
        $this->assertDatabaseCount('pagos', 0);
    }

    private function assertClientPushMessage(User $cliente, string $expectedMessage): void
    {
        Notification::assertSentTo(
            $cliente,
            NuevaCitaNotification::class,
            function (NuevaCitaNotification $notification, array $channels) use ($cliente, $expectedMessage): bool {
                $mensaje = $notification->toWebPush($cliente, $notification)->toArray()['body'] ?? '';

                return str_contains($mensaje, $expectedMessage)
                    && !str_contains($mensaje, 'completa el pago');
            }
        );

        }
}