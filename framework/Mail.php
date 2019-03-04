<?php
namespace app\framework;

use PHPMailer\PHPMailer;

class Mail {
    public static function send($to, $subject, $body) {
        $smtp_config = include("config/smtp.php");
        if (!is_array($to)) {
            $to = [$to];
        }
        $mail = new PHPMailer;      
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0; // off
        $mail->Host = $smtp_config["host"];
        $mail->Port = $smtp_config["port"];
        $mail->SMTPSecure = $smtp_config["encryption"];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config["username"];
        $mail->Password = $smtp_config["password"];
        $mail->setFrom($smtp_config["address"], $smtp_config["name"]);
        foreach ($to as $addr) {
            $mail->addAddress($addr);
        }
        $mail->Subject = $subject;
        $mail->msgHTML($body);
        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
            return false;
        } else {
            return true;
        }
    }
}

?>