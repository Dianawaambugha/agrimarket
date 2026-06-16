<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendOTP($email, $otp)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'dianawambugha22@gmail.com';

        $mail->Password = 'lwbs rcul dskd pgml';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom(
            'dianawambugha22@gmail.com',
            'Agri Market Connect'
        );

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'Agri Market Connect OTP';

        $mail->Body = "
            <h2>Email Verification</h2>
            <p>Your OTP code is:</p>
            <h1>$otp</h1>
            <p>Expires in 20 minutes.</p>
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;
    }
}