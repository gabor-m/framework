<?php
namespace app\controllers;

use app\framework\Controller;
use app\framework\Response;
use app\framework\Mail;
use app\framework\Storage;

class MainController extends Controller {

    public function index($req) {
        // return Response::view("test");
        
        return [
            "files" => $_FILES,
            "image" => $req->file("image")->store(),
        ];
    }
    
    public function paramTest($req) {
        return [
            "id" => $req->get("id"),
            "param" => $req->get("token"),
        ];
    }
}

?>