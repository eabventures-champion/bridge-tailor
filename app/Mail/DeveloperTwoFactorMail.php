<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeveloperTwoFactorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public User $developer,
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Bridge Tailor] Developer 2FA Verification Code: {$this->otp}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.developer_2fa',
        );
    }
}
