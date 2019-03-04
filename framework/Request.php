<?php
namespace app\framework;

class Request {
    public function __construct() {
        
    }
    
    public function get($name, $default = "") {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        } else {
            return $default;
        }
    }
}

?>