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

if(!isset($_GET["id"]))
{
    die("Invalid order.");
}

$order_id = $_GET["id"];
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

$farmer_id = $farm->fetch()["farmer_id"];

/*
|--------------------------------------------------------------------------
| GET ORDER + OWNERSHIP CHECK
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT

o.*,

p.product_name,
p.price_per_unit,

u.full_name,
u.email,
u.phone_number

FROM orders o

INNER JOIN products p
ON o.product_id = p.product_id

INNER JOIN buyers b
ON o.buyer_id = b.buyer_id

INNER JOIN users u
ON b.user_id = u.user_id

WHERE
o.order_id=?
AND p.farmer_id=?
");

$stmt->execute([
    $order_id,
    $farmer_id
]);

$order = $stmt->fetch();

if(!$order)
{
    die("Unauthorized access.");
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Order Details</title>
</head>

<body>

<h2>Order Details</h2>

<hr>

<h3>Buyer Information</h3>

<p>
Name:
<?php echo htmlspecialchars($order["full_name"]); ?>
</p>

<p>
Email:
<?php echo htmlspecialchars($order["email"]); ?>
</p>

<p>
Phone:
<?php echo htmlspecialchars($order["phone_number"]); ?>
</p>

<hr>

<h3>Product Information</h3>

<p>
Product:
<?php echo htmlspecialchars($order["product_name"]); ?>
</p>

<p>
Unit Price:
KES <?php echo number_format($order["price_per_unit"],2); ?>
</p>

<hr>

<h3>Order Information</h3>

<p>
Order ID:
<?php echo $order["order_id"]; ?>
</p>

<p>
Quantity:
<?php echo $order["quantity_ordered"]; ?>
</p>

<p>
Total Amount:
KES <?php echo number_format($order["total_amount"],2); ?>
</p>

<p>
Status:
<?php echo $order["order_status"]; ?>
</p>

<br>

<a href="delivery_tracking.php?id=<?php echo $order["order_id"]; ?>">
Update Delivery
</a>

<br><br>

<a href="view_orders.php">
Back
</a>

</body>
</html>