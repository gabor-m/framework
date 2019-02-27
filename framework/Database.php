<?php
namespace app\database;

use app\models\Model;

class Database {
    private static $pdo;
    private static $config;
    public static $dbname;
    
    public static function init() {
        $db_config = json_decode(file_get_contents("config/db.json"), true);
        $dsn = 'mysql:dbname=' . $db_config["database"] . ';host=' . $db_config["host"]
            . ";charset=" . $db_config["charset"];
        self::$pdo = new \PDO($dsn, $db_config["username"], $db_config["password"]);
        self::$config = $db_config;
        self::$dbname = $db_config["database"];
        self::syncSchema();
    }
    
    public static function tables() {
        $tables = self::$pdo->query(
            "SELECT table_name FROM information_schema.tables "
            . "WHERE table_schema = '". self::$dbname . "'"
        );
        $return_array = [];
        foreach ($tables as $row) {
            $return_array[] = $row["table_name"];
        }
        return $return_array;
    }
    
    public static function columns($table) {
        $columns = self::$pdo->query(
            "SELECT column_name, data_type, column_default, column_type, is_nullable FROM information_schema.columns "
            . "WHERE table_schema = '" . self::$dbname . "' AND "
            . "table_name = '" . $table . "'"
        );
        $return_array = [];
        foreach ($columns as $column) {
            $return_array[$column["column_name"]] = [
                "type" => $column["column_type"],
                "default" => $column["column_default"],
                "nullable" => strtolower($column["is_nullable"]) === "yes"
            ];
        }
        return $return_array;
    }
    
    private static function syncSchema() {
        $models = Model::allModels();
        foreach ($models as $model) {
            $columns = $model::columns();
            $table_name = $model::tableName();
            self::syncTable($table_name);
            self::syncId($table_name);
            foreach ($columns as $column_name => $column_data) {
                if ($column_name !== "id") {
                    self::syncColumn($table_name, $column_name, $column_data);
                }
            }
        }
    }
    
    private static function syncTable($name) {
        self::$pdo->query(
            'CREATE TABLE IF NOT EXISTS `' . $name . '` '
            . '(`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) '
            . 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );
    }
    
    private static function syncId($table_name) {
        $results = self::$pdo->query(
            "SELECT column_name, data_type, column_default, column_type, is_nullable FROM information_schema.columns "
            . "WHERE table_schema = '" . self::$dbname . "' AND "
            . "table_name = '" . $table_name . "' AND column_name = 'id'"
        );
        $count = $results->rowCount();
        // TODO
    }
    
    private static function syncColumn($table_name, $column_name, $column_data) {
        $results = self::$pdo->query(
            "SELECT column_name, data_type, column_default, column_type, is_nullable FROM information_schema.columns "
            . "WHERE table_schema = '" . self::$dbname . "' AND "
            . "table_name = '" . $table_name . "' AND column_name = '" . $column_name . "'"
        );
        $count = $results->rowCount();
        if ($count === 0) {
            self::addOrModifyColumn("ADD", $column_data);
        } else {
            $result = $results->fetch(\PDO::FETCH_ASSOC);
            if (self::columnTypeEquals($result["column_type"], $column_data["sql_type"])
                    || $result["column_default"] !== $column_data["sql_default"]
                    || strtolower($result["is_nullable"] === "no")) {
                self::addOrModifyColumn("MODIFY COLUMN", $column_data);
            }
        }
        // Foreign key
        if ($column_data["referenced_table"]) {
            $foreign_key = self::getForeignKey($table_name, $column_name);
            if ($foreign_key) {
                if ($foreign_key["referenced_table_name"] !== $column_data["referenced_table"]
                        || $foreign_key["referenced_column_name"] !== "id") {
                    self::$pdo->query("ALTER TABLE `" . $table_name . "` DROP FOREIGN KEY " . $foreign_key["constraint_name"]);
                    self::addForeignKey($table_name, $column_name, $column_data["referenced_table"]);
                }
            } else {
                self::addForeignKey($table_name, $column_name, $column_data["referenced_table"]);
            }
        }
    }
    
    private static function addForeignKey($table_name, $column_name, $referenced_table) {
        self::$pdo->query(
            "ALTER TABLE " . $table_name . " ADD CONSTRAINT " . $table_name . "_" . $column_name . "_" . $referenced_table . "_fk "
            . "FOREIGN KEY (" . $column_name . ") "
            . "REFERENCES " . $referenced_table . "(id)"
        );
    }
    
    private static function columnTypeEquals($type1, $type2) {
        // TODO: pl. enum esetén törölni kell a szóközöket mindkettõbõl és úgy összehasonlítani
        return strtolower($type1) !== strtolower($type2);
    }
    
    private static function addOrModifyColumn($type, $column_data) {
        self::$pdo->query(
            "ALTER TABLE " . $column_data["table_name"] . " " . $type . " `"
            . $column_data["column_name"] . "` " . $column_data["sql_type"]
            . " NULL DEFAULT " . ($column_data["sql_default"] === NULL ? "null" : self::$pdo->quote($column_data["sql_default"]))
        );
    }
    
    public static function findRecordById($table, $id) {
        $results = self::$pdo->query(
            'SELECT * FROM `' . $table . '` WHERE id = ' . $id
        );
        return $results->fetch(\PDO::FETCH_ASSOC);
    }
    
    public static function updateRecordById($table, $id, $data = []) {
        $assignments = [];
        foreach ($data as $key => $value) {
            $assignments[] = "`" . $key . "` = :" . $key;
        }
        $assignments = implode(", ", $assignments);
        $results = self::$pdo->prepare(
            "UPDATE " . $table
            . " SET " . $assignments
            . " WHERE id = " . $id);
        $results->execute($data);
    }
    
    public static function getForeignKey($table, $column) {
        $results = self::$pdo->query(
            "SELECT table_name,column_name,constraint_name,referenced_table_name,referenced_column_name "
            . "FROM information_schema.key_column_usage "
            . "WHERE table_schema = '" . self::$dbname . "' AND "
            . "table_name = '" . $table . "' AND "
            . "column_name = '" . $column . "'"
        );
        // TODO: on delete, on update (in referential_constraints table)
        return $results->fetch(\PDO::FETCH_ASSOC);
    }
}
