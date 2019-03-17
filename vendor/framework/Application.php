<?php
namespace app\framework;

use app\framework\cron\Cron;
use app\framework\process\Process;

class Application {
    private static $settings;
    
    public static function init() {
        self::$settings = (object) include("config/app.php");
        // DB
        Database::init();
        // Routes
        if (self::$settings->enable_processes) {
            Process::addRoutes();
            require "config/process.php";
        }
        if (self::$settings->enable_cron) {
            Cron::addRoutes();
            require "config/cron.php";
        }
        require "config/route.php";
        // Action
        Route::performAction();
        // Start cron
        Cron::start();
    }
}

?>