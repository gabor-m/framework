<?php
namespace app\framework;

class Cron {
    public static $sleep = 10; // 10 sec
    
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
            $tick_url = Helpers::absoluteRootUrl() . Route::to("CronController@tick");
            Helpers::fetch($tick_url, 0.1); // 0.1 sec
        }
    }
    
    public static function tick() {
        file_put_contents("last_cron_tick", strval(time()));
    }
    
    public static function nextJob() {
        // TEST job
        // file_put_contents("storage/temp/" . strval(time()) . ".cron.tick", "pk");
    }
}

?>