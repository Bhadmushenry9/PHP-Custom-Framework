<?php
declare(strict_types=1);

namespace App\Model;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['email', 'full_name', 'is_active'];

    protected static function booted()
    {
        static::creating(function(User $user) {
            $user->id =  Str::uuid();
        });
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
