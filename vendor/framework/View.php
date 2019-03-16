<?php
namespace app\framework;

use app\framework\template\TemplateRenderer;

class View {
    
    public function __construct() {
        
    }
    
    public function render($path, $params = []) {
        $__path_str = addslashes($path);
        extract($params);
        unset($params);
        unset($path);
        ob_start();
        eval("?> " . (new TemplateRenderer("@include('{$__path_str}')"))->render());
        $rendered = ob_get_contents();
        ob_end_clean();
        return $rendered;
    }
}