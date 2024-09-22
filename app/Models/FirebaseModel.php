<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Kreait\Laravel\Firebase\Facades\Firebase;

abstract class FirebaseModel extends Model implements FirebaseModelInterface
{
    protected $firestore;
    protected $shouldSyncToFirebase = true;
    protected $firebaseCollection;
    protected $id;
    protected $fillable = [];
    protected $hidden = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->firestore = Firebase::firestore();
        $this->firebaseCollection = $this->getFirebaseCollectionName();
    }


    public function syncToFirebase(string $operation = 'update')
    {
        if (!$this->shouldSyncToFirebase) {
            return;
        }

        try {
            $collection = $this->firestore->database()->collection($this->firebaseCollection);
            $document = $collection->document($this->getFirebaseDocumentId());
            $data = $this->toArray();

            if ($operation === 'create') {
                $document->set($data);
            } else {
                $document->update($data);
            }

            $this->logFirebaseSyncSuccess($operation);
        } catch (\Exception $e) {
            $this->logFirebaseSyncError($e, $operation);
        }
    }


    public function deleteFromFirebase()
    {
        if (!$this->shouldSyncToFirebase) {
            return;
        }

        try {
            $collection = $this->firestore->database()->collection($this->firebaseCollection);
            $document = $collection->document($this->getFirebaseDocumentId());
            $document->delete();
            $this->logFirebaseDeleteSuccess();
        } catch (\Exception $e) {
            $this->logFirebaseDeleteError($e);
        }
    }

    protected function getFirebaseDocumentId(): string
    {
        return (string)$this->id;
    }

    protected function logFirebaseSyncSuccess(string $operation): void
    {
        Log::info("Model " . get_class($this) . " (ID: {$this->id}) successfully {$operation}d to Firebase.");
    }

    protected function logFirebaseSyncError(\Exception $e, string $operation): void
    {
        Log::error("Error {$operation}ing " . get_class($this) . " (ID: {$this->id}) to Firebase: " . $e->getMessage());
    }

    protected function logFirebaseDeleteSuccess(): void
    {
        Log::info("Model " . get_class($this) . " (ID: {$this->id}) successfully deleted from Firebase.");
    }

    protected function logFirebaseDeleteError(\Exception $e): void
    {
        Log::error("Error deleting " . get_class($this) . " (ID: {$this->id}) from Firebase: " . $e->getMessage());
    }

    public function disableFirebaseSync(): self
    {
        $this->shouldSyncToFirebase = false;
        return $this;
    }

    public function enableFirebaseSync(): self
    {
        $this->shouldSyncToFirebase = true;
        return $this;
    }

    public function setFirebaseCollection(string $collection): self
    {
        $this->firebaseCollection = $collection;
        return $this;
    }

    protected function getFirebaseCollectionName(): string
    {
        return $this->firebaseCollection ?? strtolower(class_basename($this));
    }


    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }    

    public function create(array $data): self
    {
        $document = $this->firestore->database()->collection($this->firebaseCollection)->add($data);
        $this->setId($document->id());
        $this->syncToFirebase('create');
        return $this;
    }

    public function read(string $id): ?self
    {
        $document = $this->firestore->database()->collection($this->firebaseCollection)->document($id)->snapshot();

        if ($document->exists()) {
            $this->setId($id);
            // Assurez-vous de définir les attributs appropriés à partir des données
            // $this->libelle = $document->get('libelle'); // Exemple
            return $this;
        }

        return null;
    }

    public function updateFirebase(array $data): self
    {
        $this->firestore->database()->collection($this->firebaseCollection)->document($this->getFirebaseDocumentId())->set($data, ['merge' => true]);
        $this->syncToFirebase('update');
        return $this;
    }


    public function delete(): void
    {
        $this->deleteFromFirebase();
    }

    public function toArray(): array
    {
        // On récupère toutes les propriétés définies dans $fillable
        $data = [];

        foreach ($this->fillable as $attribute) {
            if (property_exists($this, $attribute)) {
                $data[$attribute] = $this->{$attribute};
            }
        }

        // Supprimer les attributs définis dans $hidden
        return Arr::except($data, $this->hidden);
    }


}