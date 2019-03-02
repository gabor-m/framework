<?php
namespace app;

class View {
    private $globals = [];
    public $layout = null;
    
    public function __construct() {
        
    }
    
    public function share($name, $value) {
        $this->globals[$name] = $value;
    }
    
    private function extends($path) {
        $this->layout = $path;
    }
    
    private function escape($str) {
        return htmlspecialchars($str);
    }
    
    public function include($path, $params = []) {
        if (file_exists($path)) {
            foreach ($this->globals as $name => $value) {
                $$name = $value;
            }
            foreach ($params as $name => $value) {
                $$name = $value;
            }
            unset($params);
            ob_start();
            require_once($path);
            $rendered = ob_get_contents();
            ob_end_clean();
            return $rendered;
        }
        throw Exception("Template not found");
    }
    
    public function render($path, $params = []) {
        $rendered = $this->include($path, $params);
        if ($this->layout) {
            $rendered = $this->include($this->layout, [ "content" => $rendered ]);
        }
        return $rendered;
    }
}