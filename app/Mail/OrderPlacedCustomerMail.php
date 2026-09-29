<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?string $guestPassword = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order confirmed — '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.orders.customer',
            with: [
                'order' => $this->order,
                'guestPassword' => $this->guestPassword,
            ],
        );
    }
}
