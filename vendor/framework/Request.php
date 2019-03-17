<?php
namespace app\framework;

class Request {
    private $params = [];
    public static $currentRequest;
        
    public function __construct($params) {
        $this->params = $params;
    }
    
    public static function current() {
        return self::$currentRequest;
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
    
    public function file($name) {
        $files = self::files($name);
        if (count($files) > 0) {
            return $files[0];
        }
        return null;
    }
    
    public function files($name) {
        if (isset($_FILES[$name])) {
            if (
                !isset($_FILES[$name]["tmp_name"])
                || !isset($_FILES[$name]["name"])
                || !isset($_FILES[$name]["error"])
                || !isset($_FILES[$name]["size"])
            ) {
                return []; // something went wrong
            }
            $files = [];
            if (is_array($_FILES[$name]["error"])) {
                for ($i = 0; $i < count($_FILES[$name]["error"]); $i += 1) {
                    $files[] = [
                        "name" => $_FILES[$name]["name"][$i],
                        "tmp_name" => $_FILES[$name]["tmp_name"][$i],
                        "size" => $_FILES[$name]["size"][$i],
                        "error" => $_FILES[$name]["error"][$i],                        
                    ];
                }
            } else {
                $files[] = [
                    "name" => $_FILES[$name]["name"],
                    "tmp_name" => $_FILES[$name]["tmp_name"],
                    "size" => $_FILES[$name]["size"],
                    "error" => $_FILES[$name]["error"],
                ];
            }
            $file_objects = [];
            foreach ($files as $file) {
                if ($file["error"] === UPLOAD_ERR_OK && is_uploaded_file($file["tmp_name"])) {
                    $file_objects[] = new UploadedFile($file["name"], $file["tmp_name"]);
                }
            }
            return $file_objects;
        }
        return [];
    }
}

?>