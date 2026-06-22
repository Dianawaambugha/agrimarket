diana 
<?php

require_once "auth_check.php";

/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*)
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
SELECT COUNT(*)
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
SELECT COUNT(*)
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
SELECT COUNT(*)
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
SELECT COUNT(*)
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
SELECT COUNT(*)
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

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="header">
🌾 Agri Market Connect - Farmer Dashboard
</div>

<div class="container">

<h2>
Welcome,
<?php echo htmlspecialchars($_SESSION["name"]); ?>
</h2>

<br>

<div class="cards">

<div class="card">
<h3>Total Products</h3>
<p><?php echo $total_products; ?></p>
</div>

<div class="card">
<h3>Available Products</h3>
<p><?php echo $available_products; ?></p>
</div>

<div class="card">
<h3>Total Orders</h3>
<p><?php echo $total_orders; ?></p>
</div>

<div class="card">
<h3>Revenue</h3>
<p>KES <?php echo number_format($total_revenue,0); ?></p>
</div>

</div>

<div class="cards">

<div class="card">
<h3>Pending Orders</h3>
<p><?php echo $pending_orders; ?></p>
</div>

<div class="card">
<h3>Delivered Orders</h3>
<p><?php echo $delivered_orders; ?></p>
</div>

<div class="card">
<h3>Sold Out Products</h3>
<p><?php echo $sold_products; ?></p>
</div>

<div class="card">
<h3>Average Price</h3>
<p>KES <?php echo number_format($average_price,2); ?></p>
</div>

</div>

<div class="card">

<h3>Performance Summary</h3>

<br>

<p>

<strong>Best Selling Product:</strong>

<?php

echo $best_product
? htmlspecialchars($best_product["product_name"])
: "N/A";

?>

</p>

<br>

<p>

<strong>Total Revenue:</strong>

KES <?php echo number_format($total_revenue,2); ?>

</p>

</div>

<br>

<h2>Quick Actions</h2>

<div class="menu">

<a href="add_products.php">
➕ Add Products
</a>

<a href="view_products.php">
📦 View Products
</a>

<a href="view_orders.php">
🛒 View Orders
</a>

<a href="delivery_tracking.php">
🚚 Delivery Tracking
</a>

<a href="market_analytics.php">
📈 Market Analytics
</a>

<a href="market_insights.php">
💡 Market Insights
</a>

<a href="sales_analytics.php">
📊 Sales Analytics
</a>

<a href="sales_charts.php">
📈 Sales Charts
</a>

<a href="stock_alerts.php">
⚠ Stock Alerts
</a>

<a href="export_sales_report.php">
📄 Download Report
</a>

<a href="profile.php">
🏡 Farm Profile
</a>

<a href="messages.php">
💬 Messages
</a>

<a href="../auth/logout.php">
🚪 Logout
</a>

</div>

</div>

</body>
</html>
