diana 
<?php

require_once "auth_check.php";

$stmt = $conn->prepare("
SELECT *
FROM products
WHERE farmer_id=?
ORDER BY product_id DESC
");

$stmt->execute([$farmer_id]);

$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>My Products</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>My Products</h2>

<table border="1" cellpadding="10">

<tr>
<th>Image</th>
<th>Product</th>
<th>Category</th>
<th>Quantity</th>
<th>Price</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php foreach($products as $product): ?>

<tr>

<td>

<?php if($product["image1"]): ?>

<img
src="../assets/product_images/<?php echo $product["image1"]; ?>"
width="80">

<?php endif; ?>

</td>

<td><?php echo htmlspecialchars($product["product_name"]); ?></td>

<td><?php echo htmlspecialchars($product["category"]); ?></td>

<td><?php echo $product["quantity"]; ?></td>

<td><?php echo $product["price_per_unit"]; ?></td>

<td><?php echo $product["status"]; ?></td>

<td>

<a href="edit_products.php?id=<?php echo $product["product_id"]; ?>">
Edit
</a>

|

<a
href="delete_products.php?id=<?php echo $product["product_id"]; ?>"
onclick="return confirm('Delete Product?')"
>
Delete
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