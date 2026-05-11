<?php
// ════════════════════════════════════════════════════════════════
// app/Mail/PasswordResetMail.php
// php artisan make:mail PasswordResetMail
// Replace content with this.
// ════════════════════════════════════════════════════════════════
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $otp,
        public readonly string $magicLink,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your PulseWork password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
        );
    }
}