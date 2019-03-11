<?php
namespace app\controllers;

use app\framework\Controller;
use app\framework\Response;
use app\framework\Mail;
use app\framework\Storage;
use app\models\User;

class MainController extends Controller {

    public function index($req) {
        $user = User::findOne(1);
        $user->profile_pic = "da39a3ee5e6b4b0d3255bfef95601890afd80709";
        $user->save();
        // return Response::view("test");
        return []; // Response::download("122a76d0f09a52e7cccc74d1bb10d3ff367886e7");
    }
    
    public function first($req) {
        return false;
    }
    
    public function paramTest($req) {
        return [
            "id" => $req->get("id"),
            "param" => $req->get("token"),
        ];
    }
}

?>