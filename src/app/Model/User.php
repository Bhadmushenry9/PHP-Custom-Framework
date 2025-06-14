<?php

namespace App\Model;

use App\Relations\HasMany;
use App\Core\Model;

class User extends Model
{
    protected array $fillable = ['email', 'full_name', 'is_active'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }
}
