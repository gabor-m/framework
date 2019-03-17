<?php
namespace app\framework\cron;

use app\framework\Route;
use app\framework\Helpers;

class Cron {
    public static $sleep = 10; // 10 sec
    private static $jobs = [];
    
    public static function lastTick() {
        if (!file_exists("last_cron_tick")) {
            return 0;
        }
        return intval(file_get_contents("last_cron_tick"));
    }
    
    public static function needTick() {
        $last_tick = self::lastTick();
        $now = time();
        $diff = $now - $last_tick;
        return $diff >= self::$sleep || $diff < 0; // ha kisebb mint 0, akkor inkonzisztens
    }
    
    public static function start() {
        $last_tick = self::lastTick();
        $now = time();
        $diff = $now - $last_tick;
        if ($diff > 60 || $diff < 0) { // ha kisebb mint 0, akkor inkonzisztens
            $tick_url = Helpers::absoluteRootUrl() . Route::to("app/framework/cron/CronController@tick");
            Helpers::fetch($tick_url, 0.1); // 0.1 sec
        }
    }
    
    public static function tick() {
        file_put_contents("last_cron_tick", strval(time()));
    }
    
    public static function performNextJob() {
        foreach (self::$jobs as $job) {
            if ($job->needPerform()) {
                $job->perform();
                return; // perform only 1 job at once
            }
        }
    }
    
    public static function add($callback) {
        $job = new CronJob($callback);
        self::$jobs[] = $job;
        return $job;
    }
    
    public static function addRoutes() {
        Route::get("/cron/tick", "app/framework/cron/CronController@tick");
        Route::get("/cron/next-job", "app/framework/cron/CronController@nextJob");
        Route::get("/cron/last-tick", "app/framework/cron/CronController@lastTick");
    }
}

?>