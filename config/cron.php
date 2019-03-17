<?php
use app\framework\cron\Cron;

$job = Cron::add(function () {
    file_put_contents("storage/temp/" . time() . ".cron.tick", "HELLO WORLD");
})->hourly("00, 01, 02, 03");

?>