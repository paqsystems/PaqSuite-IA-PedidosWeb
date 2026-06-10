<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class ResetPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $resetUrl,
        public readonly int $expirationMinutes,
    ) {}

    public function build(): self
    {
        return $this->subject(__('mail.passwordReset.subject'))
            ->view('emails.reset-password');
    }
}
