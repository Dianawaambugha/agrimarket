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

/*
|--------------------------------------------------------------------------
| GET BUYER ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT buyer_id
FROM buyers
WHERE user_id=?
");

$stmt->execute([$_SESSION["user_id"]]);

$buyer_id = $stmt->fetchColumn();

if(!$buyer_id)
{
    die("Buyer profile not found.");
}

/*
|--------------------------------------------------------------------------
| BUYER STATISTICS
|--------------------------------------------------------------------------
*/

$total_orders = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
");

$total_orders->execute([$buyer_id]);
$total_orders = $total_orders->fetchColumn();

$pending_orders = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
AND order_status='Pending'
");

$pending_orders->execute([$buyer_id]);
$pending_orders = $pending_orders->fetchColumn();

$delivered_orders = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
AND order_status='Delivered'
");

$delivered_orders->execute([$buyer_id]);
$delivered_orders = $delivered_orders->fetchColumn();

$total_spent = $conn->prepare("
SELECT IFNULL(SUM(total_amount),0)
FROM orders
WHERE buyer_id=?
");

$total_spent->execute([$buyer_id]);
$total_spent = $total_spent->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>

<title>Buyer Dashboard</title>

<style>

body{
    font-family:Arial;
    margin:20px;
}

.card{
    border:1px solid #ddd;
    padding:15px;
    margin-bottom:15px;
    background:#f8f8f8;
}

</style>

</head>

<body>

<h2>
Welcome <?php echo htmlspecialchars($_SESSION["name"]); ?>
</h2>

<hr>

<div class="card">

<h3>My Statistics</h3>

<p>
Total Orders:
<b><?php echo $total_orders; ?></b>
</p>

<p>
Pending Orders:
<b><?php echo $pending_orders; ?></b>
</p>

<p>
Delivered Orders:
<b><?php echo $delivered_orders; ?></b>
</p>

<p>
Total Spent:
<b>KES <?php echo number_format($total_spent,2); ?></b>
</p>

</div>

<hr>

<h3>Buyer Services</h3>

<a href="marketplace.php">
🛒 Browse Products
</a>

<br><br>

<a href="my_orders.php">
📦 My Orders
</a>

<br><br>
<a href="market_insights.php">
📈 Market Insights
</a>
<br><br>
<a href="profile.php">
👤 My Profile
</a>
<br><br>


<a href="../auth/logout.php">
🚪 Logout
</a>

</body>
</html>

