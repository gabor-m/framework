<?php
namespace app\framework\process;

use app\framework\Helpers;
use app\framework\Route;

class Process {
    private static $processes = [];
    
    public static function add($name, $callback) {
        self::$processes[$name] = $callback;
    }
    
    public static function get($name = "") {
        if (isset(self::$processes[$name])) {
            return self::$processes[$name];
        }
        return function ($ignore) {
            // pass
        };
    }
    
    public static function spawn($name, $data = []) {
        $json_data = json_encode($data);
        $process_url = Helpers::absoluteRootUrl() . Route::to("app/framework/process/ProcessController@spawn", [
            "name" => $name,
            "data" => $json_data,
        ]);
        //Helpers::protectedCall(function () {
            Helpers::fetch($process_url, 0.1);
        //});
    }
    
    public static function addRoutes() {
        Route::get("/process/<name:[a-z0-9-]+>/spawn", "app/framework/process/ProcessController@spawn");
    }
}

?>