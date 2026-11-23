<?php

namespace App\Mail;

use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReembolsoDefinido extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pago $pago)
    {
        $this->pago->loadMissing('cita.cliente', 'cita.servicio');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Importe de reembolso definido - Cita #' . $this->pago->cita_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reembolso-definido',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
