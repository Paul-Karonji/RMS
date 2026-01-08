<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InquiryApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $tenant;
    protected array $credentials;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $tenant, array $credentials)
    {
        $this->tenant = $tenant;
        $this->credentials = $credentials;
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
            ->subject('Rental Inquiry Approved - Welcome!')
            ->greeting('Hello ' . $this->tenant->name . ',')
            ->line('Great news! Your rental inquiry has been approved.')
            ->line('We have created a tenant account for you.')
            ->line('')
            ->line('**Login Credentials:**')
            ->line('Email: ' . $this->credentials['email'])
            ->line('Temporary Password: **' . $this->credentials['temporary_password'] . '**')
            ->action('Login Now', url('/login'))
            ->line('⚠️ **Important:** You must change your password on first login.')
            ->line('Our team will contact you shortly to finalize the lease agreement.')
            ->line('Thank you for choosing our property!');
    }
}
