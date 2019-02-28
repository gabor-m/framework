<?php
namespace app\database;

class SelectQuery {
    private $model;
    private $condition;
    private $order;
    private $limit;
    private $offset;
    
    public function __construct($model) {
        $this->model = $model;
    }
    
    public function where($condition) {
        $this->condition = $condition;
        
        return $this;
    }
    
    public function generateSqlSelect($count = false) {
        $table = $this->model::tableName();
        $sql = "SELECT " . ($count ? "COUNT(*)" : "*") . " FROM " . $table . " ";
        if ($this->condition) {
            $sql .= $this->generateSqlCondition();
        }
        if ($this->order) {
            $sql .= $this->generateSqlOrder();
        }
        if ($this->limit) {
            $sql .= " LIMIT " . strval($this->limit);
        }
        if ($this->offset) {
            if (!$this->limit) {
                $sql .= " LIMIT 18446744073709551615"; // large number
            }
            $sql .= " OFFSET " . strval($this->offset);
        }
        var_dump($sql);
        return $sql;
    }
    
    private static function valueToSql($val) {
        if (is_string($val)) {
            return Database::$pdo->quote($val);
        }
        if (is_array($val)) {
            return json_encode($val);
        }
        return strval($val);
    }
    
    private static function conditionToSql($cond) {
        if (!is_array($cond)) {
            return $cond;
        }
        $op = $cond[0];
        switch ($op) {
        case "like":
            return "(`" . $cond[1] . "` LIKE " . self::valueToSql($cond[2]) . ")";
        case "in":
            return "(`" . $cond[1] . "` IN (" . implode(", ", array_map("self::valueToSql", $cond[2])) . "))";
        case "and":
        case "or":
            return "(" . implode(" " . strtoupper($op) . " ", array_map("self::conditionToSql", array_slice($cond, 1))) . ")";
        case "=":
        case "<>":
        case "<":
        case ">":
        case "<=":
        case ">=":
            return "(`" . $cond[1] . "`" . $op . self::valueToSql($cond[2]) . ")";
        }
    }
    
    private function generateSqlCondition() {
        if (!$this->condition) {
            return "";
        }
        return "WHERE " . self::conditionToSql($this->condition);
    }
    
    public function orderBy($columns) {
        $this->order = $column;
        
        return $this;
    }
    
    public function limit($n) {
        $this->limit = $n;
        return $this;
    }
    
    public function offset($n) {
        $this->offset = $n;
        return $this;
    }
    
    public function all() {
        $sql = $this->generateSqlSelect();
        $results = Database::$pdo->query($sql);
        if (!$results) {
            return [];
        }
        $models = [];
        foreach ($results as $result) {
            $models[] = new $this->model($result);
        }
        return $models;
    }
    
    public function count() {
        $sql = $this->generateSqlSelect(true);
        $results = Database::$pdo->query($sql);
        if (!$results) {
            return 0;
        }
        $results = $results->fetch(\PDO::FETCH_ASSOC);
        foreach ($results as $result) {
            return intval($result);
        }
    }
    
    public function paginate($page_size = 20) {
        $query = clone $this;
        $total = $this->count();
        return new Pagination($query, $total, $page_size);
    }
}