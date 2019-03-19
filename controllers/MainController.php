<?php
namespace app\controllers;

use app\framework\Controller;
use app\framework\Response;
use app\framework\Mail;
use app\framework\Storage;
use app\models\User;
use app\framework\Route;
use app\framework\process\Process;
use app\framework\Crypto;

class MainController extends Controller {

    public function index($req) {
        $user = User::findOne(1);
        $user->profile_pic = "da39a3ee5e6b4b0d3255bfef95601890afd80709";
        $user->save();
        $users = User::find()->all();
        // Process::spawn("test", ["message" => "Hello World"]);
        return Response::view("test", [
            "x" => 15,
            "users" => $users,
        ]);
        // return ["x" => 15]; // Response::download("122a76d0f09a52e7cccc74d1bb10d3ff367886e7");
    }
    
    public function first($req) {
        if ($req->get("first", "")) {
            return Response::html("<h1>First</h1>");
        }
    }
    
    public function url($path) {
        
    }
    
    public function paramTest($req) {
        return [
            "id" => $req->get("id"),
            "param" => $req->get("token"),
            "url" => Route::to("MainController@paramTest", ["id"=>15, "token"=>26]),
            "json" => $req->getjson("json", []),
            "cipher" => Crypto::decrypt(Crypto::encrypt([
                "secret" => 42
            ])),
        ];
    }
}

?>