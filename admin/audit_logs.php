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
SELECT
a.*,
u.full_name
FROM audit_logs a
LEFT JOIN users u
ON a.user_id = u.user_id
ORDER BY a.action_date DESC
");

$stmt->execute();

$logs = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>
<title>Audit Logs</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Audit Logs</h2>

<hr>

<table>

<tr>
<th>Log ID</th>
<th>User</th>
<th>Action Performed</th>
<th>Date</th>
</tr>

<?php foreach($logs as $log): ?>

<tr>

<td>
<?php echo $log["log_id"]; ?>
</td>

<td>
<?php echo htmlspecialchars($log["full_name"] ?? "Unknown"); ?>
</td>

<td>
<?php echo htmlspecialchars($log["action_performed"]); ?>
</td>

<td>
<?php echo $log["action_date"]; ?>
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