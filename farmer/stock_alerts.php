diana 
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
|--------------------------------------------------------------------------
| GET FARMER ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$stmt->execute([$_SESSION["user_id"]]);

$farmer_id = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| LOW STOCK PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
product_name,
quantity,
unit,
status
FROM products
WHERE farmer_id=?
AND quantity <= 10
ORDER BY quantity ASC
");

$stmt->execute([$farmer_id]);

$products = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<title>Stock Alerts</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Stock Alerts</h2>

<?php if(count($products)==0): ?>

<p>No low-stock products found.</p>

<?php endif; ?>

<?php foreach($products as $product): ?>

<?php if($product["quantity"] <= 0): ?>

<div class="danger">

❌

<b>
<?= htmlspecialchars($product["product_name"]) ?>
</b>

is OUT OF STOCK

</div>

<?php else: ?>

<div class="warning">

⚠

<b>
<?= htmlspecialchars($product["product_name"]) ?>
</b>

has only

<b>
<?= $product["quantity"] ?>
<?= $product["unit"] ?>
</b>

remaining.

</div>

<?php endif; ?>

<?php endforeach; ?>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>
