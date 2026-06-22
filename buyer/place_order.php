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
| GET ORDERS
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

<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<div class="container">

<div class="header">

<h2>📦 My Orders</h2>

</div>

<div class="navigation">

<a href="dashboard.php">Dashboard</a>

<a href="marketplace.php">Marketplace</a>

<a href="profile.php">Profile</a>

<a href="messages.php">Messages</a>

</div>

<?php if(empty($orders)): ?>

<div class="card">

<p>
You have not placed any orders yet.
</p>

<a class="btn" href="marketplace.php">
Browse Products
</a>

</div>

<?php endif; ?>

<?php foreach($orders as $order): ?>

<div class="order-card">

<div class="order-top">

<h3>
<?php echo htmlspecialchars($order["product_name"]); ?>
</h3>

<?php

$status = strtolower($order["order_status"]);

?>

<span class="badge <?php echo $status; ?>">

<?php echo htmlspecialchars($order["order_status"]); ?>

</span>

</div>

<p>

<strong>Farmer:</strong>

<?php echo htmlspecialchars($order["farmer_name"]); ?>

</p>

<p>

<strong>Quantity:</strong>

<?php echo $order["quantity_ordered"]; ?>

</p>

<p>

<strong>Total Amount:</strong>

KES <?php echo number_format($order["total_amount"],2); ?>

</p>

<p>

<strong>Order Date:</strong>

<?php echo $order["order_date"]; ?>

</p>

<?php if($order["order_status"]=="Pending"): ?>

<a
class="btn btn-danger"
href="cancel_order.php?id=<?php echo $order["order_id"]; ?>"
onclick="return confirm('Are you sure you want to cancel this order?')">

Cancel Order

</a>

<?php endif; ?>

</div>

<?php endforeach; ?>

<br>

<a class="btn" href="dashboard.php">
← Back To Dashboard
</a>

</div>

</body>
</html>