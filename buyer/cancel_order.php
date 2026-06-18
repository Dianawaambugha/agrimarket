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

if(!isset($_GET["id"]))
{
    die("Invalid request.");
}

$order_id = (int)$_GET["id"];

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

$buyer_id = $buyer_query->fetchColumn();

if(!$buyer_id)
{
    die("Buyer not found.");
}

/*
|--------------------------------------------------------------------------
| VERIFY ORDER BELONGS TO BUYER
|--------------------------------------------------------------------------
*/

$order = $conn->prepare("
SELECT *
FROM orders
WHERE order_id=?
AND buyer_id=?
");

$order->execute([
    $order_id,
    $buyer_id
]);

$order_data = $order->fetch();

if(!$order_data)
{
    die("Order not found.");
}

/*
|--------------------------------------------------------------------------
| ONLY PENDING ORDERS CAN BE CANCELLED
|--------------------------------------------------------------------------
*/

if($order_data["order_status"] != "Pending")
{
    die("Only pending orders can be cancelled.");
}

/*
|--------------------------------------------------------------------------
| CANCEL ORDER
|--------------------------------------------------------------------------
*/

$update = $conn->prepare("
UPDATE orders
SET order_status='Cancelled'
WHERE order_id=?
");

$update->execute([$order_id]);

header("Location: my_orders.php");
exit();

?>
```
