<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class Invoice extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['amount', 'user_id', 'status'];

    protected static function booted()
    {
        static::creating(function(Invoice $invoice) {
            $invoice->id =  Uuid::uuid4()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
