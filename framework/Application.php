<?php
namespace app\framework;

class Application {
    /*
    public static function load_modules() {
        foreach (glob("modules/*") as $dir) {
            if (is_dir($dir)) {
                $route_file = $dir . "/config/route.php";
                if (file_exists($route_file)) {
                    require $route_file;
                }
            }
        }
    }
    */
    
    public static function init() {
        // DB
        Database::init();
        // Routes
        self::load_modules();
        require "config/route.php";
        Route::performAction();
    }
}

?>