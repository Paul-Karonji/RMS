<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropertyRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Property Requires Changes - ' . $this->property->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your property submission requires some changes before it can be approved.')
            ->line('**Property Details:**')
            ->line('Name: ' . $this->property->name)
            ->line('Type: ' . ucfirst($this->property->property_type))
            ->line('Location: ' . $this->property->city . ', ' . $this->property->state)
            ->line('')
            ->line('**Reason for Rejection:**')
            ->line($this->property->rejection_reason ?? 'No reason provided')
            ->line('')
            ->action('Update & Resubmit Property', url('/owner/properties/' . $this->property->id . '/edit'))
            ->line('Please make the necessary changes and resubmit your property for approval.')
            ->line('You can resubmit as many times as needed.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'property_rejected',
            'property_id' => $this->property->id,
            'property_name' => $this->property->name,
            'rejection_reason' => $this->property->rejection_reason,
            'message' => 'Your property "' . $this->property->name . '" requires changes',
        ];
    }
}
