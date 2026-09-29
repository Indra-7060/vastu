<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your password — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        $url = url(route('customer.password.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));

        return new Content(
            html: 'emails.auth.reset-password',
            with: [
                'user' => $this->user,
                'resetUrl' => $url,
                'expiresMinutes' => (int) config('auth.passwords.users.expire', 60),
            ],
        );
    }
}
