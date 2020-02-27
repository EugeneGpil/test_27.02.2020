<?php

namespace App\Models;

use App\Models\Base\Model;

class UserRole extends Model
{
    public function __construct()
    {
        $this->tableName = "user_role";

        $this->columnNames = [
            "id",
            "role"
        ];
    }
}
