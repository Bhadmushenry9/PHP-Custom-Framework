<?php

namespace App\Models;

class User extends Model
{
    public function __construct()
    {
        parent::__construct();

        $this->db->table(DEF_TBL_USERS);
    }
}
