<?php

namespace App\Models;

use App\Config;
use App\Models\Base\Model;

class User extends Model
{
    public function __construct()
    {
        $this->tableName = "user";
        $this->rowsPerPage = Config::getConfig()["rows_per_page"];

        $this->columnNames = [
            "id",
            "username",
            "role_id"
        ];
    }

    public function getUsersWithRolesByPage($page)
    {
        return $this->getPageWithRelations(
            ["user.username", "user_role.role"],
            "UserRole",
            ["user.role_id", "user_role.id"],
            $page
        );
    }
}
