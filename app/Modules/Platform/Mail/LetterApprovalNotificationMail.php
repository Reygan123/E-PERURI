<?php

declare(strict_types=1);

namespace App\Modules\Platform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LetterApprovalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $letterMetadata
     */
    public function __construct(
        public string $recipientName,
        public string $notificationTitle,
        public string $notificationMessage,
        public array $letterMetadata = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[E-Arsip PTKP] {$this->notificationTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.letter-approval',
            with: [
                'metadata' => $this->letterMetadata,
            ],
        );
    }
}
