<?php
namespace app\framework\template;

class ComponentMaker {
    protected $componentStack = [];

    protected $componentData = [];

    protected $slots = [];

    protected $slotStack = [];
    
    public function startComponent($name, array $data = []) {
        if (ob_start()) {
            $this->componentStack[] = $name;
            $this->componentData[$this->currentComponent()] = $data;
            $this->slots[$this->currentComponent()] = [];
        }
    }
    
    public function endComponent() {
        $__name = array_pop($this->componentStack);
        $__data = $this->componentData();
        extract($__data);
        eval('?>' . (new TemplateRenderer(TemplateRenderer::lookup($__name)))->render());
    }
    
    protected function componentData() {
        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => trim(ob_get_clean())],
            $this->slots[count($this->componentStack)]
        );
    }
    
    public function startSlot($name) {
        if (ob_start()) {
            $this->slots[$this->currentComponent()][$name] = '';
            $this->slotStack[$this->currentComponent()][] = $name;
        }
    }
    
    public function endSlot() {
        // last($this->componentStack);
        $currentSlot = array_pop(
            $this->slotStack[$this->currentComponent()]
        );
        $this->slots[$this->currentComponent()][$currentSlot] = trim(ob_get_clean());
    }
    
    protected function currentComponent() {
        return count($this->componentStack) - 1;
    }

}

?>
