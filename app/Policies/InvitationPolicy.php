<?php

namespace App\Policies;

use App\Models\User;

// Who can invite whom:
//  - SuperAdmin: invites Admin only (to seed a new tenant company).
//  - Admin     : invites Member/Sales/Manager into their own company.
//                CANNOT invite another Admin (would create two admins for one company).
class InvitationPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['SuperAdmin', 'Admin'], true);
    }

    public function create(User $user, string $role)
    {
        if ($user->role === 'SuperAdmin') {
            return $role === 'Admin';
        }

        if ($user->role === 'Admin') {
            return in_array($role, ['Member', 'Sales', 'Manager'], true);
        }

        // Member/Sales/Manager can't invite anyone.
        return false;
    }
}
