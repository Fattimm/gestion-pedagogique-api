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
        return $this->hasAnyRole($user, ['ADMIN', 'MANAGER', 'CM']);
    }

    public function update(User $user, User $targetUser)
    {
        return $user->role === 'ADMIN';
    }

    public function view(User $user)
    {
        return $this->hasAnyRole($user, ['ADMIN', 'MANAGER', 'CM']);
    }

    protected function hasAnyRole(User $user, array $roles)
    {
        return in_array($user->role, $roles);
    }
}
