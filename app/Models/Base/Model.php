<?php

namespace App\Models\Base;

use PDO;
use \App\Config;

abstract class Model
{
    protected $tabelName;
    protected $columnNames;
    protected $rowsPerPage;
    protected static $DB = null;

    private const TABLE_NAMES = [
        "user",
        "user_role"
    ];

    private const CONNECTION_PARAMS = [
        "default",
        "no_database"
    ];

    public static function getDB($param = "default")
    {
        if (!in_array($param, self::CONNECTION_PARAMS)) {
            return null;
        }

        if (self::$DB[$param]) {
            return self::$DB[$param];
        }

        $config = Config::getConfig();
        $hostDatabaseString = "mysql:host=" . $config["database_server"];
        if ($param != "no_database") {
            $hostDatabaseString = $hostDatabaseString . ";dbname=" . $config["database_name"];
        }
        $hostDatabaseString = $hostDatabaseString . ";charset=utf8";

        self::$DB[$param] = new PDO(
            $hostDatabaseString,
            $config["database_user"],
            $config["database_password"],
            [
                PDO::ATTR_TIMEOUT => $config["database_connection_timeout"],
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return self::$DB[$param];
    }

    public function getRowsPerPage() {
        if (isset($this->rowsPerPage)) {
            return $this->rowsPerPage;
        }
        return null;
    }

    public function getAll(): array
    {
        if (!in_array($this->tableName, self::TABLE_NAMES)) {
            return [];
        }
        $all = $this->getDB()->query("SELECT * FROM `" . $this->tableName . "` LIMIT 1000");
        return $all->fetchAll();
    }

    public function getById($id): array
    {
        return $this->getByColumn("id", $id);
    }

    public function getByColumn($columnName, $value)
    {
        if (
            !in_array($this->tableName, self::TABLE_NAMES)
            || !in_array($columnName, $this->columnNames)
        ) {
            return [];
        }
        $needed = $this->getDB()->prepare("
            SELECT * FROM `" . $this->tableName . "` WHERE `" . $columnName . "` = ? LIMIT 1
        ");
        $needed->execute([$value]);
        $needed = $needed->fetchAll();
        if (empty($needed)) {
            return [];
        }
        return $needed;
    }

    public function add($incomingData)
    {
        if (!in_array($this->tableName, self::TABLE_NAMES)) {
            return false;
        }

        if (isset($incomingData["id"])) {
            unset($incomingData["id"]);
        }

        $columnsStr = "(";
        $valuesStr = "(";

        $firstIteration = true;
        foreach ($incomingData as $key => $value) {

            if (!in_array($key, $this->columnNames)) {
                return false;
            }

            if ($firstIteration) {
                $firstIteration = false;
            } else {
                $columnsStr = $columnsStr . ", ";
                $valuesStr = $valuesStr . ", ";
            }

            $columnsStr = $columnsStr . "`" . $key . "`";
            $valuesStr = $valuesStr . "?";
        }

        $columnsStr = $columnsStr . ")";
        $valuesStr = $valuesStr . ")";

        $requestStr = "INSERT INTO `" . $this->tableName . "` " . $columnsStr . " VALUES " . $valuesStr;
        $request = $this->getDB()->prepare($requestStr);
        $request->execute(array_values($incomingData));
        return true;
    }

    public function getPage($columns, int $pageNumber) {
        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }

        $columnsStr = "";

        $firstIteration = true;
        foreach ($columns as $column) {
            if (!in_array($column, $this->columnNames)) {
                return [];
            }

            if ($firstIteration) {
                $firstIteration = false;
            } else {
                $columnsStr = $columnsStr . ", ";
            }

            $columnsStr = $columnsStr . "`" . $column . "`";
        }

        $request = $this->getDB()->query("
            SELECT " . $columnsStr . " FROM `" . $this->tableName . "` LIMIT "
                . (int) $this->rowsPerPage . " OFFSET " . (int) (($pageNumber - 1) * $this->rowsPerPage)
        );
        return $request->fetchAll();
    }
}
