<?php

namespace App\Mail;

use App\Models\PlatformSecurityCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecurityAccessCodeRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PlatformSecurityCode $securityCode)
    {
    }

    public function envelope(): Envelope
    {
        $name = $this->securityCode->name ?: 'Requester';
        $email = $this->securityCode->email;
        $code = $this->securityCode->access_code;

        return new Envelope(
            subject: "[Bridge Tailor Security] Access Code Request from {$name} ({$email}) — Code: {$code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.security_access_request',
        );
    }
}
