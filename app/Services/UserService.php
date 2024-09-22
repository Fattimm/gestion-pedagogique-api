<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Storage;
use App\Services\Interfaces\UserServiceInterface;

class UserService implements UserServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(StoreUserRequest $request, $firebaseId = null)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Gestion de la photo
            if ($request->hasFile('photo')) {
                $userPhoto = $request->file('photo');
                // Stockage de la photo avec son nom original
                $photoLocalPath = $userPhoto->storeAs('public/photos', $userPhoto->getClientOriginalName());
                $data['photo'] = $photoLocalPath; // Stocke le chemin relatif
            } else {
                $data['photo'] = null; // Assurez-vous que la colonne est définie si pas de photo
            }

            // Ajoutez l'ID de Firebase aux données de l'utilisateur
            if ($firebaseId) {
                $data['firebase_id'] = $firebaseId;
            }

            // Création de l'utilisateur
            $user = $this->userRepository->create(array_merge($data, [
                'password' => Hash::make($data['password']),
            ]));

            DB::commit();

            // Ajoute l'URL complète de la photo à la réponse
            if ($user->photo) {
                $user->photo_url = Storage::url($user->photo);
            }

            return [
                'status' => 201,
                'data' => $user,
                'message' => 'Utilisateur créé avec succès dans la base locale'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => 500,
                'message' => 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage()
            ];
        }
    }

    public function updateUser(string $id, array $data)
    {
        DB::beginTransaction();

        try {
            // Vérifier si un mot de passe est fourni et le hacher
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Mettre à jour l'utilisateur local
            $user = $this->userRepository->update($id, $data);

            DB::commit();

            return [
                'status' => 200,
                'data' => $user,
                'message' => 'Utilisateur mis à jour avec succès dans la base locale'
            ];
        } catch (Exception $e) {
            DB::rollBack();
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
            throw new Exception('Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage());
        }
    }

    public function listUsers(array $data = [])
    {
        try {
            if (isset($data['role'])) {
                return $this->userRepository->findByRole($data['role']);
            } else {
                return $this->userRepository->All();
            }
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la récupération des utilisateurs : ' . $e->getMessage());
        }
    }


    public function getUserDetails($id)
    {
        return $this->userRepository->find($id);
    }
}
