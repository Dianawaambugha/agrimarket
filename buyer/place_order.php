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
    die("Access denied");
}

if(!isset($_GET["id"]))
{
    die("Product not selected.");
}

$product_id = $_GET["id"];

$product_query = $conn->prepare("
SELECT *
FROM products
WHERE product_id=?
");

$product_query->execute([$product_id]);

$product = $product_query->fetch();

if(!$product)
{
    die("Product not found.");
}

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $quantity_ordered = $_POST["quantity"];

    $total_amount =
    $quantity_ordered *
    $product["price_per_unit"];

    $buyer_query = $conn->prepare("
    SELECT buyer_id
    FROM buyers
    WHERE user_id=?
    ");

    $buyer_query->execute([
        $_SESSION["user_id"]
    ]);

    $buyer = $buyer_query->fetch();

    $buyer_id = $buyer["buyer_id"];

    $order = $conn->prepare("
    INSERT INTO orders
    (
        buyer_id,
        product_id,
        quantity_ordered,
        total_amount
    )
    VALUES
    (?,?,?,?)
    ");

    $order->execute([
        $buyer_id,
        $product_id,
        $quantity_ordered,
        $total_amount
    ]);

    $message =
    "Order placed successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Place Order</title>
</head>

<body>

<h2>Place Order</h2>

<p style="color:green;">
<?php echo $message; ?>
</p>

<h3>
<?php echo $product["product_name"]; ?>
</h3>

<p>
Price:
KES <?php echo $product["price_per_unit"]; ?>
per <?php echo $product["unit"]; ?>
</p>

<form method="POST">

<label>
Quantity Required
</label>

<br><br>

<input
type="number"
step="0.1"
name="quantity"
required>

<br><br>

<button type="submit">
Place Order
</button>

</form>

<br>

<a href="marketplace.php">
Back to Marketplace
</a>

</body>
</html>