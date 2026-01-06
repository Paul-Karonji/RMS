<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropertySubmitted extends Notification implements ShouldQueue
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
            ->subject('New Property Pending Approval')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new property has been submitted for approval.')
            ->line('**Property Details:**')
            ->line('Name: ' . $this->property->name)
            ->line('Type: ' . ucfirst($this->property->property_type))
            ->line('Location: ' . $this->property->city . ', ' . $this->property->state)
            ->line('Total Units: ' . $this->property->total_units)
            ->line('Owner: ' . ($this->property->propertyOwner->name ?? 'N/A'))
            ->action('Review Property', url('/admin/properties/' . $this->property->id))
            ->line('Please review and approve or reject this property submission.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'property_submitted',
            'property_id' => $this->property->id,
            'property_name' => $this->property->name,
            'owner_name' => $this->property->propertyOwner->name ?? 'N/A',
            'message' => 'New property "' . $this->property->name . '" submitted for approval',
        ];
    }
}
