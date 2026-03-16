<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorVerificationApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[PsychoCare] Your professional profile has been approved')
            ->line('Congratulations! Your professional verification has been approved.')
            ->line('You can now accept patients and start consultations.')
            ->action('Go to your dashboard', url('/doctor'));
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'doctor_verification_approved'];
    }
}
