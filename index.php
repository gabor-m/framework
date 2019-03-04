<?php
use app\framework\Application;
use PHPMailer\PHPMailer;

spl_autoload_register(function ($class_name) {
    $path = str_replace("\\", "/", str_replace("app\\", "", $class_name)) . ".php";
    if (!file_exists($path)) {
        $path = "vendor/" . $path;
    }
    require $path;
});

Application::init();
