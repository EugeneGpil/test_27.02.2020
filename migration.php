<?php

require_once __DIR__ . "/app/Models/Base/Model.php";
require_once __DIR__ . "/app/Config.php";

use \App\Models\Base\Model;
use \App\Config;

$config = Config::getConfig();

$migration = Model::getDB("no_database")->prepare('
    CREATE DATABASE IF NOT EXISTS `' . $config["database_name"] . '`;
    USE `' . $config["database_name"] . '`;
    CREATE TABLE IF NOT EXISTS `user` ( 
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(255) NOT NULL UNIQUE,
        `role_id` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

    CREATE TABLE IF NOT EXISTS `user_role` (
        `id` int(11) NOT NULL AUTO_INCREMENT ,
        `role` varchar(255) NOT NULL UNIQUE,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
');
$migration->execute();
echo "DONE\n";
