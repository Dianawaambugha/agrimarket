diana 
<?php

session_start();
require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"]!="Farmer")
{
    die("Access Denied");
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
SELECT DISTINCT

u.user_id,
u.full_name,
u.role

FROM users u

INNER JOIN messages m

ON
(
m.sender_id=u.user_id
AND m.receiver_id=?
)

OR
(
m.receiver_id=u.user_id
AND m.sender_id=?
)

ORDER BY u.full_name
");

$stmt->execute([
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

<h2>My Conversations</h2>

<?php foreach($conversations as $chat): ?>

<p>

<a href="conversation.php?user=<?php echo $chat["user_id"]; ?>">

<?php echo htmlspecialchars($chat["full_name"]); ?>

(<?php echo $chat["role"]; ?>)

</a>

</p>

<?php endforeach; ?>

<hr>

<h3>Admin</h3>

<?php

$admins = $conn->query("
SELECT user_id,full_name
FROM users
WHERE role='Admin'
")->fetchAll();

foreach($admins as $admin)
{
?>

<p>

<a href="conversation.php?user=<?php echo $admin["user_id"]; ?>">

💬 <?php echo htmlspecialchars($admin["full_name"]); ?>

</a>

</p>

<?php
}
?>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>
