<?php

namespace App;

class Response
{
    private const DATA_KEYS = [
        "AddUserRoleForm" => [
            "role" => "",
            "message" => "",
            "error" => ""
        ],
        "AddUserForm" => [
            "username" => "",
            "role_id" => "",
            "roles" => [],
            "message" => "",
            "error" => ""
        ],
        "UsersList" => [
            "users" => [],
            "currentPage" => "",
            "nextPage" => "",
            "previousPage" => ""
        ]
    ];

    public static function response($page, $data = null)
    {
        $data = self::setKeys($page, $data);
        require_once $_SERVER["DOCUMENT_ROOT"] . "/app/Views/Base/Body.php";
    }

    private static function setKeys($page, $data)
    {
        if (isset(self::DATA_KEYS[$page])) {
            foreach (self::DATA_KEYS[$page] as $key => $value) {
                if (!isset($data[$key])) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }
}