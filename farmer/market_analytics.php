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
    die("Access Denied");
}

/*
Average prices by product
*/

$avgPrices = $conn->query("
SELECT
product_name,
ROUND(AVG(price_per_unit),2) AS avg_price,
COUNT(*) AS total_listings
FROM products
GROUP BY product_name
ORDER BY avg_price DESC
");

$avgPrices = $avgPrices->fetchAll();

/*
Highest priced products
*/

$highest = $conn->query("
SELECT
product_name,
price_per_unit
FROM products
ORDER BY price_per_unit DESC
LIMIT 5
");

$highest = $highest->fetchAll();

/*
Lowest priced products
*/

$lowest = $conn->query("
SELECT
product_name,
price_per_unit
FROM products
ORDER BY price_per_unit ASC
LIMIT 5
");

$lowest = $lowest->fetchAll();

/*
Recent Listings
*/

$recent = $conn->query("
SELECT
product_name,
price_per_unit,
created_at
FROM products
ORDER BY created_at DESC
LIMIT 10
");

$recent = $recent->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Market Analytics</title>
</head>

<body>

<h2>Market Analytics Dashboard</h2>

<hr>

<h3>Average Market Prices</h3>

<table border="1" cellpadding="10">

<tr>
<th>Product</th>
<th>Average Price</th>
<th>Total Listings</th>
</tr>

<?php foreach($avgPrices as $row): ?>

<tr>
<td><?php echo htmlspecialchars($row["product_name"]); ?></td>
<td>KES <?php echo $row["avg_price"]; ?></td>
<td><?php echo $row["total_listings"]; ?></td>
</tr>

<?php endforeach; ?>

</table>

<hr>

<h3>Highest Priced Products</h3>

<table border="1" cellpadding="10">

<tr>
<th>Product</th>
<th>Price</th>
</tr>

<?php foreach($highest as $row): ?>

<tr>
<td><?php echo htmlspecialchars($row["product_name"]); ?></td>
<td>KES <?php echo $row["price_per_unit"]; ?></td>
</tr>

<?php endforeach; ?>

</table>

<hr>

<h3>Lowest Priced Products</h3>

<table border="1" cellpadding="10">

<tr>
<th>Product</th>
<th>Price</th>
</tr>

<?php foreach($lowest as $row): ?>

<tr>
<td><?php echo htmlspecialchars($row["product_name"]); ?></td>
<td>KES <?php echo $row["price_per_unit"]; ?></td>
</tr>

<?php endforeach; ?>

</table>

<hr>

<h3>Latest Market Listings</h3>

<table border="1" cellpadding="10">

<tr>
<th>Product</th>
<th>Price</th>
<th>Date Listed</th>
</tr>

<?php foreach($recent as $row): ?>

<tr>
<td><?php echo htmlspecialchars($row["product_name"]); ?></td>
<td>KES <?php echo $row["price_per_unit"]; ?></td>
<td><?php echo $row["created_at"]; ?></td>
</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back to Dashboard
</a>

</body>
</html>