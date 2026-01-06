<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropertyApproved extends Notification implements ShouldQueue
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
            ->subject('Property Approved - ' . $this->property->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! Your property has been approved.')
            ->line('**Property Details:**')
            ->line('Name: ' . $this->property->name)
            ->line('Type: ' . ucfirst($this->property->property_type))
            ->line('Location: ' . $this->property->city . ', ' . $this->property->state)
            ->line('Total Units: ' . $this->property->total_units)
            ->line('Approved on: ' . $this->property->approved_at->format('M d, Y'))
            ->action('View Property', url('/owner/properties/' . $this->property->id))
            ->line('You can now add units to this property and start managing it.')
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'property_approved',
            'property_id' => $this->property->id,
            'property_name' => $this->property->name,
            'approved_at' => $this->property->approved_at,
            'message' => 'Your property "' . $this->property->name . '" has been approved',
        ];
    }
}
