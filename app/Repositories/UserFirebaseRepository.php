<?php
namespace App\Repositories;

use Kreait\Firebase\Firestore;
use App\Repositories\Interfaces\UserFirebaseRepositoryInterface;

class UserFirebaseRepository implements UserFirebaseRepositoryInterface
{
    protected $firestore;

    public function __construct(?Firestore $firestore)
    {
        $this->firestore = $firestore;
    }

    private function firebaseDisponible(): bool
    {
        return $this->firestore !== null;
    }

    public function store(array $data)
    {
        if (!$this->firebaseDisponible()) {
            return null;
        }

        if ($this->emailExists($data['email']) || $this->phoneExists($data['telephone'])) {
            throw new \Exception("Un utilisateur avec cet email ou ce numéro de téléphone existe déjà.");
        }

        $document = $this->firestore->database()->collection('users')->add($data);
        return $document->id();
    }

    private function emailExists(string $email): bool
    {
        $users = $this->firestore->database()->collection('users')
            ->where('email', '=', $email)->documents();
        return !$users->isEmpty();
    }

    private function phoneExists(string $phone): bool
    {
        $users = $this->firestore->database()->collection('users')
            ->where('telephone', '=', $phone)->documents();
        return !$users->isEmpty();
    }

    public function update(string $id, array $data)
    {
        if (!$this->firebaseDisponible()) return null;
        $this->firestore->database()->collection('users')->document($id)->set($data, ['merge' => true]);
    }

    public function delete(string $id)
    {
        if (!$this->firebaseDisponible()) return null;
        $this->firestore->database()->collection('users')->document($id)->delete();
    }

    public function find(string $id)
    {
        if (!$this->firebaseDisponible()) return null;
        $document = $this->firestore->database()->collection('users')->document($id)->snapshot();
        return $document->exists() ? $document->data() : null;
    }

    public function all(array $filters = [])
    {
        if (!$this->firebaseDisponible()) return [];
        $reference = $this->firestore->database()->collection('users');
        if (!empty($filters['role'])) {
            $reference = $reference->where('role', '=', $filters['role']);
        }
        $users = [];
        foreach ($reference->documents() as $document) {
            if ($document->exists()) {
                $users[] = array_merge(['id' => $document->id()], $document->data());
            }
        }
        return $users;
    }

    public function filterByRole(string $role)
    {
        if (!$this->firebaseDisponible()) return [];
        $users = [];
        foreach ($this->firestore->database()->collection('users')->where('role', '=', $role)->documents() as $document) {
            if ($document->exists()) {
                $users[] = array_merge(['id' => $document->id()], $document->data());
            }
        }
        return $users;
    }

}
