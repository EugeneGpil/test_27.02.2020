<?php

namespace App\Controllers;

use App\Response;
use App\Models\UserRole;

class UserRolesController
{
    public function showAddUserRoleForm()
    {
        return Response::response("AddUserRoleForm");
    }

    public function addRole($request)
    {
        if (!isset($request["role"]) || !$request["role"]) {
            return Response::response("AddUserRoleForm", [
                "error" => "Некорректная роль"
            ]);
        }

        $userRole = new UserRole();
        $existingRole = $userRole->getByColumn("role", $request["role"]);
        if ($existingRole) {
            return Response::response("AddUserRoleForm", [
                "role" => $request["role"],
                "error" => "Эта роль уже сущеструет"
            ]);
        }

        $res = $userRole->add($request);

        if ($res) {
            return Response::response("AddUserRoleForm", [
                "role" => $request["role"],
                "message" => "Роль успешно добавлена"
            ]);
        }
    }
}