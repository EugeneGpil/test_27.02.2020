<?php

namespace App;

class Config
{
    public static function getConfig(): array
    {
        return include(__DIR__ . "/../config.php");
    }
}
