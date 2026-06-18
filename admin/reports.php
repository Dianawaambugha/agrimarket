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

/* USERS */

$total_farmers = $conn->query("
SELECT COUNT(*)
FROM users
WHERE role='Farmer'
")->fetchColumn();

$total_buyers = $conn->query("
SELECT COUNT(*)
FROM users
WHERE role='Buyer'
")->fetchColumn();

$total_admins = $conn->query("
SELECT COUNT(*)
FROM users
WHERE role='Admin'
")->fetchColumn();

$active_farmers = $conn->query("
SELECT COUNT(*)
FROM users
WHERE role='Farmer'
AND status='Active'
")->fetchColumn();

$pending_farmers = $conn->query("
SELECT COUNT(*)
FROM users
WHERE role='Farmer'
AND status='Inactive'
")->fetchColumn();

/* PRODUCTS */

$total_products = $conn->query("
SELECT COUNT(*)
FROM products
")->fetchColumn();

$available_products = $conn->query("
SELECT COUNT(*)
FROM products
WHERE status='Available'
")->fetchColumn();

$sold_products = $conn->query("
SELECT COUNT(*)
FROM products
WHERE status='Sold Out'
")->fetchColumn();

/* ORDERS */

$total_orders = $conn->query("
SELECT COUNT(*)
FROM orders
")->fetchColumn();

$pending_orders = $conn->query("
SELECT COUNT(*)
FROM orders
WHERE order_status='Pending'
")->fetchColumn();

$delivered_orders = $conn->query("
SELECT COUNT(*)
FROM orders
WHERE order_status='Delivered'
")->fetchColumn();

$cancelled_orders = $conn->query("
SELECT COUNT(*)
FROM orders
WHERE order_status='Cancelled'
")->fetchColumn();

/* REVENUE */

$total_revenue = $conn->query("
SELECT IFNULL(SUM(total_amount),0)
FROM orders
WHERE order_status IN ('Paid','Delivered')
")->fetchColumn();

/* TOP PRODUCT */

$top_product = $conn->query("
SELECT
p.product_name,
SUM(o.quantity_ordered) total_sold
FROM orders o
JOIN products p
ON o.product_id=p.product_id
GROUP BY p.product_id
ORDER BY total_sold DESC
LIMIT 1
")->fetch();

/* TOP FARMER */

$top_farmer = $conn->query("
SELECT
u.full_name,
COUNT(p.product_id) total_products
FROM products p
JOIN farmers f
ON p.farmer_id=f.farmer_id
JOIN users u
ON f.user_id=u.user_id
GROUP BY f.farmer_id
ORDER BY total_products DESC
LIMIT 1
")->fetch();

/* MARKET PRICE INFO */

$latest_price = $conn->query("
SELECT
product_name,
recorded_date
FROM market_prices
ORDER BY recorded_date DESC
LIMIT 1
")->fetch();

/* AUDIT LOGS */

$total_logs = $conn->query("
SELECT COUNT(*)
FROM audit_logs
")->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>

<title>System Reports</title>

<style>

body{
font-family:Arial;
margin:20px;
}

.section{
border:1px solid #ddd;
padding:15px;
margin-bottom:15px;
border-radius:8px;
background:#fafafa;
}

h2{
color:#2c3e50;
}

h3{
color:#27ae60;
}

</style>

</head>

<body>

<h2>System Reports Dashboard</h2>

<div class="section">

<h3>User Reports</h3>

<p>Total Farmers: <b><?= $total_farmers ?></b></p>

<p>Total Buyers: <b><?= $total_buyers ?></b></p>

<p>Total Admins: <b><?= $total_admins ?></b></p>

<p>Active Farmers: <b><?= $active_farmers ?></b></p>

<p>Pending Farmer Approvals: <b><?= $pending_farmers ?></b></p>

</div>

<div class="section">

<h3>Product Reports</h3>

<p>Total Products: <b><?= $total_products ?></b></p>

<p>Available Products: <b><?= $available_products ?></b></p>

<p>Sold Out Products: <b><?= $sold_products ?></b></p>

</div>

<div class="section">

<h3>Order Reports</h3>

<p>Total Orders: <b><?= $total_orders ?></b></p>

<p>Pending Orders: <b><?= $pending_orders ?></b></p>

<p>Delivered Orders: <b><?= $delivered_orders ?></b></p>

<p>Cancelled Orders: <b><?= $cancelled_orders ?></b></p>

</div>

<div class="section">

<h3>Revenue Report</h3>

<p>KES <b><?= number_format($total_revenue,2) ?></b></p>

</div>

<div class="section">

<h3>Performance Report</h3>

<p>
Top Selling Product:
<b>
<?= $top_product ? htmlspecialchars($top_product["product_name"]) : "N/A" ?>
</b>
</p>

<p>
Top Farmer:
<b>
<?= $top_farmer ? htmlspecialchars($top_farmer["full_name"]) : "N/A" ?>
</b>
</p>

</div>

<div class="section">

<h3>Market Price Report</h3>

<p>
Latest Product Price Update:
<b>
<?= $latest_price ? htmlspecialchars($latest_price["product_name"]) : "N/A" ?>
</b>
</p>

<p>
Date:
<b>
<?= $latest_price ? $latest_price["recorded_date"] : "N/A" ?>
</b>
</p>

</div>

<div class="section">

<h3>Audit Report</h3>

<p>Total Audit Logs: <b><?= $total_logs ?></b></p>

</div>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>