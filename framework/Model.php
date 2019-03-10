<?php
namespace app\framework;

use app\database\SelectQuery;

class Model {
    protected $id;
    protected $isNewRecord = true;
    
    public function __get($property) {
        if (property_exists($this, $property)) {
            // Idegen kulcs
            if ($this->isForeignKeyColumn($property)) {
                $column_data = $this->foreignKeyColumns()[$property];
                $class_name = "app\\models\\" . $column_data["referenced_model"];
                return $class_name::findOne($this->$property);
            }
            // JSON
            if ($this->isJsonColumn($property)) {
                return json_decode($this->$property, true);
            }
            return $this->$property;
        }
        return null;
    }
    
    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            // ID módosítása
            if ($property === "id") {
                $this->id = $value;
                $exists = $this->fillWithData($value);
                if (!$exists) {
                    throw new \Exception("Wrong ID");
                }
                return;
            }
            // Idegen kulcs módosítása
            if ($this->isForeignKeyColumn($property)) {
                $column_data = $this->foreignKeyColumns()[$property];
                $class_name = "app\\models\\" . $column_data["referenced_model"];
                if (is_object($value) && is_a($value, $class_name)) {
                    $this->$property = $value->id;
                } else {
                    $this->$property = intval($value);
                }
                return;
            }
            // JSON
            if ($this->isJsonColumn($property)) {
                $this->$property = json_encode($value);
                return;
            }
            if ($this->isFileColumn($property)) {
                if (!Storage::has($value)) {
                    throw new \Exception("File not found");
                }
            }
            $this->$property = $value;
        }
    }
    
    private static function camelCaseToUndersoreCase($camelcase) {
        $camelcase = str_split($camelcase);
        $underscore_case = "";
        foreach ($camelcase as $i => $c) {
            if ($i !== 0 && ctype_upper($c)) {
                $underscore_case .= "_";
            }
            $underscore_case .= strtolower($c);
        }
        return $underscore_case;
    }
    
    public static function tableName() {
        $a = explode("\\", get_called_class());
        $camelcase = end($a);
        return self::camelCaseToUndersoreCase($camelcase);
    }
    
    public static function columns() {
        $class_name = get_called_class(); // with namespace
        $class_reflection = new \ReflectionClass($class_name);
        $properties = $class_reflection->getProperties(
            \ReflectionProperty::IS_PUBLIC
            | \ReflectionProperty::IS_PROTECTED
            | \ReflectionProperty::IS_PRIVATE
        );
        $defaults = $class_reflection->getDefaultProperties();
        $return_array = [];
        foreach ($properties as $property) {
            $type = trim(substr(substr($property->getDocComment(), 3), 0, -2));
            if ($property->name !== "id" && $type) {
                $return_array[$property->name] = [
                    "table_name" => self::tableName(),
                    "column_name" => $property->name,
                    "type" => $type,
                    "sql_type" => self::toSqlType($type),
                    "default" => $defaults[$property->name],
                    "sql_default" => self::toSqlDefault($defaults[$property->name]),
                    "referenced_model" => ctype_upper($type[0]) ? $type : null,
                    "referenced_table" => ctype_upper($type[0]) ? self::camelCaseToUndersoreCase($type) : null,
                ];
            }
        }
        return $return_array;
    }
    
    private static function foreignKeyColumns() {
        $refs = [];
        $columns = self::columns();
        foreach ($columns as $column_name => $column_data) {
            if ($column_data["referenced_model"]) {
                $refs[$column_name] = $column_data;
            }
        }
        return $refs;
    }
    
    private static function isForeignKeyColumn($column) {
        $refs = self::foreignKeyColumns();
        return isset($refs[$column]);
    }
    
    private static function isJsonColumn($column) {
        $column = self::columns()[$column];
        return $column["type"] == "json";
    }
    
    private static function isFileColumn($column) {
        $column = self::columns()[$column];
        return $column["type"] == "file";
    }
    
    private static function toSqlType($type) {
        if (ctype_upper($type[0])) {
            // table reference
            return "int(11)"; // foreign key
        }
        switch ($type) {
        case "int":
            return "int(11)";
        case "tinyint":
            return "tinyint(1)";
        case "bool":
            return "tinyint(1)";
        case "file":
            return "char(40)";
        }
        if (strpos($type, "enum") === 0) {
            return str_replace(" ", "", $type);
        }
        return strtolower($type);
    }
    
    private static function toSqlDefault($default) {
        if ($default === null) {
            return null;
        }
        if (is_array($default)) {
            return json_encode($default);
        }
        return strval($default);
    }
    
    public static function allModels() {
        $all_classes = get_declared_classes();
        $models = [];
        foreach ($all_classes as $c) {
            if (strpos($c, "app\\models\\") === 0 && $c !== "app\\models\\Model") {
                $models[] = $c;
            }
        }
        return $models;
    }
    
    private static function syncSchema() {
        $class = get_called_class();
        return Database::syncSchema($class);
    }
    
    private function fillWithData() {
        $id = $this->id;
        $table = self::tableName();
        $columns = self::columns();
        $record = Database::findRecordById($table, $id);
        if (!$record) {
            return false;
        }
        foreach ($columns as $column_name => $column_data) {
            $this->$column_name = $record[$column_name];
        }
        $this->isNewRecord = false; // Éles adatokkal feltöltve, tehát már nem új rekord
        return true;
    }
    
    public static function findOne($id) {
        $this_class = get_called_class();
        $model = new $this_class;
        $model->id = $id;
        $exists = $model->fillWithData();
        if (!$exists) {
            return null;
        }
        return $model;
    }
    
    public function asArray($include_id = false) {
        $data = [];
        $columns = self::columns();
        foreach ($columns as $column_name => $column_data) {
            $data[$column_name] = $this->$column_name;
        }
        if ($include_id) {
            $data["id"] = $this->id;
        }
        return $data;
    }
    
    public function save() {
        $table = self::tableName();
        $this->beforeSave();
        if (!$this->id) {
            $this->id = Database::insertRecord($table, $this->asArray());
        } else {
            $id = $this->id;
            Database::updateRecordById($table, $id, $this->asArray());
            var_dump(Database::error());
        }
        $this->isNewRecord = false;
        $this->afterSave();
    }
    
    protected function beforeSave() {
        
    }
    
    protected function afterSave() {
        
    }
    
    public function fill($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    public static function find() {
        $class_name = get_called_class();
        return new SelectQuery($class_name);
    }
    
    public static function __load($class) {
        if ($class && $class !== "app\\framework\\Model") {
            $class::syncSchema();
        }
    }
}