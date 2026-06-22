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

/*
|--------------------------------------------------------------------------
| GET CONVERSATIONS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT DISTINCT
u.user_id,
u.full_name,
u.role

FROM users u

INNER JOIN messages m
ON
(
    m.sender_id = u.user_id
    AND m.receiver_id = ?
)
OR
(
    m.receiver_id = u.user_id
    AND m.sender_id = ?
)

WHERE u.user_id != ?

ORDER BY u.full_name
");

$stmt->execute([
    $user_id,
    $user_id,
    $user_id
]);

$conversations = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>

<title>Messages</title>

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<div class="header">

<h2>💬 My Conversations</h2>

</div>

<div class="navigation">

<a href="dashboard.php">Dashboard</a>

<a href="marketplace.php">Marketplace</a>

<a href="my_orders.php">My Orders</a>

<a href="profile.php">Profile</a>

</div>

<div class="card">

<h3>Open Conversation</h3>

<?php if(empty($conversations)): ?>

<p>
No conversations available yet.
Purchase a product and contact a farmer first.
</p>

<?php else: ?>

<?php foreach($conversations as $person): ?>

<div class="message-card">

<h4>
<?php echo htmlspecialchars($person["full_name"]); ?>
</h4>

<p>
<?php echo htmlspecialchars($person["role"]); ?>
</p>

<a
class="btn"
href="conversation.php?user=<?php echo $person["user_id"]; ?>">
Open Conversation
</a>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

<a class="btn" href="dashboard.php">
← Back To Dashboard
</a>

</div>

</body>
</html>