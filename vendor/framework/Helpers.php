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
    
    public static function fetch($url, $timeout = 10) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, floor($timeout * 1000));
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    public static function protectedCall($callback) {
        try {
            return @$callback();
        } catch (Throwable $e) {
            // PHP 7
            return null;
        } catch (Exception $e) {
            // PHP 5
            return null;
        }
    }
}

?>