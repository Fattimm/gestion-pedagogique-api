<?php

namespace App\Repositories;

use Exception;
use App\Models\Referentiel;
use Kreait\Firebase\Firestore;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\ReferentielRepositoryInterface;

class ReferentielRepository implements ReferentielRepositoryInterface
{
    protected $firestore;
    protected $firebaseCollection = 'referentiels';

    public function __construct()
    {
        $this->firestore = app('firebase.firestore')->database();
    }

    public function create(array $data): Referentiel
    {
        Log::info('Tentative de création dans Firebase avec les données : ', $data);

        $document = $this->firestore->collection($this->firebaseCollection)->add($data);

        Log::info('Réponse de Firebase après création : ', ['document_id' => $document->id()]);

        $referentiel = new Referentiel($data);
        $referentiel->setId($document->id());

        Log::info('Référentiel créé : ', $referentiel->toArray());

        return $referentiel;
    }

    public function findByCodeOrLibelle($code, $libelle): ?Referentiel
    {
        // Rechercher dans Firestore
        $query = $this->firestore->collection($this->firebaseCollection);

        $snapshotCode = $query->where('code', '=', $code)->documents();
        $snapshotLibelle = $query->where('libelle', '=', $libelle)->documents();

        foreach ($snapshotCode as $document) {
            if ($document->exists()) {
                $referentiel = new Referentiel($document->data());
                $referentiel->setId($document->id());
                return $referentiel;
            }
        }

        foreach ($snapshotLibelle as $document) {
            if ($document->exists()) {
                $referentiel = new Referentiel($document->data());
                $referentiel->setId($document->id());
                return $referentiel;
            }
        }

        return null;
    }


    public function all(): Collection
    {
        Log::info('Récupération de tous les référentiels depuis Firestore');

        $snapshot = $this->firestore->collection($this->firebaseCollection)->documents();
        $referentiels = new Collection();

        foreach ($snapshot as $document) {
            if ($document->exists()) {
                $data = $document->data();
                $referentiel = new Referentiel($data);
                $referentiel->setId($document->id());
                $referentiels->push($referentiel);
            }
        }

        Log::info('Nombre de référentiels récupérés : ' . $referentiels->count());

        return $referentiels;
    }

    public function getAllFromFirestore()
    {
        return $this->firestore->collection($this->firebaseCollection)->documents();
    }


    public function findByStatut($etat): Collection
    {
        $query = $this->firestore->collection($this->firebaseCollection);
        $snapshot = $query->where('statut', '=', $etat)->documents();

        $referentiels = new Collection();
        foreach ($snapshot as $document) {
            if ($document->exists()) {
                $data = $document->data();
                $referentiel = new Referentiel($data);
                $referentiel->setId($document->id());
                $referentiels->push($referentiel);
            }
        }
        return $referentiels;
    }



    public function addCompetence($referentielId, $type, array $competenceData)
    {
        // Référence à la collection des compétences pour un référentiel spécifique
        $competenceRef = $this->firestore->collection('referentiels')->document($referentielId)
            ->collection('competences');

        // Ajouter une compétence
        $competenceRef->add([
            'type' => $type,
            'data' => $competenceData
        ]);
    }


    public function addModule($referentielId, $type, $competenceId, array $moduleData)
    {
        // Référence à la collection des modules pour une compétence donnée
        $moduleRef = $this->firestore->collection('referentiels')->document($referentielId)
            ->collection('competences')->document($competenceId)
            ->collection('modules');

        // Ajouter un module
        $moduleRef->add([
            'type' => $type,
            'data' => $moduleData
        ]);
    }



    public function find($id)
    {
        $document = $this->firestore->collection($this->firebaseCollection)->document($id)->snapshot();

        if ($document->exists()) {
            $referentiel = new Referentiel($document->data());
            $referentiel->setId($document->id());
            return $referentiel;
        }

        return null; // Retourne null si le document n'existe pas
    }

