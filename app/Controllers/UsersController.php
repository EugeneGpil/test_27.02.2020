<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Response;

class UsersController
{
    public function showAddUserForm()
    {
        $rolesObj = new UserRole();
        $roles = $rolesObj->getAll();

        return Response::response("AddUserForm", ["roles" => $roles]);
    }

    public function addUser($incomingData)
    {
        $rolesObj = new UserRole();
        $roles = $rolesObj->getAll();
        $defaultResp = ["roles" => $roles];

        if (!isset($incomingData["username"]) || !$incomingData["username"]) {
            $defaultResp["role_id"] = isset($incomingData["role_id"]) ? $incomingData["role_id"] : null;
            $defaultResp["error"] = "Некорректное имя пользователя";
            return Response::response("AddUserForm", $defaultResp);
        }

        $defaultResp["username"] = $incomingData["username"];

        $isRoleExist = false;
        if (isset($incomingData["role_id"])) {
            foreach ($roles as $role) {
                if ($role["id"] == $incomingData["role_id"]) {
                    $isRoleExist = true;
                    break;
                }
            }
        }

        if (!isset($incomingData["role_id"]) || !$incomingData["role_id"] || !$isRoleExist) {
            $defaultResp["error"] = "Некорректная роль";
            return Response::response("AddUserForm", $defaultResp);
        }

        $defaultResp["role_id"] = $incomingData["role_id"];

        $usersObj = new User();
        $existingUser = $usersObj->getByColumn("username", $incomingData["username"]);
        
        if ($existingUser) {
            $defaultResp["error"] = "Пользователь уже существует";
            return Response::response("AddUserForm", $defaultResp);
        }

        $usersObj->add($incomingData);

        $defaultResp["message"] = "Пользователь успешно добавлен";
        return Response::response("AddUserForm", $defaultResp);
    }

    public function showAllUsers($incomingData)
    {
        $page = isset($incomingData[0]) ? $this->getValidPageNumber($incomingData[0]) : 0;

        if ($page <= 0) {
            $page = 1;
        }
        
        $usersObj = new User();
        $users = $usersObj->getUsersWithRolesByPage($page);

        // $rolesObj = new UserRole();
        // $roles = $rolesObj->getAll();

        // $resultData = [];
        // foreach ($users as $user) {
        //     $resultData["users"][] = [
        //         "username" => $user["username"],
        //         "role" => $this->getById($roles, $user["role_id"])["role"] ?? ""
        //     ];
        // }

        $resultData["users"] = $users;
        
        $resultData["currentPage"] = $page;

        if (count($users) >= $usersObj->getRowsPerPage()) {
            $resultData["nextPage"] = $page + 1;
        }

        if ($page >= 1) {
            $resultData["previousPage"] = $page - 1;
        }

        return Response::response("UsersList", $resultData);
    }

    private function getById($array, $id) {
        foreach ($array as $element) {
            if ($element["id"] == $id) {
                return $element;
            }
        }
        return null;
    }

    private function getValidPageNumber($val)
    {
        if (is_numeric($val) && (int) $val > 0) {
            return (int) round($val + 0);
        }
        return null;
    }
}