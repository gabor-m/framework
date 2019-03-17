<?php
namespace app\framework\cron;

use app\framework\Helpers;
use app\framework\Route;
use app\framework\Response;

class CronController {
    public function tick() {
        ignore_user_abort(true);
        error_reporting(0);
        Helpers::protectedCall(function () {
            if (Cron::needTick()) { 
                Cron::tick();
                $job_url = Helpers::absoluteRootUrl() . Route::to("app/framework/cron/CronController@nextJob");           
                Helpers::fetch($job_url, 1); // timeout: 1 sec
                sleep(Cron::$sleep);
                $tick_url = Helpers::absoluteRootUrl() . Route::to("app/framework/cron/CronController@tick");
                Helpers::fetch($tick_url, 1); // call itself (recursion); timeout: 1 sec
            }
        });
    }
    
    public function nextJob() {
        ignore_user_abort(true);
        error_reporting(0);
        Helpers::protectedCall(function () {
            Cron::performNextJob();
        });
    }
    
    public function lastTick() {
        return Response::json(date('Y-m-d H:i:s', Cron::lastTick()));
    }
}

?>