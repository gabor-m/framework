<?php
namespace app\framework\cron;

use app\framework\Helpers;

class CronJob {
    private $callback;
    private $type;
    private $daily_times = [];
    private $hourly_times = [];
    private $hash;
    private $file_path;
    
    public function __construct($callback) {
        $this->callback = $callback;
        $this->hash = sha1(strval(new \ReflectionFunction($callback)));
        $this->file_path = "storage/_cron/" . $this->hash . ".last";
    }
    
    public function perform() {
        $date = $this->needPerform();
        if ($date) {
            file_put_contents($this->file_path, $date);
            $callback = $this->callback;
            $callback();
        }
    }
    
    public function needPerform() {
        if ($this->type === null) {
            return false;
        }
        $now = time();
        $last_time = strtotime($this->lastDate());
        foreach ($this->generateDates() as $date) {
            $time = strtotime($date);
            $min = $now - 30; // 30 sec
            $max = $now + 30; // 30 sec
            if ($time >= $min && $time <= $max && $last_time !== $time) {
                return $date;
            }
        }
        return false;
    }
    
    public function hourly($times = "00") {
        $this->type = "hourly";
        $times = explode(",", $times);
        $filtered = [];
        foreach ($times as $t) {
            $filtered[] = trim($t);
        }
        $this->hourly_times = $filtered;
        return $this;
    }
    
    public function daily($times = "00:00") {
        $this->type = "daily";
        $times = explode(",", $times);
        $filtered = [];
        foreach ($times as $t) {
            $filtered[] = trim($t);
        }
        $this->daily_times = $filtered;
        return $this;
    }
    
    public function generateDates() {
        $dates = [];
        $today = date("Y-m-d");
        $this_hour = date("H");
        switch ($this->type) {
        case "daily":
            foreach ($this->daily_times as $time) {
                $dates[] = $today . " " . $time . ":00";
            }
            break;
        case "hourly":
            foreach ($this->hourly_times as $time) {
                $dates[] = $today . " " . $this_hour . ":" . $time . ":00";
            }
            break;
        }
        return $dates;
    }
    
    private function lastDate() {
        if (!file_exists($this->file_path)) {
            return "1990-01-01 00:00:00";
        }
        return file_get_contents($this->file_path);
    }
}

?>