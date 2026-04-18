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
            ->subject('🚀 Nouvelle inscription : ' . $this->user->name)
            ->greeting('Bonjour Admin,')
            ->line('Un nouvel utilisateur s\'est inscrit sur votre plateforme.')
            ->line('**Nom :** ' . $this->user->name)
            ->line('**Email :** ' . $this->user->email)
            ->action('Voir l\'utilisateur', url('/admin/users')) // Optionnel
            ->line('Félicitations !');
    }

}
