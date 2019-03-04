<?php
use app\framework\Application;

spl_autoload_register(function ($class_name) {
    require str_replace("\\", "/", str_replace("app\\", "", $class_name)) . ".php";
});

Application::init();

