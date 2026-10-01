<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $statusLabel,
        public string $description
    ) {
    }

    public function envelope(): Envelope
    {
        $number = $this->order->order_number;
        $subject = match (strtolower((string) $this->order->status)) {
            'packed' => "Your order {$number} is packed",
            'shipped' => "Your order {$number} is on its way",
            'delivered' => "Your order {$number} has been delivered",
            'cancelled' => "Your order {$number} has been cancelled",
            default => "Update on your order {$number}: {$this->statusLabel}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.orders.status-updated',
            with: [
                'order' => $this->order->loadMissing(['items', 'statusLogs']),
                'statusLabel' => $this->statusLabel,
                'description' => $this->description,
            ],
        );
    }
}
