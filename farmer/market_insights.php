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

$user_id = $_SESSION["user_id"];

$farmer = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$farmer->execute([$user_id]);

$farmer_id = $farmer->fetchColumn();

$products = $conn->prepare("
SELECT DISTINCT product_name
FROM products
WHERE farmer_id=?
");

$products->execute([$farmer_id]);

$products = $products->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Market Insights</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>
<body>

<h2>Market Insights</h2>

<table border="1" cellpadding="10">

<tr>
<th>Product</th>
<th>My Price</th>
<th>Market Average</th>
<th>Predicted Price</th>
<th>Trend</th>
</tr>

<?php

foreach($products as $item)
{
    $product_name = $item["product_name"];

    $myPrice = $conn->prepare("
    SELECT AVG(price_per_unit)
    FROM products
    WHERE farmer_id=?
    AND product_name=?
    ");

    $myPrice->execute([
        $farmer_id,
        $product_name
    ]);

    $my_price = $myPrice->fetchColumn();

    $history = $conn->prepare("
    SELECT average_price
    FROM market_prices
    WHERE product_name=?
    ORDER BY recorded_date DESC
    LIMIT 3
    ");

    $history->execute([$product_name]);

    $prices = $history->fetchAll(PDO::FETCH_COLUMN);

    if(count($prices) < 3)
    {
        continue;
    }

    $current_price = $prices[0];

    $historical_average =
    array_sum($prices)/count($prices);

    $listing = $conn->prepare("
    SELECT AVG(price_per_unit)
    FROM products
    WHERE product_name=?
    ");

    $listing->execute([$product_name]);

    $market_average =
    $listing->fetchColumn();

    if(!$market_average)
    {
        $market_average =
        $historical_average;
    }

    $prediction =
    (
        $historical_average +
        $market_average
    ) / 2;

    if($prediction > $current_price)
    {
        $trend = "↑ Rising";
    }
    elseif($prediction < $current_price)
    {
        $trend = "↓ Falling";
    }
    else
    {
        $trend = "→ Stable";
    }

?>

<tr>

<td><?= htmlspecialchars($product_name) ?></td>

<td>KES <?= number_format($my_price,2) ?></td>

<td>KES <?= number_format($market_average,2) ?></td>

<td>KES <?= number_format($prediction,2) ?></td>

<td><?= $trend ?></td>

</tr>

<?php } ?>

</table>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>