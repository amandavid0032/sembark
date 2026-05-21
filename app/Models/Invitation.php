<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Invitation row. Stays around after acceptance so we can show "accepted by X".
class Invitation extends Model
{
    protected $fillable = [
        'company_id', 'email', 'role', 'token', 'invited_by',
        'accepted_at', 'accepted_user_id',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Who sent the invite (FK on users.id).
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // The user that was created when this invite was accepted (null while pending).
    public function acceptedUser()
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}
