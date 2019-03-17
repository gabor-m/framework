<?php
use app\framework\Route;
use app\framework\cron\Cron;

Route::get(".*", "MainController@first");
Route::get("/test", "MainController@index");
Route::get("/param-test/<id:[0-9]+>/<token:[a-f0-9]+>", "MainController@paramTest");

// CRON
Cron::addRoutes();

?>