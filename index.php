<?php
use app\framework\Application;

spl_autoload_register(function ($class_name) {
    $path = str_replace("\\", "/", str_replace("app\\", "", $class_name)) . ".php";
    if (!file_exists($path)) {
        $path = "vendor/" . $path;
    }
    require $path;
    if (method_exists($class_name, "__load") && (new ReflectionMethod($class_name, "__load"))->isStatic()) {
        $class_name::__load($class_name);
    }
});

Application::init();
