<?php

session_start();

require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

$stmt = $conn->prepare("
SELECT *
FROM products
WHERE status='Available'
ORDER BY product_id DESC
");

$stmt->execute();

$products = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Marketplace</title>
</head>

<body>

<h2>Marketplace</h2>

<table border="1" cellpadding="10">

<tr>
<th>Product</th>
<th>Category</th>
<th>Quantity</th>
<th>Unit</th>
<th>Price</th>
<th>Action</th>
</tr>

<?php foreach($products as $product): ?>

<tr>

<td><?php echo $product["product_name"]; ?></td>

<td><?php echo $product["category"]; ?></td>

<td><?php echo $product["quantity"]; ?></td>

<td><?php echo $product["unit"]; ?></td>

<td><?php echo $product["price_per_unit"]; ?></td>

<td>

<a href="place_order.php?id=<?php echo $product['product_id']; ?>">
Order
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>