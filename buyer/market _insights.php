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

$products = $conn->query("
SELECT DISTINCT product_name
FROM market_prices
ORDER BY product_name
")->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Market Insights</title>
</head>
<body>

<h2>Market Insights</h2>

<table border="1" cellpadding="10">

<tr>

<th>Product</th>
<th>Current Price</th>
<th>Predicted Price</th>
<th>Recommendation</th>

</tr>

<?php

foreach($products as $item)
{
    $product_name =
    $item["product_name"];

    $history = $conn->prepare("
    SELECT average_price
    FROM market_prices
    WHERE product_name=?
    ORDER BY recorded_date DESC
    LIMIT 3
    ");

    $history->execute([
        $product_name
    ]);

    $prices =
    $history->fetchAll(PDO::FETCH_COLUMN);

    if(count($prices) < 3)
    {
        continue;
    }

    $current_price =
    $prices[0];

    $historical_average =
    array_sum($prices) /
    count($prices);

    $listing = $conn->prepare("
    SELECT AVG(price_per_unit)
    FROM products
    WHERE product_name=?
    ");

    $listing->execute([
        $product_name
    ]);

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
        $recommendation =
        "Buy Now";
    }
    else
    {
        $recommendation =
        "Wait";
    }

?>

<tr>

<td><?= htmlspecialchars($product_name) ?></td>

<td>
KES <?= number_format($current_price,2) ?>
</td>

<td>
KES <?= number_format($prediction,2) ?>
</td>

<td>
<?= $recommendation ?>
</td>

</tr>

<?php } ?>

</table>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>