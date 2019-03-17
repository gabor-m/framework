<?php
namespace app\framework\template;

class LoopMaker {
    protected $loopsStack = [];
    
    public function addLoop($data) {
        $length = is_array($data) || $data instanceof Countable ? count($data) : null;
        $parent = end($this->loopsStack);
        $this->loopsStack[] = [
            'iteration' => 0,
            'index' => 0,
            'remaining' => $length ?? null,
            'count' => $length,
            'first' => true,
            'last' => isset($length) ? $length == 1 : null,
            'depth' => count($this->loopsStack) + 1,
            'parent' => $parent ? (object) $parent : null,
        ];
    }
    
    public function incrementLoop() {
        $loop = $this->loopsStack[$index = count($this->loopsStack) - 1];
        $this->loopsStack[$index] = array_merge($this->loopsStack[$index], [
            'iteration' => $loop['iteration'] + 1,
            'index' => $loop['iteration'],
            'first' => $loop['iteration'] == 0,
            'remaining' => isset($loop['count']) ? $loop['remaining'] - 1 : null,
            'last' => isset($loop['count']) ? $loop['iteration'] == $loop['count'] - 1 : null,
        ]);
    }
    
    public function popLoop() {
        array_pop($this->loopsStack);
    }
    
    public function getLastLoop() {
        $last = end($this->loopsStack);
        if ($last) {
            return (object) $last;
        }
    }
    
    public function getLoopStack() {
        return $this->loopsStack;
    }
}

?>