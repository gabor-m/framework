<?php
namespace app\controllers;

use app\framework\Controller;
use app\framework\Response;
use app\framework\Mail;
use app\framework\Storage;

class MainController extends Controller {

    public function index($req) {
        // return Response::view("test");
        return [];//Response::download("122a76d0f09a52e7cccc74d1bb10d3ff367886e7");
    }
    
    public function paramTest($req) {
        return [
            "id" => $req->get("id"),
            "param" => $req->get("token"),
        ];
    }
}

?>