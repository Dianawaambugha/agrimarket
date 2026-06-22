diana 
<?php

session_start();
require_once "../config/db.php";

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $product_name = $_POST["product_name"];
    $average_price = $_POST["average_price"];
    $market_region = $_POST["market_region"];
    $recorded_date = $_POST["recorded_date"];
    $source = $_POST["source"];

    $stmt = $conn->prepare("
    INSERT INTO market_prices
    (
        product_name,
        average_price,
        market_region,
        recorded_date,
        source
    )
    VALUES
    (?,?,?,?,?)
    ");

    $stmt->execute([
        $product_name,
        $average_price,
        $market_region,
        $recorded_date,
        $source
    ]);

    $message = "Market price added successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Market Price</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Add Market Price</h2>

<p><?= $message ?></p>

<form method="POST">

<input
type="text"
name="product_name"
placeholder="Product"
required>

<br><br>

<input
type="number"
step="0.01"
name="average_price"
placeholder="Average Price"
required>

<br><br>

<input
type="text"
name="market_region"
placeholder="Region"
required>

<br><br>

<input
type="date"
name="recorded_date"
required>

<br><br>

<input
type="text"
name="source"
placeholder="Esoko / M-Farm / KAMIS"
required>

<br><br>

<button type="submit">
Save
</button>

</form>

</body>
</html>