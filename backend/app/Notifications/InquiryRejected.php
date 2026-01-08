<?php

namespace App\Notifications;

use App\Models\RentalInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InquiryRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected RentalInquiry $inquiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(RentalInquiry $inquiry)
    {
        $this->inquiry = $inquiry;
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
        return (new MailMessage)
            ->subject('Rental Inquiry Update')
            ->greeting('Hello ' . $this->inquiry->name . ',')
            ->line('Thank you for your interest in our property.')
            ->line('Unfortunately, we are unable to proceed with your rental inquiry at this time.')
            ->line('')
            ->line('**Reason:** ' . $this->inquiry->rejection_reason)
            ->line('')
            ->line('We appreciate your interest and encourage you to explore other available units on our platform.')
            ->action('Browse Available Units', url('/units'))
            ->line('Thank you for considering our property.');
    }
}
