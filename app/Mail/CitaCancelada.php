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

    // Conserva los datos de la cita y el motivo para mostrarlos en el correo.
    public function __construct(public Cita $cita, public ?string $motivo = null)
    {
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
