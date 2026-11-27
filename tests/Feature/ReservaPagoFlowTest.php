<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservaPagoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserving_creates_a_pending_cita_and_redirects_to_checkout_with_cita_id(): void
    {
        Mail::fake();
        Notification::fake();

        $cliente = $this->crearUsuario('cliente');
        $trabajador = $this->crearUsuario('trabajador');
        $servicio = $this->crearServicio();
        $reserva = [
            'servicios' => [$servicio->id],
            'trabajadores_por_servicio' => [$servicio->id => $trabajador->id],
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '10:00',
        ];

        $response = $this->actingAs($cliente)
            ->post(route('clientes.reserva.store'), $reserva);

        // El flujo nuevo crea la cita directamente
        $this->assertDatabaseCount('citas', 1);
        $this->assertDatabaseCount('pagos', 0);

        $cita = Cita::query()->sole();

        $response->assertRedirect(route('checkout.view', ['cita_id' => $cita->id]));

        $this->assertSame($cliente->id, $cita->cliente_id);
        $this->assertSame($trabajador->id, $cita->trabajador_id);
        $this->assertSame($servicio->id, $cita->servicio_id);
        $this->assertSame('pendiente', $cita->estado);
        $this->assertSame(80, (int) $cita->monto_total);

        $this->assertDatabaseHas('cita_servicio', [
            'cita_id' => $cita->id,
            'servicio_id' => $servicio->id,
            'trabajador_id' => $trabajador->id,
        ]);
    }

    public function test_submitting_payment_creates_pending_cita_services_and_review_payment_together(): void
    {
        Mail::fake();
        Notification::fake();

        $cliente = $this->crearUsuario('cliente');
        $trabajador = $this->crearUsuario('trabajador');
        $servicio = $this->crearServicio();
        $reserva = [
            'cliente_id' => $cliente->id,
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '10:00',
            'trabajador_id' => $trabajador->id,
            'servicio_id' => $servicio->id,
            'trabajadores_por_servicio' => [$servicio->id => $trabajador->id],
        ];

        $this->actingAs($cliente)
            ->withSession(['reserva_pendiente' => $reserva])
            ->post(route('checkout.procesar'), [
                'metodo_pago' => 'transferencia',
                'referencia' => 'REF12345',
                'monto' => '0.01',
            ])
            ->assertRedirect(route('clientes.reserva'))
            ->assertSessionMissing('reserva_pendiente');

        $cita = Cita::query()->sole();
        $pago = Pago::query()->sole();

        $this->assertSame('pendiente', $cita->estado);
        $this->assertSame('revision', $pago->estado_pago);
        $this->assertSame($cita->id, $pago->cita_id);
        $this->assertDatabaseHas('cita_servicio', [
            'cita_id' => $cita->id,
            'servicio_id' => $servicio->id,
            'trabajador_id' => $trabajador->id,
        ]);
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
}