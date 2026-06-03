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
     * @param  array<string, mixed>  $comprobanteViewData
     */
    public function __construct(
        public readonly array $comprobanteViewData,
        public readonly ?string $fromAddress = null,
        public readonly ?string $fromName = null,
        public readonly string $mailLocale = 'es',
    ) {}

    public function build(): self
    {
        return $this->withLocale($this->mailLocale, function (): self {
            $subject = view('emails.comprobante-notification-subject', $this->comprobanteViewData)->render();

            $message = $this->subject(trim($subject))
                ->view('emails.comprobante-notification-body', $this->comprobanteViewData);

            if ($this->fromAddress !== null && $this->fromAddress !== '') {
                $message->from(
                    $this->fromAddress,
                    $this->fromName ?? (string) config('mail.from.name', config('app.name'))
                );
            }

            return $message;
        });
    }
}
