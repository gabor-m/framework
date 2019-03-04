<?php
namespace app\framework;

class Pagination {
    private $query;
    public $total;
    public $pageSize;
    
    public function __construct($query, $page_size) {
        $countQuery = clone $query; 
        $this->query = $query;
        $this->total = $countQuery->count();
        $this->pageSize = $page_size;
    }
    
    public function page($index) {
        $query = clone $this->query;
        $offset = $index * $this->pageSize;
        $limit = $this->pageSize;
        return $query->offset($offset)->limit($limit)->all();
    }
    
    public function pageCount() {
        return ceil(max(1, $this->total / $this->pageSize));
    }
}