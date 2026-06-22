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

<div class="container">

<div class="header">
    <h2>📦 My Orders</h2>
</div>

<div class="navigation">

<a href="dashboard.php">
Dashboard
</a>

<a href="marketplace.php">
Marketplace
</a>

<a href="profile.php">
Profile
</a>

</div>

<div class="card">

<table>

<tr>
<th>Order ID</th>
<th>Product</th>
<th>Farmer</th>
<th>Quantity</th>
<th>Total Amount</th>
<th>Status</th>
<th>Order Date</th>
<th>Actions</th>
</tr>

<?php foreach($orders as $order): ?>

<tr>

<td>
#<?php echo $order["order_id"]; ?>
</td>

<td>
<?php echo htmlspecialchars($order["product_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($order["farmer_name"]); ?>
</td>

<td>
<?php echo $order["quantity_ordered"]; ?>
</td>

<td>
KES <?php echo number_format($order["total_amount"],2); ?>
</td>

<td>

<?php

$status = $order["order_status"];

if($status=="Pending")
{
    echo "🟡 Pending";
}
elseif($status=="Confirmed")
{
    echo "🔵 Confirmed";
}
elseif($status=="Paid")
{
    echo "💳 Paid";
}
elseif($status=="Dispatched")
{
    echo "🚚 Dispatched";
}
elseif($status=="Delivered")
{
    echo "✅ Delivered";
}
elseif($status=="Cancelled")
{
    echo "❌ Cancelled";
}

?>

</td>

<td>
<?php echo $order["order_date"]; ?>
</td>

<td>

<?php if($order["order_status"]=="Pending"): ?>

<a
class="btn btn-danger"
href="cancel_order.php?id=<?php echo $order["order_id"]; ?>"
onclick="return confirm('Cancel this order?')">
Cancel
</a>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<a class="btn" href="dashboard.php">
← Back To Dashboard
</a>

</div>

</body>
</html>
