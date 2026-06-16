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
    die("Access denied");
}

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| GET FARMER ID
|--------------------------------------------------------------------------
*/

$farm = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$farm->execute([$user_id]);

$farmer = $farm->fetch();

if(!$farmer)
{
    die("Farmer profile not found.");
}

$farmer_id = $farmer["farmer_id"];

/*
|--------------------------------------------------------------------------
| GET ORDERS FOR THIS FARMER ONLY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
o.order_id,
o.quantity_ordered,
o.total_amount,
o.order_status,

p.product_name,

u.full_name AS buyer_name

FROM orders o

INNER JOIN products p
ON o.product_id = p.product_id

INNER JOIN buyers b
ON o.buyer_id = b.buyer_id

INNER JOIN users u
ON b.user_id = u.user_id

WHERE p.farmer_id=?

ORDER BY o.order_id DESC
");

$stmt->execute([$farmer_id]);

$orders = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Incoming Orders</title>
</head>

<body>

<h2>Incoming Orders</h2>

<table border="1" cellpadding="10">

<tr>
<th>Order ID</th>
<th>Buyer</th>
<th>Product</th>
<th>Quantity</th>
<th>Amount</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php foreach($orders as $order): ?>

<tr>

<td><?php echo $order["order_id"]; ?></td>

<td><?php echo htmlspecialchars($order["buyer_name"]); ?></td>

<td><?php echo htmlspecialchars($order["product_name"]); ?></td>

<td><?php echo $order["quantity_ordered"]; ?></td>

<td>KES <?php echo number_format($order["total_amount"],2); ?></td>

<td><?php echo $order["order_status"]; ?></td>

<td>

<a href="order_details.php?id=<?php echo $order["order_id"]; ?>">
View
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

<br><br>

<a href="dashboard.php">
Back to Dashboard
</a>

</body>
</html>