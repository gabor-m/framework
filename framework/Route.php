<?php
namespace app\framework;

class Route {
    private static $url_rules = [];
    private static $controllers = [];
     
    public static function get($url, $action) {
        self::addRule("get", $url, $action);
    }

    public static function post($url, $action) {
        self::addRule("post", $url, $action);
    }
    
    private static function addRule($method, $url, $action) {
        $action_parts = explode("@", $action);
        $controller = "app\\controllers\\" . str_replace("/", "\\", $action_parts[0]);
        $action = $action_parts[1];
        self::$url_rules[] = [
            "method" => $method,
            "url" => $url,
            "controller" => $controller,
            "action" => $action,
        ];
        if (!isset(self::$controllers[$controller])) {
            self::$controllers[$controller] = new $controller;
        }
    }
    
    private static function test_url() {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsed_url["path"];
    }
    
    public static function performAction() {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsed_url["path"];
        $query = $_GET;
        foreach (self::$url_rules as $rule) {
            if ($rule["url"] === $path) {
                $action = $rule["action"];
                $response = self::$controllers[$rule["controller"]]->$action(new Request());
                if (is_string($response)) {
                    Response::html($response)->write();
                } else if (is_array($response)) {
                    Response::json($response)->write();
                } else if (is_a($response, "app\\framework\\Response")) {
                    $response->write();
                }
                break;
            }
        }
    }
}

?>