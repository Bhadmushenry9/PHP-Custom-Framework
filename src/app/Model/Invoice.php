<?php

namespace App\Model;

use App\Core\Model;
use App\Relations\BelongsTo;

class Invoice extends Model
{
    protected array $fillable = ['amount', 'user_id', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
