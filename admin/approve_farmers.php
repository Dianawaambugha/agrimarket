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

if(isset($_GET["approve"]))
{
    $user_id = $_GET["approve"];

    $stmt = $conn->prepare("
    UPDATE users
    SET status='Active'
    WHERE user_id=?
    ");

    $stmt->execute([$user_id]);

    header("Location: approve_farmers.php");
    exit();
}

$stmt = $conn->prepare("
SELECT *
FROM users
WHERE role='Farmer'
AND status='Inactive'
ORDER BY user_id DESC
");

$stmt->execute();

$farmers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Approve Farmers</title>
</head>

<body>

<h2>Pending Farmer Approvals</h2>

<table border="1" cellpadding="10">

<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Action</th>
</tr>

<?php foreach($farmers as $farmer): ?>

<tr>

<td>
<?php echo $farmer["user_id"]; ?>
</td>

<td>
<?php echo $farmer["full_name"]; ?>
</td>

<td>
<?php echo $farmer["email"]; ?>
</td>

<td>
<?php echo $farmer["phone_number"]; ?>
</td>

<td>

<a href="?approve=<?php echo $farmer["user_id"]; ?>">
Approve
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>