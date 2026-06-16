<?php

require_once "config/mail.php";

$otp = rand(100000,999999);

if(sendOTP("YOUR_PERSONAL_EMAIL@gmail.com", $otp))
{
    echo "Email sent successfully. OTP = " . $otp;
}
else
{
    echo "Failed to send email.";
}