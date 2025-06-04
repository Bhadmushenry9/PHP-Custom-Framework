<?php

namespace App\Models;

use App\Relations\BelongsTo;

class Invoice extends Model
{
    protected $table = DEF_TBL_USERS;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
