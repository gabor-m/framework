<?php
use app\framework\process\Process;

Process::add("test", function ($data) {
    sleep(10);
    file_put_contents("storage/temp/process.test.ended", $data->message);
});

?>