<?php
namespace app\modules\dbadmin\controllers;

use app\framework\Controller;

class MainController extends Controller {
    public function index() {
        return [
            "test" => true,
        ];
    }
}

?>