<?php
namespace app\controllers;

use app\framework\Controller;
use app\framework\Response;

class MainController extends Controller {

    public function index($req) {
        return [
            "x" => 15
        ];
    }
    
}

?>