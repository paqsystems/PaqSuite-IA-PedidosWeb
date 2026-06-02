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
        public readonly ?string $fromAddress = null,
        public readonly ?string $fromName = null,
    ) {}

    public function build(): self
    {
        $subject = view('emails.comprobante-notification-subject', $this->viewData)->render();

        $message = $this->subject(trim($subject))
            ->view('emails.comprobante-notification-body', $this->viewData);

        if ($this->fromAddress !== null && $this->fromAddress !== '') {
            $message->from(
                $this->fromAddress,
                $this->fromName ?? (string) config('mail.from.name', config('app.name'))
            );
        }

        return $message;
    }
}
