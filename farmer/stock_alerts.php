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

<style>

body{
    font-family:Arial;
    margin:20px;
}

.warning{
    background:#fff3cd;
    border:1px solid #ffeeba;
    padding:10px;
    margin-bottom:10px;
}

.danger{
    background:#f8d7da;
    border:1px solid #f5c6cb;
    padding:10px;
    margin-bottom:10px;
}

</style>

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
```
