<?php
namespace app\framework\process;

use app\framework\Helpers;
use app\framework\Route;
use app\framework\Response;

class ProcessController {
    public function spawn($req) {
        ignore_user_abort(true);
        // set_time_limit(0);
        error_reporting(0);
        $name = $req->get("name");
        $data = $req->getJson("data");
        (Process::get($name))($data);
        return Response::json([
            "name" => $name,
            "status" => "done",
        ]);
    }
}

?>