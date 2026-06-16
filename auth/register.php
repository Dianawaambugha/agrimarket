<?php

require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    $hashed_password =
    password_hash($password, PASSWORD_DEFAULT);

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
            /*
            Farmers require approval.
            Buyers are active immediately.
            */

            $status = "Active";

            if($role == "Farmer")
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
                $full_name,
                $email,
                $phone,
                $hashed_password,
                $role,
                $status
            ]);

            $user_id = $conn->lastInsertId();

            if($role == "Farmer")
            {
                $farmer = $conn->prepare("
                INSERT INTO farmers(user_id)
                VALUES(?)
                ");

                $farmer->execute([$user_id]);

                $message =
                "Registration successful. Your account is awaiting administrator approval.";
            }
            else
            {
                $buyer = $conn->prepare("
                INSERT INTO buyers(user_id)
                VALUES(?)
                ");

                $buyer->execute([$user_id]);

                $message =
                "Registration successful. You can now login.";
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