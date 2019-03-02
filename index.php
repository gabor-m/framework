<?php

// echo $_SERVER['REQUEST_URI'];

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
use app\View;

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
/*
$user = User::findOne(1);
var_dump($user);
$user->data = [5,5,5,5,[false]];
$user->save();
var_dump($user->data);

$u = new User();

var_dump($u);
$u->data="[]";
var_dump($u);
$u->save();
*/

// var_dump(User::find()->paginate(10)->pageCount());

$wtf = 5;

$view = new View();
$view->setRoot("views");
// $view->layout = "layout.tmpl";
echo $view->render("a", [
    "wtf" => $wtf,
]);