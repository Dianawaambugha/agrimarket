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

/* Total Farmers */

$stmt = $conn->query("
SELECT COUNT(*) total
FROM users
WHERE role='Farmer'
");

$total_farmers = $stmt->fetch()["total"];

/* Total Buyers */

$stmt = $conn->query("
SELECT COUNT(*) total
FROM users
WHERE role='Buyer'
");

$total_buyers = $stmt->fetch()["total"];

/* Total Products */

$stmt = $conn->query("
SELECT COUNT(*) total
FROM products
");

$total_products = $stmt->fetch()["total"];

/* Total Orders */

$stmt = $conn->query("
SELECT COUNT(*) total
FROM orders
");

$total_orders = $stmt->fetch()["total"];

/* Revenue */

$stmt = $conn->query("
SELECT IFNULL(SUM(total_amount),0) revenue
FROM orders
WHERE order_status='Delivered'
");

$total_revenue = $stmt->fetch()["revenue"];

?>

<!DOCTYPE html>
<html>
<head>
<title>Reports Dashboard</title>
</head>

<body>

<h2>System Reports Dashboard</h2>

<hr>

<h3>Users</h3>

<p>
Total Farmers:
<b><?php echo $total_farmers; ?></b>
</p>

<p>
Total Buyers:
<b><?php echo $total_buyers; ?></b>
</p>

<hr>

<h3>Products</h3>

<p>
Total Products:
<b><?php echo $total_products; ?></b>
</p>

<hr>

<h3>Orders</h3>

<p>
Total Orders:
<b><?php echo $total_orders; ?></b>
</p>

<hr>

<h3>Revenue</h3>

<p>
KES
<b><?php echo number_format($total_revenue,2); ?></b>
</p>

<hr>

<a href="dashboard.php">
Back to Dashboard
</a>

</body>
</html>