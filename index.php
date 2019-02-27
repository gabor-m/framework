<?php

echo $_SERVER['REQUEST_URI'];

function require_all_php_files() {
    $all_files = glob('./**/*.php');
    foreach ($all_files as $file) {
        require $file;
    }
}

require_all_php_files();

use app\models\Test;
use app\database\Database;

var_dump(\app\models\Model::allModels());

Database::init();
var_dump(Database::columns("user2"));

