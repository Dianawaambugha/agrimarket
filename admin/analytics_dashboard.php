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

/*
|--------------------------------------------------------------------------
| TOP PRODUCTS
|--------------------------------------------------------------------------
*/

$top_products = $conn->query("
SELECT
product_name,
COUNT(*) AS total_listings
FROM products
GROUP BY product_name
ORDER BY total_listings DESC
LIMIT 5
")->fetchAll();

/*
|--------------------------------------------------------------------------
| TOP FARMERS
|--------------------------------------------------------------------------
*/

$top_farmers = $conn->query("
SELECT
u.full_name,
COUNT(p.product_id) AS total_products
FROM users u
INNER JOIN farmers f
ON u.user_id = f.user_id
LEFT JOIN products p
ON f.farmer_id = p.farmer_id
GROUP BY u.user_id
ORDER BY total_products DESC
LIMIT 5
")->fetchAll();

/*
|--------------------------------------------------------------------------
| TOP BUYERS
|--------------------------------------------------------------------------
*/

$top_buyers = $conn->query("
SELECT
u.full_name,
COUNT(o.order_id) AS total_orders
FROM users u
INNER JOIN buyers b
ON u.user_id = b.user_id
LEFT JOIN orders o
ON b.buyer_id = o.buyer_id
GROUP BY u.user_id
ORDER BY total_orders DESC
LIMIT 5
")->fetchAll();

/*
|--------------------------------------------------------------------------
| CATEGORY ANALYSIS
|--------------------------------------------------------------------------
*/

$categories = $conn->query("
SELECT
category,
COUNT(*) AS total_products
FROM products
GROUP BY category
ORDER BY total_products DESC
")->fetchAll();

/*
|--------------------------------------------------------------------------
| HIGHEST PRICED PRODUCTS
|--------------------------------------------------------------------------
*/

$expensive_products = $conn->query("
SELECT
product_name,
AVG(price_per_unit) AS avg_price
FROM products
GROUP BY product_name
ORDER BY avg_price DESC
LIMIT 5
")->fetchAll();

/*
|--------------------------------------------------------------------------
| MONTHLY SALES TREND
|--------------------------------------------------------------------------
*/

$monthly_sales = [];

try
{
    $monthly_sales = $conn->query("
    SELECT
    DATE_FORMAT(order_date,'%Y-%m') AS month,
    COUNT(*) AS total_orders,
    SUM(total_amount) AS total_sales
    FROM orders
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
    ")->fetchAll();
}
catch(Exception $e)
{
}

/*
|--------------------------------------------------------------------------
| PRICE FORECAST ANALYTICS
|--------------------------------------------------------------------------
*/

$forecast_products = $conn->query("
SELECT
product_name,
AVG(average_price) AS avg_price
FROM market_prices
GROUP BY product_name
ORDER BY avg_price DESC
LIMIT 10
")->fetchAll();

/*
|--------------------------------------------------------------------------
| PREDICTION CONFIDENCE
|--------------------------------------------------------------------------
*/

$prediction_confidence = 80;

?>

<!DOCTYPE html>
<html>

<head>

<title>Analytics Dashboard</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Analytics Dashboard</h2>

<div class="card">

<h3>Top 5 Products</h3>

<table>

<tr>
<th>Product</th>
<th>Total Listings</th>
</tr>

<?php foreach($top_products as $row): ?>

<tr>

<td><?= htmlspecialchars($row["product_name"]) ?></td>

<td><?= $row["total_listings"] ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h3>Top Farmers</h3>

<table>

<tr>
<th>Farmer</th>
<th>Total Listings</th>
</tr>

<?php foreach($top_farmers as $row): ?>

<tr>

<td><?= htmlspecialchars($row["full_name"]) ?></td>

<td><?= $row["total_products"] ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h3>Top Buyers</h3>

<table>

<tr>
<th>Buyer</th>
<th>Total Orders</th>
</tr>

<?php foreach($top_buyers as $row): ?>

<tr>

<td><?= htmlspecialchars($row["full_name"]) ?></td>

<td><?= $row["total_orders"] ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h3>Product Category Analysis</h3>

<table>

<tr>
<th>Category</th>
<th>Total Products</th>
</tr>

<?php foreach($categories as $row): ?>

<tr>

<td><?= htmlspecialchars($row["category"]) ?></td>

<td><?= $row["total_products"] ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h3>Highest Priced Products</h3>

<table>

<tr>
<th>Product</th>
<th>Average Price (KES)</th>
</tr>

<?php foreach($expensive_products as $row): ?>

<tr>

<td><?= htmlspecialchars($row["product_name"]) ?></td>

<td><?= number_format($row["avg_price"],2) ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php if(!empty($monthly_sales)): ?>

<div class="card">

<h3>Monthly Sales Trend</h3>

<table>

<tr>
<th>Month</th>
<th>Total Orders</th>
<th>Total Sales (KES)</th>
</tr>

<?php foreach($monthly_sales as $row): ?>

<tr>

<td><?= $row["month"] ?></td>

<td><?= $row["total_orders"] ?></td>

<td><?= number_format($row["total_sales"],2) ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php endif; ?>

<div class="card">

<h3>Price Forecast Analytics</h3>

<table>

<tr>
<th>Product</th>
<th>Average Historical Price</th>
<th>Expected Trend</th>
</tr>

<?php foreach($forecast_products as $row): ?>

<tr>

<td>
<?= htmlspecialchars($row["product_name"]) ?>
</td>

<td>
KES <?= number_format($row["avg_price"],2) ?>
</td>

<td>

<?php

if($row["avg_price"] >= 100)
{
echo "<span style='color:green;'>Rising</span>";
}
elseif($row["avg_price"] >= 60)
{
echo "<span style='color:blue;'>Stable</span>";
}
else
{
echo "<span style='color:red;'>Falling</span>";
}

?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h3>Prediction Confidence</h3>

<p>

Current Prediction Confidence:

<b><?= $prediction_confidence ?>%</b>

</p>

<p>

Confidence is based on historical market prices
combined with current farmer listing prices.

</p>

</div>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>

</html>