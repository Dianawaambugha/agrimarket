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
<link rel="stylesheet"
href="../assets/css/style.css">


</head>

<body>

<<div class="container">

<div class="header">
    <h2>👋 Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></h2>
    <p>Manage your purchases, orders and market insights.</p>
</div>

<div class="stats-grid">

```
<div class="stat-card green">
    <h3>📦 Total Orders</h3>
    <p><?php echo $total_orders; ?></p>
</div>

<div class="stat-card orange">
    <h3>⏳ Pending Orders</h3>
    <p><?php echo $pending_orders; ?></p>
</div>

<div class="stat-card blue">
    <h3>🚚 Delivered Orders</h3>
    <p><?php echo $delivered_orders; ?></p>
</div>

<div class="stat-card purple">
    <h3>💰 Total Spent</h3>
    <p>KES <?php echo number_format($total_spent,2); ?></p>
</div>
```

</div>

<div class="card">

<h3>🛍 Buyer Services</h3>

<div class="service-grid">

<div class="service-card">
<h2>🛒</h2>
<a href="marketplace.php">Browse Products</a>
</div>

<div class="service-card">
<h2>📦</h2>
<a href="my_orders.php">My Orders</a>
</div>

<div class="service-card">
<h2>💬</h2>
<a href="messages.php">Messages</a>
</div>

<div class="service-card">
<h2>📊</h2>
<a href="spending_analytics.php">Spending Analytics</a>
</div>

<div class="service-card">
<h2>📈</h2>
<a href="market_insights.php">Market Insights</a>
</div>

<div class="service-card">
<h2>👤</h2>
<a href="profile.php">My Profile</a>
</div>

<div class="service-card logout-card">
<h2>🚪</h2>
<a href="../auth/logout.php">Logout</a>
</div>

</div>

</div>

</div>


<div class="card">

<h3>Buyer Services</h3>

<br>

<a class="btn" href="marketplace.php">🛒 Browse Products</a>

<a class="btn" href="my_orders.php">📦 My Orders</a>

<a class="btn" href="messages.php">💬 Messages</a>

<a class="btn" href="spending_analytics.php">📊 Spending Analytics</a>

<a class="btn" href="market_insights.php">📈 Market Insights</a>

<a class="btn" href="profile.php">👤 My Profile</a>

<a class="btn btn-danger" href="../auth/logout.php">🚪 Logout</a>

</div>

</div>

</body>
</html>

