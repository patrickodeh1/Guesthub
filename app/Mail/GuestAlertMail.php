<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuestAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $eventLabel,
        public string $message,
    ) {}

    public function build()
    {
        return $this->subject("GuestHub: {$this->eventLabel}")
            ->markdown('emails.guest-alert')
            ->with([
                'eventLabel' => $this->eventLabel,
                'message' => $this->message,
            ]);
    }
}
