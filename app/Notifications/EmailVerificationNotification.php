<?php

namespace App\Notifications;


use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    public $message;
    public $subject;
    public $formEmail;
    public $mailer;
    public $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->message='استخدم الرمز في الاسفل لاكمال عملية التحقق.';
        $this->subject='تحقق من البريد الالكتروني';
        $this->formEmail='7288f7106e-fbbe16@inbox.mailtrap.io';
        $this->mailer='smtp';
        $this->otp=new Otp;
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
    public function toMail($notifiable)
    {
        $otp = $this->otp->generate($notifiable->email,'numeric',6,15);
        return (new MailMessage)
                    ->mailer('smtp')
            ->subject($this->subject)
            ->greeting('مرحبا ' . $notifiable->name)
            ->line($this->message)
            ->line('الرمز: ' . $otp->token);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
