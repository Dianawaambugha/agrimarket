diana 
<?php

session_start();
require_once "../config/db.php";

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

$id = $_GET["id"];

$stmt = $conn->prepare("
SELECT *
FROM market_prices
WHERE price_id=?
");

$stmt->execute([$id]);

$price = $stmt->fetch();

if(!$price)
{
    die("Price record not found.");
}

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $update = $conn->prepare("
    UPDATE market_prices
    SET
    product_name=?,
    average_price=?,
    market_region=?,
    recorded_date=?,
    source=?
    WHERE price_id=?
    ");

    $update->execute([
        $_POST["product_name"],
        $_POST["average_price"],
        $_POST["market_region"],
        $_POST["recorded_date"],
        $_POST["source"],
        $id
    ]);

    $message = "Updated Successfully";

    $stmt->execute([$id]);
    $price = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Market Price</title>
<link rel="stylesheet"
href="../assets/css/style.css">
</head>

<body>

<h2>Edit Market Price</h2>

<p><?= $message ?></p>

<form method="POST">

<input
type="text"
name="product_name"
value="<?= htmlspecialchars($price['product_name']) ?>"
required>

<br><br>

<input
type="number"
step="0.01"
name="average_price"
value="<?= $price['average_price'] ?>"
required>

<br><br>

<input
type="text"
name="market_region"
value="<?= htmlspecialchars($price['market_region']) ?>"
required>

<br><br>

<input
type="date"
name="recorded_date"
value="<?= $price['recorded_date'] ?>"
required>

<br><br>

<input
type="text"
name="source"
value="<?= htmlspecialchars($price['source']) ?>"
required>

<br><br>

<button type="submit">
Update
</button>

</form>

</body>
</html>