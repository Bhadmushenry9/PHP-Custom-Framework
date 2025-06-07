<?php

namespace App\Models;

use App\Core\Model;
use App\Relations\BelongsTo;

class Invoice extends Model
{
    protected string $table = DEF_TBL_INVOICES;
    protected array $fillable = ['amount', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
