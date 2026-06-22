diana 
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
<link rel="stylesheet"
href="../assets/css/style.css">


</head>

<body>

<div class="container">

<div class="header">
    <h2>👤 My Profile</h2>
</div>

<div class="navigation">

<a href="dashboard.php">Dashboard</a>

<a href="marketplace.php">Marketplace</a>

<a href="my_orders.php">My Orders</a>

</div>

<?php if(!empty($message)): ?>

<div class="success">
    <?php echo $message; ?>
</div>

<?php endif; ?>

<div class="card">

<form method="POST">

<div class="form-group">

<label>Full Name</label>

<input
type="text"
name="full_name"
value="<?php echo htmlspecialchars($profile["full_name"]); ?>"
required>

</div>

<div class="form-group">

<label>Email Address</label>

<input
type="email"
value="<?php echo htmlspecialchars($profile["email"]); ?>"
readonly>

</div>

<div class="form-group">

<label>Phone Number</label>

<input
type="text"
name="phone_number"
value="<?php echo htmlspecialchars($profile["phone_number"]); ?>"
required>

</div>

<div class="form-group">

<label>Delivery Address</label>

<textarea
name="delivery_address"
rows="5"
required><?php echo htmlspecialchars($profile["delivery_address"]); ?></textarea>

</div>

<div class="form-group">

<label>Member Since</label>

<input
type="text"
value="<?php echo $profile["created_at"]; ?>"
readonly>

</div>

<button class="btn" type="submit">
Update Profile
</button>

</form>

</div>

<a class="btn" href="dashboard.php">
← Back To Dashboard
</a>

</div>

</body>
</html>