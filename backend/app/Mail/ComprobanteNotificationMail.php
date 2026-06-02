<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class ComprobanteNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $viewData
     */
    public function __construct(
        public readonly array $viewData,
    ) {}

    public function build(): self
    {
        $subject = view('emails.comprobante-notification-subject', $this->viewData)->render();

        return $this->subject(trim($subject))
            ->view('emails.comprobante-notification-body', $this->viewData);
    }
}
