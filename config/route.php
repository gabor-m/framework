<?php
use app\framework\Route;

Route::get("/test", "MainController@index");
Route::get("/param-test/<id:[0-9]+>/<token:[a-f0-9]+>", "MainController@paramTest");

?>