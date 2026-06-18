<?php

session_start();

require_once "../config/db.php";

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $otp = trim($_POST["otp"]);

    if(
        isset($_SESSION["otp"])
        &&
        $otp == $_SESSION["otp"]
    )
    {
        $status = "Active";

        if($_SESSION["role"]=="Farmer")
        {
            $status = "Inactive";
        }

        $stmt = $conn->prepare("
        INSERT INTO users
        (
            full_name,
            email,
            phone_number,
            password,
            role,
            status
        )
        VALUES
        (?,?,?,?,?,?)
        ");

        $stmt->execute([
            $_SESSION["full_name"],
            $_SESSION["email"],
            $_SESSION["phone"],
            $_SESSION["password"],
            $_SESSION["role"],
            $status
        ]);

        $user_id =
        $conn->lastInsertId();

        if($_SESSION["role"]=="Farmer")
        {
            $farmer =
            $conn->prepare("
            INSERT INTO farmers(user_id)
            VALUES(?)
            ");

            $farmer->execute([$user_id]);
        }

        if($_SESSION["role"]=="Buyer")
        {
            $buyer =
            $conn->prepare("
            INSERT INTO buyers(user_id)
            VALUES(?)
            ");

            $buyer->execute([$user_id]);
        }

        session_destroy();

        header("Location: login.php");
        exit();
    }
    else
    {
        $message = "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>

<html>
<head>
<title>Verify OTP</title>
</head>

<body>

<h2>Email Verification</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<input
type="text"
name="otp"
placeholder="Enter OTP"
required>

<br><br>

<button type="submit">
Verify OTP
</button>

</form>

</body>
</html>
