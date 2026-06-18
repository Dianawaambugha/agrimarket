```php
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

$buyer_query = $conn->prepare("
SELECT buyer_id
FROM buyers
WHERE user_id=?
");

$buyer_query->execute([
    $_SESSION["user_id"]
]);

$buyer = $buyer_query->fetch();

if(!$buyer)
{
    die("Buyer profile not found.");
}

$buyer_id = $buyer["buyer_id"];

/*
|--------------------------------------------------------------------------
| GET BUYER ORDERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
o.order_id,
o.quantity_ordered,
o.total_amount,
o.order_status,
o.order_date,

p.product_name,

u.full_name AS farmer_name

FROM orders o

INNER JOIN products p
ON o.product_id = p.product_id

INNER JOIN farmers f
ON p.farmer_id = f.farmer_id

INNER JOIN users u
ON f.user_id = u.user_id

WHERE o.buyer_id=?

ORDER BY o.order_id DESC
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
<th>Farmer</th>
<th>Quantity</th>
<th>Total Amount</th>
<th>Status</th>
<th>Order Date</th>
<th>Action</th>
</tr>

<?php foreach($orders as $order): ?>

<tr>

<td><?php echo $order["order_id"]; ?></td>

<td><?php echo htmlspecialchars($order["product_name"]); ?></td>

<td><?php echo htmlspecialchars($order["farmer_name"]); ?></td>

<td><?php echo $order["quantity_ordered"]; ?></td>

<td>
KES <?php echo number_format($order["total_amount"],2); ?>
</td>

<td><?php echo $order["order_status"]; ?></td>

<td><?php echo $order["order_date"]; ?></td>

<td>

<?php if($order["order_status"]=="Pending"): ?>

<a href="cancel_order.php?id=<?php echo $order["order_id"]; ?>">
Cancel Order
</a>

<?php else: ?>

-

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</table>

<br><br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>
```
