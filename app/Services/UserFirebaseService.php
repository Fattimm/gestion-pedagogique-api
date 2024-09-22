<?php

namespace App\Services;

use Exception;
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

    public function listUsers(array $filters = [])
    {
        return $this->userRepository->all($filters);
    }


    public function updateUser(string $firebaseId, array $data)
    {
        try {
            // Vérifier si une photo est présente et uploader si nécessaire
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                $uploadService = new UploadPhotoFirebaseService(app('firebase.storage'));
                $data['photo_url'] = $uploadService->uploadPhoto($data['photo']);
            }

            // Mettre à jour l'utilisateur dans Firebase
            $this->userRepository->update($firebaseId, $data);

            return [
                'status' => 200,
                'message' => 'Utilisateur mis à jour avec succès dans Firebase'
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur dans Firebase : ' . $e->getMessage()
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

    public function getUserById(string $id)
    {
        return $this->userRepository->find($id);
    }

    public function filterUsersByRole(string $role)
    {
        return $this->userRepository->filterByRole($role);
    }


    // protected function addUserToRoleCollection(string $userId, string $role)
    // {
    //     // Chemin de la sous-collection pour le rôle
    //     $roleCollectionPath = "users/roles/{$role}"; // Utilisez 'roles' comme sous-collection sous 'users'

    //     // Données utilisateur
    //     $data = [
    //         'userId' => $userId,
    //         // Ajoutez d'autres données utilisateur si nécessaire, comme le nom, l'email, etc.
    //     ];

    //     // Vérifiez et ajoutez l'utilisateur à la sous-collection de son rôle
    //     app('firebase.firestore')->collection($roleCollectionPath)->document($userId)->set($data);
    // }

}
