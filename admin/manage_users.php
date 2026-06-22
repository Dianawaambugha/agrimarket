diana 
<?php

session_start();

require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

$stmt = $conn->prepare("
SELECT *
FROM users
ORDER BY user_id DESC
");

$stmt->execute();

$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>
<title>Manage Users</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Manage Users</h2>

<hr>

<table>

<tr>
<th>ID</th>
<th>Full Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>Status</th>
<th>Created At</th>
<th>Actions</th>
</tr>

<?php foreach($users as $user): ?>

<tr>

<td>
<?php echo $user["user_id"]; ?>
</td>

<td>
<?php echo htmlspecialchars($user["full_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($user["email"]); ?>
</td>

<td>
<?php echo htmlspecialchars($user["phone_number"]); ?>
</td>

<td>
<?php echo $user["role"]; ?>
</td>

<td>
<?php echo $user["status"]; ?>
</td>

<td>
<?php echo $user["created_at"]; ?>
</td>

<td>

<?php
if(
    $user["role"] == "Farmer" &&
    $user["status"] == "Inactive"
)
{
?>

<a href="approve_farmer.php?id=<?php echo $user['user_id']; ?>">
Approve
</a>

<br><br>

<?php
}
?>

<?php
if(
    $user["role"] != "Admin"
)
{
?>

<a
href="delete_user.php?id=<?php echo $user['user_id']; ?>"
onclick="return confirm('Delete this user permanently?');"
>
Delete
</a>

<?php
}
else
{
?>

Protected

<?php
}
?>

</td>

</tr>

<?php endforeach; ?>

</table>

<br><br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>