<?php
namespace app\framework\template;

class ParseTree {
    public $type;
    public $param;
    public $children;
    
    public function __construct($type, $param = "", $children = []) {
        $this->type = $type;
        $this->param = $param;
        $this->children = $children;
    }
}

?>