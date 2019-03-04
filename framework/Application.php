<?php
namespace app\framework;

class Application {
    public static function init() {
        // DB
        Database::init();
        // Routes
        require "config/route.php";
        Route::performAction();
    }
}

?>