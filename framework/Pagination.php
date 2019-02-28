<?php
namespace app\database;

class Pagination {
    private $query;
    public $total;
    public $pageSize;
    
    public function __construct($query, $total, $page_size) {
        $this->query = $query;
        $this->total = $total;
        $this->pageSize = $page_size;
    }
    
    public function page($index) {
        $query = clone $this->query;
        $offset = $index * $this->pageSize;
        $limit = $this->pageSize;
        return $query->offset($offset)->limit($limit)->all();
    }
}