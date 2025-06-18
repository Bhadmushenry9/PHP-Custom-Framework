<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class User extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['email', 'full_name', 'is_active'];

    protected static function booted()
    {
        static::creating(function(Invoice $invoice) {
            $invoice->id =  Uuid::uuid4()->toString();
        });
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
