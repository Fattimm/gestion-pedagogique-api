<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function delete(User $user)
    {
        return $user->role === 'ADMIN';
    }
    public function create(User $user)
    {
        return $user->isAdmin() || $user->isManager() || $user->isCM();
    }

    public function update(User $user, User $targetUser)
    {
        return $user->isAdmin();
    }

    public function view(User $user)
    {
        return $user->isAdmin() || $user->isManager() || $user->isCM();
    }

    public function isAdmin(User $user)
    {
        return  $user->role === 'ADMIN';
    }

    public function isManager(User $user)
    {
        return  $user->role === 'MANAGER';
    }

    public function isCM(User $user)
    {
        return  $user->role === 'CM';
    }

    public function isCoach(User $user)
    {
        return  $user->role === 'COACH';
    }

    public function isApprenant(User $user)
    {
        return  $user->role === 'APPRENANT';
    }
}
