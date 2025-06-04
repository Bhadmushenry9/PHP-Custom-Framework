<?php
declare(strict_types=1);
namespace App\Models;

use App\App;
use App\DB;

abstract class Model
{
    protected DB $db;
    public function __construct()
    {
        $this->db = App::db();
    }

    public function create(array $data): void
    {
        $this->db->insertBuilder($data);
    }

    public function lastInsertId(array $data): void
    {
        $this->db->lastInsertId();
    }

    public function find():array
    {
        return [];
    }
}
