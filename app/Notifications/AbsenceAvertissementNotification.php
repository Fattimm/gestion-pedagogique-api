<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AbsenceAvertissementNotification extends Notification
{
    public function __construct(public float $totalHeures) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Avertissement — Seuil d\'absences atteint')
            ->greeting("Bonjour {$notifiable->prenom} {$notifiable->nom},")
            ->line("Vous avez atteint {$this->totalHeures}h d'absences ce semestre.")
            ->line('Nous vous invitons à régulariser votre situation.')
            ->line('Merci de contacter votre responsable pédagogique.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'avertissement_absence',
            'total_heures' => $this->totalHeures,
            'message'      => "Vous avez atteint {$this->totalHeures}h d'absences ce semestre.",
        ];
    }
}
