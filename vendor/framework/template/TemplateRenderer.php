<?php
namespace app\framework\template;

class TemplateRenderer {
    private $source;
    private $root;
    private static $base_dir = "views/";
    private static $extension = ".php";
    
    function __construct($source) {
        $this->source = $source;
        $this->root = (new TemplateParser($source))->parse();
    }
    
    private static function lookup($path) {
        // $path = str_replace(".", "/", $path);
        $path .= self::$extension;
        return file_get_contents(self::$base_dir . $path);
    }
    
    public static function makeInclude($parent_vars, $path, $other_vars = []) {
        extract($parent_vars, EXTR_SKIP);
        extract($other_vars);
        eval('?>' . (new TemplateRenderer(TemplateRenderer::lookup($path)))->render());
    }
    
    public static function makeComponent($parent_vars, $slots, $path, $other_vars = []) {
        // extract($parent_vars, EXTR_SKIP);
        extract($other_vars);
        extract($slots);
        eval('?>' . (new TemplateRenderer(TemplateRenderer::lookup($path)))->render());
    }
    
    private function renderAll($parse_tree) {
        $rendered = "";
        foreach ($parse_tree as $tree) {
            $rendered .= $this->renderRule($tree);
        }
        return $rendered;
    }
    
    private function renderRule($tree) {
        switch ($tree->type) {
        case "(text)":
            return $this->renderText($tree);
        case "(default)":
        case "(braces)":
        case "echo":
            return $this->renderEcho($tree);
        case "raw":
            return $this->renderRaw($tree);
        case "html":
            return $this->renderHtml($tree);
        case "php":
            return $this->renderPhp($tree);
        case "at":
            return $this->renderAt($tree);
        case "json":
            return $this->renderJson($tree);
        case "attr":
            return $this->renderAttr($tree);
        case "if":
            return $this->renderIf($tree);
        case "elseif":
            return $this->renderElseif($tree);
        case "else":
            return $this->renderElse($tree);
        case "foreach":
            return $this->renderForeach($tree);
        case "break":
            return $this->renderBreak($tree);
        case "comment":
            return $this->renderComment($tree);
        case "include":
            return $this->renderInclude($tree);
        case "component":
            return $this->renderComponent($tree);
        default:
            throw new Exception("Unsupported type");
        }
    }
    
    private function renderText($tree) {
        return $tree->param;
    }
    
    private function renderEcho($tree) {
        return "<?php echo ({$tree->param}); ?>";
    }
    
    private function renderHtml($tree) {
        return "<?php echo htmlspecialchars({$tree->param}); ?>"; 
    }
    
    private function renderRaw($tree) {
        return "<?php echo ({$tree->param}); ?>";
    }
    
    private function renderPhp($tree) {
        return "<?php {$this->renderAll($tree->children)} ?>";
    }
    
    private function renderAt() {
        return "@";
    }
    
    private function renderJson($tree) {
        return "<?php echo json_encode({$tree->param}); ?>";
    }
    
    private function renderAttr($tree) {
        return "<?php echo htmlspecialchars({$tree->param}, ENT_QUOTES); ?>";
    }
    
    private function renderIf($tree) {
        return "<?php if ({$tree->param}) { ?> {$this->renderAll($tree->children)} <?php } ?>";
    }
    
    private function renderElseif($tree) {
        return "<?php } elseif ({$tree->param}) { ?>";
    }
    
    private function renderElse($tree) {
        return "<?php } else { ?>";
    }
    
    private function renderForeach($tree) {
        return "<?php foreach ({$tree->param}) { ?> {$this->renderAll($this->children)} <?php } ?>";
    }
    
    private function renderBreak($tree) {
        return "<?php break; ?>";
    }
    
    private function renderComment($tree) {
        return "";
    }
    
    private function renderInclude($tree) {
        return "<?php \\app\\framework\\template\\TemplateRenderer::makeInclude(get_defined_vars(), {$tree->param}); ?>";
    }
    
    private function renderSlot($tree) {
        return $this->renderAll($tree->children);
    }
    
    private function renderComponent($tree) {
        $slots = [];
        $slot = "";
        foreach ($tree->children as $child) {
            if ($child->type === "slot") {
                $slot_name = str_replace("'", "", $child->param);
                $slots[$slot_name] = $this->renderSlot($child);
            } else {
                $slot .= $this->renderRule($child);
            }
        }
        $slots["slot"] = $slot;
        $array_as_string = "[";
        foreach ($slots as $key => $val) {
            $array_as_string .= "'" . addslashes($key) . "'=>'" . addslashes($val) . "',";
        }
        $array_as_string .= "]";
        return "<?php \\app\\framework\\template\\TemplateRenderer::makeComponent(get_defined_vars(), {$array_as_string}, {$tree->param}); ?>";
    }
    
    public function render() {
        return $this->renderAll($this->root);
    }
}

?>