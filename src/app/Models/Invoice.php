<?php

namespace App\Models;

class Invoice extends Model
{
    public function __construct()
    {
        parent::__construct();

        $this->db->table(DEF_TBL_USERS);
    }
}
