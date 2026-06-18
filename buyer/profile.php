<?php

session_start();
require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Buyer")
{
    die("Access Denied");
}

$user_id = $_SESSION["user_id"];

$message = "";

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $full_name = trim($_POST["full_name"]);
    $phone_number = trim($_POST["phone_number"]);
    $delivery_address = trim($_POST["delivery_address"]);

    $update_user = $conn->prepare("
    UPDATE users
    SET
    full_name=?,
    phone_number=?
    WHERE user_id=?
    ");

    $update_user->execute([
        $full_name,
        $phone_number,
        $user_id
    ]);

    $update_buyer = $conn->prepare("
    UPDATE buyers
    SET delivery_address=?
    WHERE user_id=?
    ");

    $update_buyer->execute([
        $delivery_address,
        $user_id
    ]);

    $_SESSION["name"] = $full_name;

    $message = "Profile updated successfully.";
}

/*
|--------------------------------------------------------------------------
| LOAD PROFILE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
u.full_name,
u.email,
u.phone_number,
u.created_at,
b.delivery_address

FROM users u

INNER JOIN buyers b
ON u.user_id = b.user_id

WHERE u.user_id=?
");

$stmt->execute([$user_id]);

$profile = $stmt->fetch();

?>

<!DOCTYPE html>
<html>

<head>

<title>Buyer Profile</title>

<style>

body{
    font-family:Arial;
    margin:20px;
}

input,
textarea{
    width:400px;
    padding:8px;
}

</style>

</head>

<body>

<h2>My Profile</h2>

<p style="color:green;">
<?php echo $message; ?>
</p>

<form method="POST">

<label>Full Name</label>
<br>

<input
type="text"
name="full_name"
value="<?php echo htmlspecialchars($profile["full_name"]); ?>"
required>

<br><br>

<label>Email Address</label>
<br>

<input
type="email"
value="<?php echo htmlspecialchars($profile["email"]); ?>"
readonly>

<br><br>

<label>Phone Number</label>
<br>

<input
type="text"
name="phone_number"
value="<?php echo htmlspecialchars($profile["phone_number"]); ?>"
required>

<br><br>

<label>Delivery Address</label>
<br>

<textarea
name="delivery_address"
rows="5"
required><?php echo htmlspecialchars($profile["delivery_address"]); ?></textarea>

<br><br>

<label>Member Since</label>
<br>

<input
type="text"
value="<?php echo $profile["created_at"]; ?>"
readonly>

<br><br>

<button type="submit">
Update Profile
</button>

</form>

<br><br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>