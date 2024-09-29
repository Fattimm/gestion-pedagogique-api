<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use App\Services\UploadPhotoFirebaseService;
use App\Services\Interfaces\UserFirebaseServiceInterface;
use App\Repositories\Interfaces\UserFirebaseRepositoryInterface;

class UserFirebaseService implements UserFirebaseServiceInterface
{

    protected $userRepository;
    protected $uploadPhotoService;


    public function __construct(UserFirebaseRepositoryInterface $userRepository, UploadPhotoFirebaseService $uploadPhotoService)
    {
        $this->userRepository = $userRepository;
        $this->uploadPhotoService = $uploadPhotoService;
    }

    public function createUser(array $data)
    {
        try {
            // Vérifier si une photo existe et l'uploader
            $photoUrl = null;
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $uploadService = new UploadPhotoFirebaseService(app('firebase.storage'));
                $photoUrl = $uploadService->uploadPhoto($data['photo']);
            }

            // Préparer les données de l'utilisateur
            $userData = array_diff_key($data, ['photo' => '']);
            $userData['photo_url'] = $photoUrl;

            // Tenter de créer l'utilisateur
            $userId = $this->userRepository->store($userData);

            // Ajouter l'utilisateur dans la collection selon son rôle
            $role = $userData['role']; // Assurez-vous que le rôle est inclus dans les données utilisateur
            $this->addUserToRoleDocument($role, $userId, $userData);

            return [
                'status' => 201,
                'data' => $userId,
                'message' => 'Utilisateur créé avec succès'
            ];
        } catch (\Exception $e) {
            // Gérer l'exception spécifique pour l'utilisateur existant
            if (strpos($e->getMessage(), "Un utilisateur avec cet email ou ce numéro de téléphone existe déjà") !== false) {
                return [
                    'status' => 409, // Conflict
                    'data' => null,
                    'message' => $e->getMessage()
                ];
            }

            // Gérer les autres exceptions
            return [
                'status' => 500,
                'data' => null,
                'message' => 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage()
            ];
        }
    }

    private function addUserToRoleDocument($role, $userId, array $userData)
    {
        $firestore = app('firebase.firestore')->database(); // Assurez-vous que vous avez configuré le Firestore correctement

        // Vérifiez si le document de rôle existe déjà dans la collection 'users'
        $roleDocument = $firestore->collection('users')->document($role);

        // Ajouter l'utilisateur directement comme document dans le document de rôle
        $roleDocument->set([$userId => $userData], ['merge' => true]); // Utilisez 'merge' pour ne pas écraser le document de rôle
    }



    public function listUsers(array $filters = [])
    {
        return $this->userRepository->all($filters);
    }



    public function updateUser(string $id, array $data)
    {
        try {
            // Récupérer l'utilisateur local
            $user = User::findOrFail($id);
            if (!$user) {
                throw new \Exception("Utilisateur local non trouvé.");
            }

            $firebaseId = $user->firebase_id;
            if (!$firebaseId) {
                throw new \Exception("ID Firebase non trouvé pour cet utilisateur.");
            }

            // Vérifier si une photo est présente et uploader si nécessaire
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $data['photo_url'] = $this->uploadPhotoService->uploadPhoto($data['photo']);
                unset($data['photo']); // Retirer la photo du tableau de données
            }

            // Mettre à jour l'utilisateur dans Firebase
            $this->userRepository->update($firebaseId, $data);

            // Mettre à jour l'utilisateur local
            $user->update($data);

            return [
                'status' => 200,
                'message' => 'Utilisateur mis à jour avec succès dans Firebase et localement'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur : ' . $e->getMessage()
            ];
        }
    }

    public function deleteUser(string $id)
    {
        try {
            return $this->userRepository->delete($id);
        } catch (Exception $e) {
            return [
                'status' => 500,
                'data' => null,
                'message' => 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage()
            ];
        }
    }
}
