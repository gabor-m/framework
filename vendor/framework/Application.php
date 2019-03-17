<?php
namespace app\framework;

class Application {
    public static function init() {
        // DB
        Database::init();
        // Routes
        require "config/route.php";
        // Start cron
        Cron::start();
        // Action
        Route::performAction();
    }
}

?>