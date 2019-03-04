<?php
namespace app\controllers;

use app\framework\Controller;
use app\framework\Response;
use app\framework\Mail;

class MainController extends Controller {

    public function index($req) {
        return Response::view("test");
    }
    
}

?>