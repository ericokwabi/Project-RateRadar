<?php

namespace App\Mail;

use App\Models\ApiCredential;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RateLimitExceeded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ApiCredential $credential,
        public Carbon $measuredAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Lightspeed rate limit bereikt: {$this->credential->store_id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.rate-limit-exceeded',
        );
    }
}
