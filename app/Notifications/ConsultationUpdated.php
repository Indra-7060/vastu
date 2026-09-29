<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer when an admin updates or deletes their consultation request.
 * Registered customers get an email + a notification in their account; guests get the email.
 */
class ConsultationUpdated extends Notification
{
    use Queueable;

    public const EVENT_STATUS = 'status';
    public const EVENT_MESSAGE = 'message';
    public const EVENT_DELETED = 'deleted';

    /** @var array<string, mixed> Snapshot of the request (it may already be deleted). */
    public array $consultation;

    public function __construct(Consultation $consultation, public string $event, public ?string $adminMessage = null)
    {
        $this->consultation = [
            'id' => $consultation->id,
            'name' => $consultation->name,
            'interest' => $consultation->interest,
            'status' => $consultation->status,
            'status_label' => $consultation->status_label,
            'requested_at' => optional($consultation->created_at)->format('d M Y'),
        ];
    }

    public function via(object $notifiable): array
    {
        return method_exists($notifiable, 'getKey') && $notifiable->getKey() ? ['mail', 'database'] : ['mail'];
    }

    public function title(): string
    {
        return match ($this->event) {
            self::EVENT_DELETED => 'Your consultation request was closed',
            self::EVENT_MESSAGE => 'New message about your consultation',
            default => match ($this->consultation['status']) {
                'contacted' => 'We have reached out about your consultation',
                'scheduled' => 'Your consultation is scheduled',
                'closed' => 'Your consultation request is complete',
                default => 'Your consultation request was updated',
            },
        };
    }

    public function body(): string
    {
        $interest = $this->consultation['interest'];

        $text = match ($this->event) {
            self::EVENT_DELETED => "Your consultation request for {$interest} (sent on {$this->consultation['requested_at']}) has been removed by our team. If you still need guidance, you are welcome to book a new consultation anytime.",
            self::EVENT_MESSAGE => "Our team has sent you a message about your consultation request for {$interest}.",
            default => match ($this->consultation['status']) {
                'contacted' => "Our team has contacted you about your consultation request for {$interest}. Please check your phone, WhatsApp or email.",
                'scheduled' => "Your consultation for {$interest} has been scheduled. Our team will share the details with you.",
                'closed' => "Your consultation request for {$interest} has been marked as complete. Thank you for choosing Vastutathastu.",
                default => "The status of your consultation request for {$interest} is now: {$this->consultation['status_label']}.",
            },
        };

        return $text;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title().' — Vastutathastu')
            ->view('emails.consultations.updated', [
                'name' => $this->consultation['name'],
                'heading' => $this->title(),
                'body' => $this->body(),
                'adminMessage' => $this->adminMessage,
                'statusLabel' => $this->event === self::EVENT_DELETED ? 'Removed' : $this->consultation['status_label'],
                'interest' => $this->consultation['interest'],
                'requestedAt' => $this->consultation['requested_at'],
                'actionUrl' => method_exists($notifiable, 'getKey') && $notifiable->getKey()
                    ? route('account', 'notifications')
                    : route('home'),
                'actionText' => method_exists($notifiable, 'getKey') && $notifiable->getKey() ? 'View in your account' : 'Visit Vastutathastu',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'consultation',
            'event' => $this->event,
            'title' => $this->title(),
            'body' => $this->body(),
            'message' => $this->adminMessage,
            'status' => $this->event === self::EVENT_DELETED ? 'removed' : $this->consultation['status'],
            'status_label' => $this->event === self::EVENT_DELETED ? 'Removed' : $this->consultation['status_label'],
            'interest' => $this->consultation['interest'],
            'consultation_id' => $this->consultation['id'],
        ];
    }
}
