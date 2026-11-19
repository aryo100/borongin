<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderAccess extends Mailable
{
    use Queueable;

    public function __construct(public string $number, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Akses pesanan Koperasi JSIT Anda');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-access');
    }
}
