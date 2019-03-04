<?php
namespace app\framework;

class Response {
    protected $headers = [];
    protected $body = "";
    
    public function __construct() {
        
    }
    
    public static function html($str) {
        $req = new Response;
        $req->body = $str;
        $req->headers["Content-Type"] = "text/html; charset=UTF-8";
        return $req;
    }
    
    public static function json($val) {
        $req = new Response;
        $req->body = json_encode($val, JSON_PRETTY_PRINT);
        $req->headers["Content-Type"] = "application/json; charset=UTF-8";
        return $req;
    }
    
    public function header($name, $value) {
        $this->headers[$name] = $value;
    }
    
    public function write() {
        foreach ($this->headers as $key => $value) {
            header($key . ": " . $value);
        }
        if ($this->body) {
            echo $this->body;
        }
    }
}

?>