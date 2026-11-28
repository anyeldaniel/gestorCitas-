<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    // Recibe y conserva la cita cuyos datos se mostrarán en el correo.
    public function __construct(public Cita $cita)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cita ha sido confirmada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cita-confirmada',
        );
    }

    // Devuelve la lista de adjuntos; este correo no lleva ninguno.
    public function attachments(): array
    {
        return [];
    }
}
