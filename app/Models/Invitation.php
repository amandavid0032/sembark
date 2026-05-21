<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Pending invitation row. Gets deleted when the invitee accepts the link.
class Invitation extends Model
{
    protected $fillable = ['company_id', 'email', 'role', 'token', 'invited_by'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Who sent the invite (FK on users.id).
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
