<?php

require_once "auth_check.php";

/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM products
WHERE farmer_id=?
");

$stmt->execute([$farmer_id]);

$total_products = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| AVAILABLE PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM products
WHERE farmer_id=?
AND status='Available'
");

$stmt->execute([$farmer_id]);

$available_products = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| SOLD OUT PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM products
WHERE farmer_id=?
AND status='Sold Out'
");

$stmt->execute([$farmer_id]);

$sold_products = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| TOTAL ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
");

$stmt->execute([$farmer_id]);

$total_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| PENDING ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
AND o.order_status='Pending'
");

$stmt->execute([$farmer_id]);

$pending_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| DELIVERED ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
AND o.order_status='Delivered'
");

$stmt->execute([$farmer_id]);

$delivered_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| TOTAL REVENUE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT IFNULL(SUM(o.total_amount),0)
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
AND o.order_status='Delivered'
");

$stmt->execute([$farmer_id]);

$total_revenue = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| BEST SELLING PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
p.product_name,
SUM(o.quantity_ordered) total_sold
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
GROUP BY p.product_id
ORDER BY total_sold DESC
LIMIT 1
");

$stmt->execute([$farmer_id]);

$best_product = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| AVERAGE SELLING PRICE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT AVG(price_per_unit)
FROM products
WHERE farmer_id=?
");

$stmt->execute([$farmer_id]);

$average_price = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>

<title>Farmer Dashboard</title>

<style>

body{
    font-family:Arial;
    margin:20px;
}

.card{
    border:1px solid #ddd;
    padding:15px;
    margin-bottom:15px;
    border-radius:8px;
    background:#f8f8f8;
}

a{
    text-decoration:none;
}

</style>

</head>

<body>

<h2>
Welcome
<?php echo htmlspecialchars($_SESSION["name"]); ?>
</h2>

<hr>

<div class="card">

<h3>Product Statistics</h3>

<p>Total Products:
<b><?php echo $total_products; ?></b></p>

<p>Available Products:
<b><?php echo $available_products; ?></b></p>

<p>Sold Out Products:
<b><?php echo $sold_products; ?></b></p>

</div>

<div class="card">

<h3>Order Statistics</h3>

<p>Total Orders:
<b><?php echo $total_orders; ?></b></p>

<p>Pending Orders:
<b><?php echo $pending_orders; ?></b></p>

<p>Delivered Orders:
<b><?php echo $delivered_orders; ?></b></p>

</div>

<div class="card">

<h3>Revenue</h3>

<p>
KES
<b><?php echo number_format($total_revenue,2); ?></b>
</p>

</div>

<div class="card">

<h3>Performance</h3>

<p>
Best Selling Product:
<b>
<?php
echo $best_product
? htmlspecialchars($best_product["product_name"])
: "N/A";
?>
</b>
</p>

<p>
Average Selling Price:
<b>
KES <?php echo number_format($average_price,2); ?>
</b>
</p>

</div>

<hr>

<h3>Quick Actions</h3>

<a href="add_products.php">➕ Add Products</a>
<br><br>

<a href="view_products.php">📋 View Products</a>
<br><br>

<a href="view_orders.php">📦 View Orders</a>
<br><br>

<a href="delivery_tracking.php">🚚 Delivery Tracking</a>
<br><br>

<a href="market_analytics.php">📈 Market Analytics</a>
<br><br>

<a href="market_insights.php">💡 Market Insights</a>
<br><br>

<a href="messages.php">💬 Messages</a>
<br><br>
<a href="sales_analytics.php">
📊 Sales Analytics
</a>
<br><br>
<a href="stock_alerts.php">
⚠ Stock Alerts
</a>

<br><br>
<a href="export_sales_report.php">
📊 Download Sales Report
</a>

<br><br>
<a href="sales_charts.php">
📈 Sales Charts
</a>

<br><br>
<a href="profile.php">🏡 Farm Profile</a>
<br><br>

<a href="../auth/logout.php">Logout</a>

</body>
</html>
