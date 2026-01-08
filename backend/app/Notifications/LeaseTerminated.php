<?php

namespace App\Notifications;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseTerminated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Lease $lease;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lease $lease)
    {
        $this->lease = $lease;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $property = $this->lease->property;
        $unit = $this->lease->unit;
        
        return (new MailMessage)
            ->subject('Lease Terminated - ' . $property->property_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your lease agreement has been terminated.')
            ->line('**Property:** ' . $property->property_name)
            ->line('**Unit:** ' . $unit->unit_number)
            ->line('**Termination Date:** ' . $this->lease->terminated_at->format('F d, Y'))
            ->line('**Reason:** ' . $this->lease->termination_reason)
            ->line('')
            ->line('Please ensure all outstanding payments are settled.')
            ->line('Your security deposit will be processed according to the lease terms.')
            ->action('View Lease Details', url('/tenant/leases/' . $this->lease->id))
            ->line('Thank you for being our tenant.');
    }
}
