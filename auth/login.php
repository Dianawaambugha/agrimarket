diana 
<?php

session_start();

require_once "../config/db.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE email=?
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if(
        $user &&
        password_verify(
            $password,
            $user["password"]
        )
    )
    {
        if($user["status"] != "Active")
        {
            $message =
            "Your account is awaiting administrator approval.";
        }
        else
        {
            $_SESSION["user_id"] =
            $user["user_id"];

            $_SESSION["name"] =
            $user["full_name"];

            $_SESSION["role"] =
            $user["role"];

            if($user["role"] == "Farmer")
            {
                header(
                "Location: ../farmer/dashboard.php"
                );
                exit();
            }

            if($user["role"] == "Buyer")
            {
                header(
                "Location: ../buyer/dashboard.php"
                );
                exit();
            }

            if($user["role"] == "Admin")
            {
                header(
                "Location: ../admin/dashboard.php"
                );
                exit();
            }
        }
    }
    else
    {
        $message =
        "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
</head>

<body>

<h2>Login</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<input
type="email"
name="email"
placeholder="Email"
required>

<br><br>

<input
type="password"
name="password"
placeholder="Password"
required>

<br><br>

<button type="submit">
Login
</button>

</form>

</body>
</html>