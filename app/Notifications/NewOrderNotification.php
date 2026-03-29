<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $order)
    {
        //
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
 public function toMail($notifiable): MailMessage 
{
    $mail = (new MailMessage)
        ->subject('Nouvelle commande reçue ! - Ref: ' . $this->order->reference)
        ->greeting('Bonjour Administrateur,')
        ->line('Une nouvelle commande vient d\'être enregistrée sur la plateforme.')
        ->line('**Référence :** ' . $this->order->reference)
        ->line('**Statut :** ' . ucfirst($this->order->status))
        ->line('**Client (ID) :** ' . ($this->order->user_id ?? 'N/A'))
        ->line('**Montant Total :** ' . number_format($this->order->total, 0, ',', ' ') . ' F CFA')
        ->line('---')
        ->line('**Détails des produits :**');

    // On boucle sur les produits chargés dans la commande
    foreach ($this->order->products as $product) {
        $mail->line("- {$product->nom} (Qté: {$product->pivot->quantity}) - " . number_format($product->prix_promo ?? $product->prix_unitaire, 0, ',', ' ') . " F CFA");
    }

    return $mail
        ->line('---')
        ->action('Gérer la commande', url('http://localhost:5173/dashbord/super@dmin/electroshop'))
        ->line('Merci de traiter cette commande dans les plus brefs délais.')
        ->salutation('Cordialement, Système ElectroShop');
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
