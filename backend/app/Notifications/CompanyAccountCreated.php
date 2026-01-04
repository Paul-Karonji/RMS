<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyAccountCreated extends Notification
{
    use Queueable;

    protected $tenant;
    protected $tempPassword;

    public function __construct(Tenant $tenant, string $tempPassword)
    {
        $this->tenant = $tenant;
        $this->tempPassword = $tempPassword;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $loginUrl = config('app.frontend_url', 'http://localhost:5173') . '/login';

        return (new MailMessage)
            ->subject('Your ' . $this->tenant->company_name . ' Account is Ready')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your company account has been created on ' . config('app.name', 'RMS Platform') . '.')
            ->line('**Company Name:** ' . $this->tenant->company_name)
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Temporary Password:** ' . $this->tempPassword)
            ->line('⚠️ You must change your password on first login.')
            ->action('Login Now', $loginUrl)
            ->line('If you have any questions, please contact our support team.');
    }
}
