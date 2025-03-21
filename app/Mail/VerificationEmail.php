<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;


class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('verification@infinix.com', 'Verification Email'),
            subject: 'Verification Email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.verification-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
