<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AbsenceConvocationNotification extends Notification
{
    public function __construct(public float $totalHeures) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Lettre de convocation — Absences excessives')
            ->greeting("Bonjour {$notifiable->prenom} {$notifiable->nom},")
            ->line("Vous avez cumulé {$this->totalHeures}h d'absences ce semestre.")
            ->line('Vous êtes convoqué(e) auprès du Responsable Pédagogique.')
            ->line('Veuillez vous présenter dans les plus brefs délais.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'convocation_absence',
            'total_heures' => $this->totalHeures,
            'message'      => "Convocation : vous avez cumulé {$this->totalHeures}h d'absences.",
        ];
    }
}
