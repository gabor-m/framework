<?php
namespace app\framework;

use app\framework\cron\Cron;

class Application {
    public static function init() {
        // DB
        Database::init();
        // Routes
        require "config/route.php";
        // Crons
        require "config/cron.php";
        // Action
        Route::performAction();
        // Start cron
        Cron::start();
    }
}

?>