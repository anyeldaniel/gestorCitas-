<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReembolsoPorDefinir extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reembolso pendiente de definir - Cita #' . $this->cita->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reembolso-por-definir',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
