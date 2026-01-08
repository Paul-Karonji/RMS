<?php

namespace App\Notifications;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Lease $lease;
    protected array $firstPayment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lease $lease, array $firstPayment)
    {
        $this->lease = $lease;
        $this->firstPayment = $firstPayment;
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
            ->subject('Lease Agreement Created - ' . $property->property_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your lease agreement has been created for:')
            ->line('**Property:** ' . $property->property_name)
            ->line('**Unit:** ' . $unit->unit_number)
            ->line('**Lease Period:** ' . $this->lease->start_date . ' to ' . $this->lease->end_date)
            ->line('**Monthly Rent:** KES ' . number_format($this->lease->rent_amount, 2))
            ->line('')
            ->line('**First Payment Due:**')
            ->line('Total Amount: KES ' . number_format($this->firstPayment['total_amount'], 2))
            ->line('- Rent: KES ' . number_format($this->firstPayment['breakdown']['rent'], 2))
            ->line('- Deposit: KES ' . number_format($this->firstPayment['breakdown']['deposit'], 2))
            ->line($this->firstPayment['note'])
            ->action('View Lease Details', url('/tenant/leases/' . $this->lease->id))
            ->line('Thank you for choosing our property!');
    }
}
