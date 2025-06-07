<?php

namespace App\Models;

use App\Relations\HasMany;
use App\Core\Model;

/**
 * @method static array all()
 * @method static array|null find($id)
 * @method static int create(array $data)
 * @method static int update($id, array $data)
 * @method static bool delete($id)
 */
class User extends Model
{
    protected string $table = DEF_TBL_USERS;
    protected array $fillable = ['email', 'full_name', 'is_active'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }
}
