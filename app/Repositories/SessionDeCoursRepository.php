<?php

namespace App\Repositories;

use App\Models\SessionDeCours;
use App\Repositories\Interfaces\SessionDeCoursRepositoryInterface;

class SessionDeCoursRepository implements SessionDeCoursRepositoryInterface
{
    public function all()
    {
        return SessionDeCours::with(['cours.module', 'cours.professeur', 'salle'])->get();
    }

    public function find(int $id)
    {
        return SessionDeCours::with(['cours.module', 'cours.professeur', 'salle'])->findOrFail($id);
    }

    public function findByCours(int $coursId)
    {
        return SessionDeCours::with(['salle'])
            ->where('cours_id', $coursId)
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get();
    }

    public function create(array $data)
    {
        return SessionDeCours::create($data);
    }

    public function update(int $id, array $data)
    {
        $session = SessionDeCours::findOrFail($id);
        $session->update($data);
        return $session;
    }

    public function delete(int $id)
    {
        $session = SessionDeCours::findOrFail($id);
        return $session->delete();
    }

    public function professeurOccupe(int $professeurId, string $date, string $heureDebut, string $heureFin, ?int $excludeId = null): bool
    {
        return SessionDeCours::whereHas('cours', fn($q) => $q->where('professeur_id', $professeurId))
            ->where('date', $date)
            ->where('statut', '!=', 'annulee')
            ->where(fn($q) => $q
                ->whereBetween('heure_debut', [$heureDebut, $heureFin])
                ->orWhereBetween('heure_fin', [$heureDebut, $heureFin])
                ->orWhere(fn($q2) => $q2->where('heure_debut', '<=', $heureDebut)->where('heure_fin', '>=', $heureFin))
            )
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function salleOccupee(int $salleId, string $date, string $heureDebut, string $heureFin, ?int $excludeId = null): bool
    {
        return SessionDeCours::where('salle_id', $salleId)
            ->where('date', $date)
            ->where('statut', '!=', 'annulee')
            ->where(fn($q) => $q
                ->whereBetween('heure_debut', [$heureDebut, $heureFin])
                ->orWhereBetween('heure_fin', [$heureDebut, $heureFin])
                ->orWhere(fn($q2) => $q2->where('heure_debut', '<=', $heureDebut)->where('heure_fin', '>=', $heureFin))
            )
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function heuresPlanifieesParCours(int $coursId): float
    {
        return (float) SessionDeCours::where('cours_id', $coursId)
            ->whereIn('statut', ['planifiee', 'effectuee'])
            ->sum('nbre_heure');
    }
}