    public function getCompetences($referentielId): array
    {
        $referentielDoc = $this->firestore->collection('referentiels')->document($referentielId);

        // Vérifiez si le document existe
        if (!$referentielDoc->exists()) {
            return [];
        }

        $data = $referentielDoc->data();

        // Décodez la chaîne JSON des compétences
        $competencesJson = $data['competenses'];
        $competences = json_decode($competencesJson, true); // Décoder en tableau associatif

        // Ajouter l'ID du document pour chaque compétence
        foreach ($competences as &$competence) {
            $competence['id'] = uniqid(); // Vous pouvez ajuster cette logique pour gérer les IDs selon votre besoin
        }

        return $competences; // Retourner un tableau de compétences
    }

    public function getModules($referentielId, $competenceId): array
    {
        $modulesDoc = $this->firestore->collection('referentiels')->document($referentielId)
            ->collection('competences')->document($competenceId)->collection('modules')->documents();

        $moduleCollection = [];

        foreach ($modulesDoc as $document) {
            if ($document->exists()) {
                $data = $document->data();
                $data['id'] = $document->id(); // Ajoute l'ID du document
                $moduleCollection[] = $data; // Ajouter le module au tableau
            }
        }

        return $moduleCollection; // Retourner un tableau de modules
    }


    public function getId()
    {
        return $this->firestore->collection($this->firebaseCollection)->document()->id();
    }


    public function softDelete($id)
    {
        $document = $this->firestore->collection($this->firebaseCollection)->document($id)->snapshot();

        if ($document->exists()) {
            // Assuming you have a 'deleted' field to mark the document as deleted
            $this->firestore->collection($this->firebaseCollection)->document($id)->update([
                ['path' => 'deleted', 'value' => true]
            ]);
            Log::info('Référentiel marqué comme supprimé : ', ['id' => $id]);
        } else {
            Log::warning('Tentative de suppression d\'un référentiel inexistant : ', ['id' => $id]);
            throw new Exception('Le référentiel n\'existe pas.');
        }
    }

//     public function softDelete($referentielId)
// {
//     $referentiel = $this->find($referentielId);
//     if ($referentiel) {
//         // Mettez à jour le champ 'deleted_at' pour le soft delete
//         $referentiel->deleted_at = now();
//         return $referentiel->save();
//     }
//     return false;
// }



    public function isReferentielInPromotion($referentielId): bool
    {
        // Implement the logic to query your promotions collection
        // Example query:
        $snapshot = $this->firestore->collection('promotions')->where('referentielId', '=', $referentielId)->documents();

        return $snapshot->isEmpty(); // Return true if it is not in use, false otherwise
    }


    public function update(string $id, array $data): void
    {
        $document = $this->firestore->collection($this->firebaseCollection)->document($id);

        // Firestore utilise une structure de tableau pour les mises à jour
        $updateData = [];
        foreach ($data as $key => $value) {
            $updateData[] = ['path' => $key, 'value' => $value];
        }

        $document->update($updateData);
    }




    public function softDeleteCompetence(string $referentielId, string $competenceId): void
    {
        $competenceRef = $this->firestore->collection($this->firebaseCollection)
            ->document($referentielId)
            ->collection('competences')
            ->document($competenceId);

        $competenceRef->update([
            ['path' => 'deleted', 'value' => true]
        ]);

        Log::info("Competence soft deleted: $competenceId");
    }


    public function softDeleteModule(string $referentielId, string $competenceId, string $moduleId): void
    {
        $moduleRef = $this->firestore->collection($this->firebaseCollection)
            ->document($referentielId)
            ->collection('competences')
            ->document($competenceId)
            ->collection('modules')
            ->document($moduleId);

        $moduleRef->update([
            ['path' => 'deleted', 'value' => true]
        ]);

        Log::info("Module soft deleted: $moduleId");
    }

    public function getCompetencesByReferentielId($referentielId)
    {
        $referentiel = $this->firestore->collection('referentiels')->document($referentielId)->snapshot();
        if ($referentiel->exists()) {
            $data = $referentiel->data();
            return $data['competences'] ?? [];
        }
        return [];
    }
}
