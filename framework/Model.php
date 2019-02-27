<?php
namespace app\models;

class Model {
    public $id;
    
    public static function tableName() {
        $a = explode("\\", get_called_class());
        $camelcase = str_split(end($a));
        $underscore_case = "";
        foreach ($camelcase as $i => $c) {
            if ($i !== 0 && ctype_upper($c)) {
                $underscore_case .= "_";
            }
            $underscore_case .= strtolower($c);
        }
        return $underscore_case;
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
            if ($property->name !== "id") {
                $doc_comment = trim(substr(substr($property->getDocComment(), 3), 0, -2));
                $return_array[$property->name] = [
                    "type" => $doc_comment,
                    "sql_type" => self::toSqlType($doc_comment),
                    "default" => $defaults[$property->name],
                    "sql_default" => self::toSqlDefault($defaults[$property->name]),
                ];
            }
        }
        return $return_array;
    }
    
    private static function toSqlType($type) {
        switch ($type) {
        case "int":
            return "int(11)";
        case "tinyint":
            return "tinyint(1)";
        case "bool":
            return "tinyint(1)";
        }
        return strtolower($type);
    }
    
    private static function toSqlDefault($default) {
        if ($default === null) {
            return "NULL";
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
}