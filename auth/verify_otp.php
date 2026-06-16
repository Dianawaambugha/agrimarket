<?php

session_start();

require_once "../config/db.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $otp = $_POST["otp"];

    if($otp == $_SESSION["otp"])
    {
        $stmt = $conn->prepare("
            INSERT INTO users
            (
                full_name,
                email,
                phone_number,
                password,
                role
            )
            VALUES
            (?,?,?,?,?)
        ");

        $stmt->execute([
            $_SESSION["full_name"],
            $_SESSION["email"],
            $_SESSION["phone"],
            $_SESSION["password"],
            $_SESSION["role"]
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

<h2>Enter OTP</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<input
type="text"
name="otp"
placeholder="OTP"
required>

<br><br>

<button type="submit">
Verify
</button>

</form>

</body>
</html>