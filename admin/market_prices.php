diana 
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

$stmt = $conn->prepare("
SELECT *
FROM market_prices
ORDER BY recorded_date DESC
");

$stmt->execute();

$prices = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Market Prices</title>
<link rel="stylesheet"
href="../assets/css/style.css">
</head>

<body>

<h2>Market Prices Management</h2>

<a href="add_market_price.php">
Add New Market Price
</a>

<br><br>

<table border="1" cellpadding="10">

<tr>
<th>ID</th>
<th>Product</th>
<th>Price</th>
<th>Region</th>
<th>Date</th>
<th>Source</th>
<th>Action</th>
</tr>

<?php foreach($prices as $price): ?>

<tr>

<td><?= $price["price_id"] ?></td>
<td><?= htmlspecialchars($price["product_name"]) ?></td>
<td>KES <?= $price["average_price"] ?></td>
<td><?= htmlspecialchars($price["market_region"]) ?></td>
<td><?= $price["recorded_date"] ?></td>
<td><?= htmlspecialchars($price["source"]) ?></td>

<td>

<a href="edit_market_price.php?id=<?= $price['price_id'] ?>">
Edit
</a>

|

<a
href="delete_market_price.php?id=<?= $price['price_id'] ?>"
onclick="return confirm('Delete this record?')"
>
Delete
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>