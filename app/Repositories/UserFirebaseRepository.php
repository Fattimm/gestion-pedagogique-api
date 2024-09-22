<?php
namespace App\Repositories;

use Kreait\Firebase\Firestore;
use App\Repositories\Interfaces\UserFirebaseRepositoryInterface;

class UserFirebaseRepository implements UserFirebaseRepositoryInterface
{
    protected $firestore;

    public function __construct(Firestore $firestore)
    {
        $this->firestore = $firestore;
    }

    public function store(array $data)
    {
        if ($this->emailExists($data['email']) || $this->phoneExists($data['telephone'])) {
            throw new \Exception("Un utilisateur avec cet email ou ce numéro de téléphone existe déjà.");
        }

        $document = $this->firestore->database()->collection('users')->add($data);
        return $document->id();
    }

    private function emailExists(string $email): bool
    {
        $users = $this->firestore->database()->collection('users')
            ->where('email', '=', $email)
            ->documents();

        return !$users->isEmpty();
    }

    private function phoneExists(string $phone): bool
    {
        $users = $this->firestore->database()->collection('users')
            ->where('telephone', '=', $phone)
            ->documents();

        return !$users->isEmpty();
    }

    public function update(string $id, array $data)
    {
        $this->firestore->database()->collection('users')->document($id)->set($data, ['merge' => true]);
    }

    public function delete(string $id)
    {
        $this->firestore->database()->collection('users')->document($id)->delete();
    }

    public function find(string $id)
    {
        $document = $this->firestore->database()->collection('users')->document($id)->snapshot();
        
        if ($document->exists()) {
            return $document->data();
        }

        return null;
    }

    public function all(array $filters = [])
    {
        $reference = $this->firestore->database()->collection('users');
        
        // Appliquer les filtres si nécessaire
        if (!empty($filters['role'])) {
            $reference = $reference->where('role', '=', $filters['role']);
        }

        $documents = $reference->documents();

        $users = [];
        foreach ($documents as $document) {
            if ($document->exists()) {
                $users[] = array_merge(['id' => $document->id()], $document->data());
            }
        }

        return $users;
    }


    public function filterByRole(string $role)
    {
        $reference = $this->firestore->database()->collection('users')
            ->where('role', '=', $role);
        
        $documents = $reference->documents();
        $users = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $users[] = array_merge(['id' => $document->id()], $document->data());
            }
        }

        return $users;
    }

}
