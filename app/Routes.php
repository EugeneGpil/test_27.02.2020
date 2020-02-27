<?php

namespace App;

use App\Response;

class Routes
{
    private const ROUTES = [
        [
            "url" => "/user_role_form",
            "method" => "GET",
            "class" => "\\App\\Controllers\\UserRolesController",
            "function" => "showAddUserRoleForm"
        ],
        [
            "url" => "/user_role_form",
            "method" => "POST",
            "class" => "\\App\\Controllers\\UserRolesController",
            "function" => "addRole"
        ],
        [
            "url" => "/add_user",
            "method" => "GET",
            "class" => "\\App\\Controllers\\UsersController",
            "function" => "showAddUserForm"
        ],
        [
            "url" => "/add_user",
            "method" => "POST",
            "class" => "\\App\\Controllers\\UsersController",
            "function" => "addUser"
        ],
        [
            "url" => "/users_list",
            "method" => "GET",
            "class" => "\\App\\Controllers\\UsersController",
            "function" => "showAllUsers"
        ],
        [
            "url" => "/",
            "method" => "GET",
            "class" => "\\App\\Controllers\\MainPageController",
            "function" => "showMainPage"
        ]
    ];

    public static function route()
    {
        $url = self::getUrl();
        $method = $_SERVER["REQUEST_METHOD"];

        foreach (self::ROUTES as $route) {
            if (
                (strpos($url, $route["url"]) === 0  && $route["method"] == $method && $route["url"] != "/")
                || ($route["url"] == "/" && $url == "/" && $route["method"] == $method)
            ) {
                $requestData = self::getRequest($url, $route["url"], $method);
                $className = $route["class"];
                $needed = new $className();
                return $needed->{$route["function"]}($requestData);
            }
        }

        return Response::response("NotFound");
    }

    private static function getRequest($url, $routeUrl, $method)
    {
        if ($method != "GET") {
            return $_REQUEST;
        }

        $routeUrlLength = strlen($routeUrl);
        $urlParamsStr = substr($url, $routeUrlLength);

        return array_values(array_diff(explode('/', $urlParamsStr), ['']));
    }

    private static function getUrl()
    {
        $url = $_SERVER["REQUEST_URI"];
        $questionMarkPosition = strpos($url, '?');
        if ($questionMarkPosition === false) {
            return $url;
        }
        return substr($url, 0, $questionMarkPosition);
    }
}
