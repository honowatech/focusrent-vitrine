<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test des paramètres e-mail — Focus Rent',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Ceci est un e-mail de test envoyé depuis le back-office Focus Rent.</p><p>Si vous le recevez, les paramètres SMTP sont corrects.</p>',
        );
    }
}
