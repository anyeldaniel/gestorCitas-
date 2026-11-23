<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaCancelada extends Mailable
{
    use Queueable, SerializesModels;

    public float $montoReembolso;
    public bool $reembolsoPorDefinir;

    public function __construct(public Cita $cita, public ?string $motivo = null)
    {
        $this->cita->loadMissing('pagos');

        $this->montoReembolso = (float) $this->cita->pagos
            ->where('estado_reembolso', 'pendiente')
            ->sum('monto_reembolso');
        $this->reembolsoPorDefinir = $this->cita->pagos
            ->contains('estado_reembolso', 'por_definir');
    }

    // Define el asunto que se verá en la bandeja del cliente.
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cita ha sido cancelada',
        );
    }

    // Selecciona la plantilla Blade que forma el cuerpo del correo.
    public function content(): Content
    {
        return new Content(
            view: 'emails.cita-cancelada',
        );
    }

    // Devuelve la lista de adjuntos; este correo no lleva ninguno.
    public function attachments(): array
    {
        return [];
    }
}
