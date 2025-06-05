<?php

namespace App\Models;

use App\Relations\HasMany;

class User extends Model
{
    protected string $table = DEF_TBL_USERS;
    protected array $fillable = ['email', 'full_name', 'is_active'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }
}
