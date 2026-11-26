<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaPendienteConfirmacion extends Mailable
{
    use Queueable, SerializesModels;

    // Conserva los datos de la cita que se mostrarán en el correo.
    public function __construct(public Cita $cita)
    {
    }

    // Define el asunto que verá el cliente en su bandeja de entrada.
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cita está pendiente de confirmación',
        );
    }

    // Indica la plantilla Blade que construye el contenido del mensaje.
    public function content(): Content
    {
        return new Content(
            view: 'emails.cita-pendiente-confirmacion',
        );
    }

    // Este aviso no incluye archivos adjuntos.
    public function attachments(): array
    {
        return [];
    }
}
