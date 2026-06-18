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

?>

<!DOCTYPE html>
<html>

<head>
<title>Export Reports</title>
</head>

<body>

<h2>Export Reports</h2>

<ul>

<li>
<a href="export_users.php">
Export Users Report
</a>
</li>

<br>

<li>
<a href="export_orders.php">
Export Orders Report
</a>
</li>

<br>

<li>
<a href="export_market_prices.php">
Export Market Prices Report
</a>
</li>

</ul>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>