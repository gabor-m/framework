<?php
namespace app\controllers;

use app\framework\Helpers;
use app\framework\Route;
use app\framework\Cron;

class CronController {
    public function tick() {
        ignore_user_abort(true);
        if (Cron::needTick()) { 
            Cron::tick();
            $job_url = Helpers::absoluteRootUrl() . Route::to("CronController@nextJob");           
            Helpers::fetch($job_url, 1); // timeout: 1 sec
            sleep(Cron::$sleep);
            $tick_url = Helpers::absoluteRootUrl() . Route::to("CronController@tick");
            Helpers::fetch($tick_url, 1); // call itself (recursion); timeout: 1 sec
        }
    }
    
    public function nextJob() {
        ignore_user_abort(true);
        Cron::nextJob();
    }
}

?>