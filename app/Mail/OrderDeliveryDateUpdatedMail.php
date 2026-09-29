<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveryDateUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $deliveryDate
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Expected delivery update — '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.orders.delivery-date-updated',
            with: [
                'order' => $this->order,
                'deliveryDate' => $this->deliveryDate,
            ],
        );
    }
}
