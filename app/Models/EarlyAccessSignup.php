<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyAccessSignup extends Model
{
    public const INTERESTS = ['buyer', 'owner', 'investor'];

    protected $fillable = ['email', 'name', 'interest', 'source', 'ip_hash', 'user_agent', 'consented_at', 'invited_at'];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'invited_at' => 'datetime',
        ];
    }
}
