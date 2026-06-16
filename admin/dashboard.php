<?php

session_start();

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
</head>

<body>

<h2>
Welcome Admin:
<?php echo $_SESSION["name"]; ?>
</h2>

<hr>

<h3>User Management</h3>

<a href="approve_farmers.php">
Approve Farmers
</a>

<br><br>

<a href="manage_users.php">
Manage Users
</a>

<br><br>

<h3>Market Analytics</h3>

<a href="market_prices.php">
Market Prices
</a>

<br><br>

<a href="reports.php">
System Reports
</a>

<br><br>

<a href="audit_logs.php">
Audit Logs
</a>
<br><br>
<a href="audit_logs.php">
View Audit Logs
</a>

<br><br>
<a href="reports.php">
System Reports
</a>
<br><br>
<a href="price_predictions.php">
Price Predictions
</a>

<br><br>

<hr>

<a href="../auth/logout.php">
Logout
</a>

</body>
</html>