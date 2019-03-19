<?php
namespace app\framework;

class Crypto {
    private const CIPHER = 'AES-256-CTR';
    
    public static function randomBytesRaw($len = 16) {
        $bytes = null;
        if (!function_exists('random_bytes')) {
            try {
                $bytes = random_bytes(16);
            } catch (\Throwable $ignore) { // For PHP 7
                
            } catch (\Exception $ignore) { // For PHP 5
                
            }
        }
        if ($bytes === null) {
            // fallback (insecure, but it works)
            $bytes = "";
            for ($i = 0; $i < $len; $i += 1) {
                $bytes .= chr(rand(0, 255));
            }
        }
        return $bytes;
    }
    
    public static function randomBytes($len = 16) {
        return bin2hex(self::randomBytesRaw($len));
    }
    
    public static function encryptRaw($value) {
        $key = self::sha512Raw(Application::$settings->encryption_key);
        $enc_key = substr($key, 0, 16);
        $mac_key = substr($key, 16);
        $iv = self::randomBytesRaw(openssl_cipher_iv_length(self::CIPHER));
        $ciphertext = \openssl_encrypt($value, self::CIPHER, $enc_key, OPENSSL_RAW_DATA, $iv);
        $mac = substr(hash_hmac('sha256', $iv . $ciphertext, $mac_key, true), 0, 16);
        return $mac . $iv . $ciphertext;
    }
    
    public static function decryptRaw($value) {
        if (strlen($value) < 32) {
            return null;
        }
        $key = self::sha512Raw(Application::$settings->encryption_key);
        $enc_key = substr($key, 0, 16);
        $mac_key = substr($key, 16);
        $mac = substr($value, 0, 16);
        $iv = substr($value, 16, 16);
        $ciphertext = substr($value, 32);
        $plaintext = \openssl_decrypt($ciphertext, self::CIPHER, $enc_key, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            return null;
        }
        $valid_mac = substr(hash_hmac('sha256', $iv . $ciphertext, $mac_key, true), 0, 16) === $mac;
        if (!$valid_mac) {
            return null;
        }
        return $plaintext;
    }
    
    public static function encrypt($value, $serialize = true) {
        if ($serialize) {
            $value = json_encode($value);
        }
        return base64_encode(self::encryptRaw($value));
    }
    
    public static function decrypt($value, $unserialize = true) {
        $value = base64_decode($value);
        $decrypted = self::decryptRaw($value);
        if ($unserialize) {
            $decrypted = json_decode($decrypted);
        }
        return $decrypted;
    }
    
    public static function encryptString($str) {
        return self::encrypt($value, false);
    }
    
    public static function decryptString($str) {
        return self::decrypt($value, false);
    }
    
    public static function sha256() {
        return hash('sha256', $str);
    }
    
    public static function sha256Raw($str) {
        return hash('sha256', $str, true);
    }
    
    public static function sha512() {
        return hash('sha512', $str);
    }
    
    public static function sha512Raw($str) {
        return hash('sha512', $str, true);
    }
}

?>