<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\UserFirebaseService;
use App\Http\Requests\StoreUserRequest;

class UserController extends Controller
{
    protected $userService;
    protected $userFirebaseService;

    public function __construct(UserService $userService, UserFirebaseService $userFirebaseService)
    {
        $this->userService = $userService;
        $this->userFirebaseService = $userFirebaseService;
    }

    public function store(StoreUserRequest $request)
    {
        // Créer un utilisateur dans Firebase
        $firebaseResponse = $this->userFirebaseService->createUser($request->validated());

        if ($firebaseResponse['status'] !== 201) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur dans Firebase',
                'firebase_user' => $firebaseResponse
            ], $firebaseResponse['status']);
        }

        // Récupérer l'ID de l'utilisateur créé dans Firebase
        $firebaseUserId = $firebaseResponse['data'];

        // Créer un utilisateur dans la base locale avec l'ID de Firebase
        $localUser = $this->userService->createUser($request, $firebaseUserId);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'local_user' => $localUser,
            'firebase_user' => $firebaseResponse
        ]);
    }

    public function index(Request $request)
    {
        $role = $request->input('role');
    
        // Lister les utilisateurs de la base locale
        $localUsers = $this->userService->listUsers(['role' => $role]);
    
        // Lister les utilisateurs de Firebase
        $firebaseUsers = $this->userFirebaseService->listUsers(['role' => $role]);
    
        // Fusionner les utilisateurs locaux et Firebase
        $allUsers = $localUsers->toArray(); // Convertit la collection Eloquent en tableau
        $allUsers = array_merge($allUsers, $firebaseUsers);
    
        return response()->json([
            'message' => 'Liste des utilisateurs récupérée avec succès',
            'users' => $allUsers
        ]);
    }



    public function update(Request $request, $id)
{
    // Récupérer l'utilisateur local
    $localUser = User::findOrFail($id);

    // Vérification des permissions pour modifier un utilisateur
    $this->authorize('update', $localUser);

    // Extraire l'ID Firebase de l'utilisateur local
    $firebaseId = $localUser->firebase_id;

    // Mettre à jour l'utilisateur dans la base locale
    $updatedLocalUser = $this->userService->updateUser($id, $request->all());

    // Mettre à jour l'utilisateur dans Firebase
    $updatedFirebaseUser = $this->userFirebaseService->updateUser($firebaseId, $request->all());

    return response()->json([
        'message' => 'Utilisateur mis à jour avec succès',
        'local_user' => $updatedLocalUser,
        'firebase_user' => $updatedFirebaseUser
    ]);
}


    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Vérification des permissions pour supprimer un utilisateur
        $this->authorize('delete', $user);

        // Suppression de l'utilisateur de la base locale
        $this->userService->deleteUser($id);

        // Suppression de l'utilisateur de Firebase
        $this->userFirebaseService->deleteUser($id);

        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }

    public function show($id)
    {
        // Récupérer l'utilisateur de la base locale
        $localUser = $this->userService->getUserDetails($id);

        // Récupérer l'utilisateur de Firebase
        $firebaseUser = $this->userFirebaseService->getUserById($id);

        return response()->json([
            'local_user' => $localUser,
            'firebase_user' => $firebaseUser
        ]);
    }
}
