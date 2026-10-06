<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Alert to the admin when someone sends the Contact Us form. */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contact) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->contact->email ? [new Address($this->contact->email, $this->contact->name)] : [];

        return new Envelope(
            subject: 'New enquiry from '.$this->contact->name.($this->contact->subject ? ' — '.$this->contact->subject : ''),
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact.received');
    }
}
