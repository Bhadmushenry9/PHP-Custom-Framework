<?php

namespace App\Models;

use App\Relations\HasMany;

class User extends Model
{
    protected $table = DEF_TBL_USERS;

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }
}
