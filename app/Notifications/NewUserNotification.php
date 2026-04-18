<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('🚀 Nouvelle inscription sur votre App')
        ->greeting('Bonjour Admin,')
        ->line('Un nouvel utilisateur vient de créer un compte.')
        ->line('**Nom :** ' . $this->user->name)
        ->line('**Email :** ' . $this->user->email)
        ->action('Voir dans l\'admin', url('/admin/users'));
    }

}
