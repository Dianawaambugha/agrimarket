<?php

session_start();

require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

$buyer_query = $conn->prepare("
SELECT buyer_id
FROM buyers
WHERE user_id=?
");

$buyer_query->execute([
    $_SESSION["user_id"]
]);

$buyer_id =
$buyer_query->fetch()["buyer_id"];

$stmt = $conn->prepare("
SELECT
orders.*,
products.product_name
FROM orders

JOIN products
ON orders.product_id =
products.product_id

WHERE buyer_id=?
ORDER BY order_id DESC
");

$stmt->execute([$buyer_id]);

$orders = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>
</head>

<body>

<h2>My Orders</h2>

<table border="1" cellpadding="10">

<tr>
<th>Order ID</th>
<th>Product</th>
<th>Quantity</th>
<th>Total</th>
<th>Status</th>
</tr>

<?php foreach($orders as $order): ?>

<tr>

<td><?php echo $order["order_id"]; ?></td>

<td><?php echo $order["product_name"]; ?></td>

<td><?php echo $order["quantity_ordered"]; ?></td>

<td><?php echo $order["total_amount"]; ?></td>

<td><?php echo $order["order_status"]; ?></td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>