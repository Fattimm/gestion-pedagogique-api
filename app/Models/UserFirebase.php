<?php

namespace App\Models;

use App\Traits\EloquentTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFirebase extends FirebaseModel
{
    use SoftDeletes, HasFactory; 

    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'telephone',
        'fonction',
        'email',
        'photo',
        'statut',
        'login',
        'role',

    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

     // Ajoutez cette méthode pour gérer les rôles
     public function hasRole($role)
     {
         return $this->role === $role;
     }
 
     // Vous pouvez également avoir une méthode pour vérifier plusieurs rôles
     public function hasAnyRole(array $roles)
     {
         return in_array($this->role, $roles);
     }

}
