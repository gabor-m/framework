<?php
namespace app\framework;

class Request {
    private $params = [];
    
    public function __construct($params) {
        $this->params = $params;
    }
    
    public function get($name, $default = "") {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        } else if (isset($this->params[$name])) {
            return $this->params[$name];
        } else {
            return $default;
        }
    }
    
    public function post($name, $default = "") {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        } else {
            return $default;
        }
    }
}

?>