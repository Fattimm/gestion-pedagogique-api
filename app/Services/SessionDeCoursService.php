<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\CoursRepositoryInterface;
use App\Repositories\Interfaces\SessionDeCoursRepositoryInterface;
use App\Services\Interfaces\SessionDeCoursServiceInterface;

class SessionDeCoursService implements SessionDeCoursServiceInterface
{
    public function __construct(
        protected SessionDeCoursRepositoryInterface $sessionRepo,
        protected CoursRepositoryInterface $coursRepo
    ) {}

    public function lister(array $filters = [])
    {
        return $this->sessionRepo->all($filters);
    }

    public function trouver(int $id)
    {
        return $this->sessionRepo->find($id);
    }

    public function listerParCours(int $coursId)
    {
        return $this->sessionRepo->findByCours($coursId);
    }

    public function creer(array $data)
    {
        DB::beginTransaction();
        try {
            $cours = $this->coursRepo->find($data['cours_id']);

            // Vérification du quota horaire
            $heuresPlanifiees = $this->sessionRepo->heuresPlanifieesParCours($cours->id);
            if (($heuresPlanifiees + $data['nbre_heure']) > $cours->quota_horaire_global) {
                $restantes = $cours->quota_horaire_global - $heuresPlanifiees;
                return ['status' => 422, 'message' => "Quota horaire dépassé. Il reste {$restantes}h disponibles sur ce cours."];
            }

            // Vérification de la disponibilité du professeur
            if ($this->sessionRepo->professeurOccupe($cours->professeur_id, $data['date'], $data['heure_debut'], $data['heure_fin'])) {
                return ['status' => 422, 'message' => 'Le professeur a déjà une session planifiée sur ce créneau.'];
            }

            // Vérification de la disponibilité de la salle (en présentiel uniquement)
            if ($data['type'] === 'presentiel' && !empty($data['salle_id'])) {
                if ($this->sessionRepo->salleOccupee($data['salle_id'], $data['date'], $data['heure_debut'], $data['heure_fin'])) {
                    return ['status' => 422, 'message' => 'La salle est déjà occupée sur ce créneau.'];
                }
            }

            $session = $this->sessionRepo->create($data);
            DB::commit();
            return ['status' => 201, 'data' => $session->load(['cours.module', 'salle']), 'message' => 'Session créée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $session = $this->sessionRepo->find($id);
            $cours   = $this->coursRepo->find($session->cours_id);

            if (isset($data['nbre_heure'])) {
                $heuresPlanifiees = $this->sessionRepo->heuresPlanifieesParCours($cours->id);
                $heuresSansActuelle = $heuresPlanifiees - $session->nbre_heure;
                if (($heuresSansActuelle + $data['nbre_heure']) > $cours->quota_horaire_global) {
                    $restantes = $cours->quota_horaire_global - $heuresSansActuelle;
                    return ['status' => 422, 'message' => "Quota horaire dépassé. Il reste {$restantes}h disponibles."];
                }
            }

            $date       = $data['date']       ?? $session->date;
            $heureDebut = $data['heure_debut'] ?? $session->heure_debut;
            $heureFin   = $data['heure_fin']   ?? $session->heure_fin;

            if ($this->sessionRepo->professeurOccupe($cours->professeur_id, $date, $heureDebut, $heureFin, $id)) {
                return ['status' => 422, 'message' => 'Le professeur a déjà une session planifiée sur ce créneau.'];
            }

            $salleId = $data['salle_id'] ?? $session->salle_id;
            $type    = $data['type']     ?? $session->type;
            if ($type === 'presentiel' && $salleId) {
                if ($this->sessionRepo->salleOccupee($salleId, $date, $heureDebut, $heureFin, $id)) {
                    return ['status' => 422, 'message' => 'La salle est déjà occupée sur ce créneau.'];
                }
            }

            $updated = $this->sessionRepo->update($id, $data);
            DB::commit();
            return ['status' => 200, 'data' => $updated, 'message' => 'Session modifiée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function annuler(int $id)
    {
        try {
            $session = $this->sessionRepo->update($id, ['statut' => 'annulee']);
            return ['status' => 200, 'data' => $session, 'message' => 'Session annulée avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }
}
