<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
            ->subject('Verify Your TradeFlow P2P Account')
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for registering on TradeFlow P2P Exchange. Please use the 6-digit OTP code below to verify your email address:')
            ->line("**{$this->otp}**")
            ->line('This code is valid for 15 minutes. If you did not request this, please ignore this email.')
            ->line('Thank you for choosing TradeFlow P2P!');
    }
}
