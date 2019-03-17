<?php
namespace app\framework;

class Helpers {
    public static function absoluteRootUrl() {
        $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on';
        $scheme = ($is_https ? "https" : "http") . "://";
        $host = $_SERVER['HTTP_HOST'];
        $port_number = intval($_SERVER['SERVER_PORT']);
        $port = ($is_https && $port_number !== 443) || (!$is_https && $port_number !== 80) ? ":" . strval($port_number) : "";
        return $scheme . $host . $port;
    }
    
    public static function fetch($url, $timeout = 8) {
        $stream_ctx = stream_context_create([
            'http'=> [
                'timeout' => $timeout, // in sec
            ]
        ]);
        file_get_contents($url, false, $stream_ctx);
    }
}

?>