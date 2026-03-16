<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorVerificationRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[PsychoCare] Your professional profile needs revision')
            ->line('Your professional verification submission was not approved.');

        if ($this->reason) {
            $mail->line("Reason: {$this->reason}");
        }

        return $mail->line('Please revise your profile and resubmit.')
            ->action('Update your profile', url('/doctor/profile'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'   => 'doctor_verification_rejected',
            'reason' => $this->reason,
        ];
    }
}
