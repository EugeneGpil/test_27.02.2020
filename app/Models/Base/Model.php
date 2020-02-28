<?php

namespace App\Models\Base;

use PDO;
use \App\Config;

abstract class Model
{
    protected $tableName;
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

    public function getRowsPerPage()
    {
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

    public function getPageWithRelations($columns, $relationClass, $relationColumns, int $pageNumber)
    {
        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }

        $className = "\\App\\Models\\" . $relationClass;
        $relationTableObj = new $className();

        if (
            !in_array($relationTableObj->tableName, self::TABLE_NAMES)
            || !in_array($this->tableName, self::TABLE_NAMES)
        ) {
            return [];
        }

        $columnsStr = "";

        $firstIteration = true;
        foreach ($columns as $tableColumnWithDot) {

            if (!$this->isTableAndColumnWithDotAllowed($tableColumnWithDot, $relationTableObj)) {
                return [];
            }

            if ($firstIteration) {
                $firstIteration = false;
            } else {
                $columnsStr = $columnsStr . ", ";
            }

            $columnsStr = $columnsStr . $this->getBacktickedTableColumn($tableColumnWithDot);
        }

        foreach ($relationColumns as $tableAndColumnWithDot) {
            if (!$this->isTableAndColumnWithDotAllowed($tableAndColumnWithDot, $relationTableObj)) {
                return [];
            }
        }

        $request = $this->getDB()->query("
            SELECT " . $columnsStr . "
            FROM `" . $this->tableName . "`
            LEFT JOIN `" . $relationTableObj->tableName . "`
            ON " . $this->getBacktickedTableColumn($relationColumns[0]) . "
             = " . $this->getBacktickedTableColumn($relationColumns[1]) . "
            LIMIT " . (int) $this->rowsPerPage . "
            OFFSET " . (int) (($pageNumber - 1) * $this->rowsPerPage)
        );
        return $request->fetchAll();
    }

    private function getBacktickedTableColumn($tableAndColumnWithDot) {
        [$tableName, $columnName] = $this->getTableAndColumn($tableAndColumnWithDot);
        return "`" . $tableName . "`.`" . $columnName . "`";
    }

    private function isTableAndColumnWithDotAllowed($tableAndColumnWithDot, $relationObj)
    {
        [$tableName, $columnName] = $this->getTableAndColumn($tableAndColumnWithDot);
        if (
            !(in_array($columnName, $this->columnNames)
            || in_array($columnName, $relationObj->columnNames))
            || !in_array($tableName, self::TABLE_NAMES)
        ) {
            return false;
        }
        return true;
    }

    private function getTableAndColumn($tableColumnStr) {
        $dotPos = strpos($tableColumnStr, ".");
        $tableName = substr($tableColumnStr, 0, $dotPos);
        $columnName = substr($tableColumnStr, $dotPos + 1);
        return [
            $tableName,
            $columnName
        ];
    }
}
