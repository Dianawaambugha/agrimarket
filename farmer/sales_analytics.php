diana
<?php

session_start();
require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Farmer")
{
    die("Access Denied");
}

/*
|--------------------------------------------------------------------------
| GET FARMER ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$stmt->execute([$_SESSION["user_id"]]);

$farmer_id = $stmt->fetchColumn();

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
| TOP PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
p.product_name,
SUM(o.quantity_ordered) total_sold,
SUM(o.total_amount) total_revenue
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
GROUP BY p.product_id
ORDER BY total_sold DESC
");

$stmt->execute([$farmer_id]);

$products = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| MONTHLY SALES
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
DATE_FORMAT(o.order_date,'%Y-%m') AS month,
COUNT(*) total_orders,
SUM(o.total_amount) total_sales
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
GROUP BY month
ORDER BY month DESC
");

$stmt->execute([$farmer_id]);

$monthly_sales = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<title>Sales Analytics</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Farmer Sales Analytics</h2>

<div class="card">

<h3>Sales Summary</h3>

<p>Total Revenue:
<b>KES <?= number_format($total_revenue,2) ?></b></p>

<p>Total Orders:
<b><?= $total_orders ?></b></p>

<p>Delivered Orders:
<b><?= $delivered_orders ?></b></p>

<p>Pending Orders:
<b><?= $pending_orders ?></b></p>

<p>Best Selling Product:
<b>
<?= $best_product ? htmlspecialchars($best_product["product_name"]) : "N/A" ?>
</b>
</p>

</div>

<div class="card">

<h3>Top Products</h3>

<table>

<tr>
<th>Product</th>
<th>Units Sold</th>
<th>Revenue</th>
</tr>

<?php foreach($products as $row): ?>

<tr>

<td><?= htmlspecialchars($row["product_name"]) ?></td>

<td><?= $row["total_sold"] ?></td>

<td>KES <?= number_format($row["total_revenue"],2) ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="card">

<h3>Monthly Sales</h3>

<table>

<tr>
<th>Month</th>
<th>Total Orders</th>
<th>Total Sales</th>
</tr>

<?php foreach($monthly_sales as $row): ?>

<tr>

<td><?= $row["month"] ?></td>

<td><?= $row["total_orders"] ?></td>

<td>KES <?= number_format($row["total_sales"],2) ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>
