<?php

namespace App\Policies;

use App\Models\ShortUrl;
use App\Models\User;

// Authorization rules for short URLs.
// Note: SuperAdmin can't create (they don't belong to a single company), but
// they can delete -- they're a global admin.
class ShortUrlPolicy
{
    public function create(User $user)
    {
        return in_array($user->role, ['Admin', 'Member', 'Sales', 'Manager'], true);
    }

    public function delete(User $user, ShortUrl $shortUrl)
    {
        // SuperAdmin: full power.
        if ($user->role === 'SuperAdmin') {
            return true;
        }

        // Admin: can delete anything in their own company.
        if ($user->role === 'Admin') {
            return $user->company_id === $shortUrl->company_id;
        }

        // Members/Sales/Manager: only their own.
        return $user->id === $shortUrl->user_id;
    }
}
