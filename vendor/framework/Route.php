<?php
namespace app\framework;

class Route {
    private static $url_rules = [];
    private static $controllers = [];
     
    public static function get($url, ...$actions) {
        foreach ($actions as $action) {
            self::addRule("get", $url, $action);
        }
    }

    public static function post($url, ...$actions) {
        foreach ($actions as $action) {
            self::addRule("post", $url, $action);
        }
    }
    
    private static function addRule($method, $url, $action) {
        $action_parts = explode("@", $action);
        if (substr($action_parts[0], 0, 4) === "app/") {
            $controller = str_replace("/", "\\", $action_parts[0]);
        } else {
            $controller = "app\\controllers\\" . str_replace("/", "\\", $action_parts[0]);
        }
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
    
    private static function match_path($pattern, $path) {
        $names = [];
        preg_match_all("/<([a-zA-Z][a-zA-Z0-9_]+)/", $pattern, $name_matches);
        $name_matches = array_slice($name_matches, 1); // az els� match a teljes, ez�rt azt kihagyom
        foreach ($name_matches[0] as $name_match) {
            $names[] = $name_match;
        }
        // var_dump($names);
        $pattern = str_replace("(", "(?:", $pattern);
        $pattern = preg_replace("/<[a-zA-Z][a-zA-Z0-9_]+:/", "(", $pattern);
        $pattern = str_replace(">", ")", $pattern);
        $regex = ">^" . $pattern . "$>"; // `>` egy olyan karakter, ami nem lehet az URL-ben
        $has_match = preg_match($regex, $path, $matches) === 1;
        $matches = array_slice($matches, 1); // az els� match a teljes, ez�rt azt kihagyom
        $params = [];
        foreach ($matches as $index => $str) {
            $params[$names[$index]] = $str;
        }
        return count($params) > 0 ? $params : $has_match;
    }
    
    public static function performAction() {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsed_url["path"];
        $query = $_GET;
        foreach (self::$url_rules as $rule) {
            $params = self::match_path($rule["url"], $path);
            if (!!$params) {
                $action = $rule["action"];
                $request = new Request($params);
                $response = self::$controllers[$rule["controller"]]->$action($request);
                if ($response === null) {
                    // pass. go to next rule
                } else if (is_string($response)) {
                    Response::html($response)->write();
                    return;
                } else if (is_array($response)) {
                    Response::json($response)->write();
                    return;
                } else if (is_a($response, "app\\framework\\Response")) {
                    $response->write();
                    return;
                } else {
                    return;
                }
            }
        }
    }
}

?>