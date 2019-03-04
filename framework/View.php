<?php
namespace app\framework;

class View {
    private $extension = ".view";
    private $rootDir = "views/";
    private $globals = [];
    private $layoutParams = [];
    public $layout = null;
    
    public function __construct() {
        
    }
    
    public function setRoot($path) {
        $this->rootDir = $path . "/";
    }
    
    public function share($name, $value) {
        $this->globals[$name] = $value;
    }
    
    private function extends($path, $layoutParams = []) {
        $this->layout = $path;
        $this->layoutParams = $layoutParams;
    }
    
    private function escape($str) {
        return htmlspecialchars($str);
    }
    
    public function include($path, $params = []) {
        $path = $this->rootDir . $path . $this->extension;
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
            $rendered = $this->include($this->layout, array_merge([ "content" => $rendered ], $this->layoutParams));
        }
        return $rendered;
    }
}