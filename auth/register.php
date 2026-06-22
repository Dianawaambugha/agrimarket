diana 
<?php

session_start();

require_once "../config/db.php";
require_once "../config/mail.php";

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    $hashed_password =
    password_hash($password,PASSWORD_DEFAULT);

    try
    {
        $check = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE email=?
        ");

        $check->execute([$email]);

        if($check->rowCount() > 0)
        {
            $message = "Email already exists.";
        }
        else
        {
            $otp = rand(100000,999999);

            $_SESSION["otp"] = $otp;

            $_SESSION["full_name"] = $full_name;
            $_SESSION["email"] = $email;
            $_SESSION["phone"] = $phone;
            $_SESSION["password"] = $hashed_password;
            $_SESSION["role"] = $role;

            if(sendOTP($email,$otp))
            {
                header("Location: verify_otp.php");
                exit();
            }
            else
            {
                $message = "Failed to send OTP email.";
            }
        }
    }
    catch(PDOException $e)
    {
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>

<html>
<head>
<title>Register</title>
</head>

<body>

<h2>Agri-Market Connect Registration</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<input
type="text"
name="full_name"
placeholder="Full Name"
required>

<br><br>

<input
type="email"
name="email"
placeholder="Email"
required>

<br><br>

<input
type="text"
name="phone"
placeholder="Phone Number"
required>

<br><br>

<input
type="password"
name="password"
placeholder="Password"
required>

<br><br>

<select name="role" required>

<option value="">
Select Role
</option>

<option value="Farmer">
Farmer
</option>

<option value="Buyer">
Buyer
</option>

</select>

<br><br>

<button type="submit">
Register
</button>

</form>

</body>
</html>
?>
