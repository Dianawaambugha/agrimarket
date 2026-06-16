<?php

require_once "auth_check.php";

$product_count = $conn->prepare("
SELECT COUNT(*) total
FROM products
WHERE farmer_id=?
");

$product_count->execute([$farmer_id]);

$total_products =
$product_count->fetch()["total"];

$order_count = $conn->prepare("
SELECT COUNT(*) total
FROM orders
INNER JOIN products
ON orders.product_id = products.product_id
WHERE products.farmer_id=?
");

$order_count->execute([$farmer_id]);

$total_orders =
$order_count->fetch()["total"];

?>

<!DOCTYPE html>
<html>

<head>
<title>Farmer Dashboard</title>
</head>

<body>

<h2>
Welcome <?php echo htmlspecialchars($_SESSION["name"]); ?>
</h2>

<hr>

<h3>Dashboard Summary</h3>

<p>
Total Products:
<?php echo $total_products; ?>
</p>

<p>
Total Orders:
<?php echo $total_orders; ?>
</p>

<hr>

<h3>Products</h3>

<a href="add_products.php">
➕Add Products
</a>

<br><br>

<a href="view_products.php">
📋View Products
</a>

<br><br>

<h3>Farm Profile</h3>
<a href="profile.php">
🏡 Farm Profile
</a>

<br><br>

<a href="view_orders.php">
📦 View Orders
</a>

<br><br>


<a href="delivery_tracking.php">
🚚 Delivery Tracking
</a>
<br><br>

<a href="market_analytics.php">
📈 Market Analytics
</a>
<br><br>
<a href="update_market_prices.php">
💰 update market prices
</a>
<br><br>
<a href="messages.php">
💬 Messages
</a>
<br><br>
<a href="market_insights.php">
📈 Market Insights
</a>

<br><br>
<a href="../auth/logout.php">
Logout
</a>

</body>
</html>