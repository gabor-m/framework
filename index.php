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
use app\models\User;

use app\database\Database;

// var_dump(\app\models\Model::allModels());

Database::init();

/*
$test = User::findOne(1);
$test->username = 'gabor';
$test->suspended = 1;
$test->user_type = 'superadmin';
$test->save();
var_dump($test->asArray());

var_dump(Database::getForeignKey("user", "created_by"));
*/

$user = User::findOne(15);
$user->id = 2;
var_dump($user);

