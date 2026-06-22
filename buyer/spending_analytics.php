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

$stmt->execute([
    $_SESSION["user_id"]
]);

$buyer_id = $stmt->fetchColumn();
$analytics_stmt = $conn->prepare("
SELECT
DATE_FORMAT(order_date,'%Y-%m') AS month,
SUM(total_amount) AS total
FROM orders
WHERE buyer_id=?
GROUP BY DATE_FORMAT(order_date,'%Y-%m')
ORDER BY month DESC
");

$analytics_stmt->execute([$buyer_id]);

$analytics = $analytics_stmt->fetchAll();

if(!$buyer_id)
{
    die("Buyer profile not found.");
}

/*
|--------------------------------------------------------------------------
| TOTAL ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
");

$stmt->execute([$buyer_id]);

$total_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| TOTAL SPENT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT IFNULL(SUM(total_amount),0)
FROM orders
WHERE buyer_id=?
");

$stmt->execute([$buyer_id]);

$total_spent = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| AVERAGE ORDER VALUE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT IFNULL(AVG(total_amount),0)
FROM orders
WHERE buyer_id=?
");

$stmt->execute([$buyer_id]);

$average_order = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| DELIVERED ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
AND order_status='Delivered'
");

$stmt->execute([$buyer_id]);

$delivered_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| PENDING ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
AND order_status='Pending'
");

$stmt->execute([$buyer_id]);

$pending_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| CANCELLED ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM orders
WHERE buyer_id=?
AND order_status='Cancelled'
");

$stmt->execute([$buyer_id]);

$cancelled_orders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| FAVOURITE PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
p.product_name,
COUNT(*) total_orders

FROM orders o

INNER JOIN products p
ON o.product_id=p.product_id

WHERE o.buyer_id=?

GROUP BY p.product_id

ORDER BY total_orders DESC

LIMIT 1
");

$stmt->execute([$buyer_id]);

$favourite_product = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| MOST EXPENSIVE PURCHASE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
p.product_name,
o.total_amount

FROM orders o

INNER JOIN products p
ON o.product_id=p.product_id

WHERE o.buyer_id=?

ORDER BY o.total_amount DESC

LIMIT 1
");

$stmt->execute([$buyer_id]);

$expensive_order = $stmt->fetch();

?>

<!DOCTYPE html>

<html>

<head>

<title>Spending Analytics</title>
<link rel="stylesheet"
href="../assets/css/style.css">


</head>

<body>

<div class="container">

<div class="header">
    <h2>📊 Spending Analytics</h2>
</div>

<div class="stats-grid">

<div class="stat-card">
<h3>Total Spent</h3>
<p>KES <?php echo number_format($total_spent,2); ?></p>
</div>

<div class="stat-card">
<h3>Total Orders</h3>
<p><?php echo $total_orders; ?></p>
</div>

<div class="stat-card">
<h3>Delivered Orders</h3>
<p><?php echo $delivered_orders; ?></p>
</div>

</div>

<div class="card">

<h3>Spending History</h3>

<table>

<tr>
<th>Month</th>
<th>Amount Spent</th>
</tr>

<?php foreach($analytics as $row): ?>

<tr>

<td>
<?php echo $row["month"]; ?>
</td>

<td>
KES <?php echo number_format($row["total"],2); ?>
</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<a class="btn" href="dashboard.php">
Back To Dashboard
</a>

</div>

</body>
</html>
