<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $temporaryPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $temporaryPassword)
    {
        $this->temporaryPassword = $temporaryPassword;
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
            ->subject('Your RMS Tenant Account is Ready')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your tenant account has been created on the RMS Platform.')
            ->line('**Login Credentials:**')
            ->line('Email: ' . $notifiable->email)
            ->line('Temporary Password: **' . $this->temporaryPassword . '**')
            ->action('Login Now', url('/login'))
            ->line('⚠️ **Important:** You must change your password on first login.')
            ->line('Thank you for using our platform!');
    }
}
