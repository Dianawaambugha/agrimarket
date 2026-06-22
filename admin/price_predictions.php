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

$products = $conn->query("
SELECT DISTINCT product_name
FROM market_prices
ORDER BY product_name
")->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<title>Price Predictions</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Market Price Predictions</h2>

<table>

<tr>

<th>Product</th>
<th>Historical Price</th>
<th>Farmer Market Avg</th>
<th>Predicted Price</th>
<th>Trend</th>
<th>Confidence</th>

</tr>

<?php

foreach($products as $item)
{
    $product_name = $item["product_name"];

    /*
    Last 3 Historical Prices
    */

    $history = $conn->prepare("
    SELECT average_price
    FROM market_prices
    WHERE product_name=?
    ORDER BY recorded_date DESC
    LIMIT 3
    ");

    $history->execute([$product_name]);

    $prices =
    $history->fetchAll(PDO::FETCH_COLUMN);

    if(count($prices) < 3)
    {
        continue;
    }

    /*
    Current Historical Price
    */

    $current_price =
    $prices[0];

    /*
    Historical Average
    */

    $historical_average =
    array_sum($prices) /
    count($prices);

    /*
    Current Farmer Listings
    */

    $listing = $conn->prepare("
    SELECT AVG(price_per_unit)
    FROM products
    WHERE product_name=?
    AND status='Available'
    ");

    $listing->execute([
        $product_name
    ]);

    $current_market_price =
    $listing->fetchColumn();

    /*
    If no farmer listings exist
    */

    if(!$current_market_price)
    {
        $current_market_price =
        $historical_average;
    }

    /*
    Hybrid Forecast Model

    60% Historical
    40% Farmer Listings
    */

    $predicted_price =
    (
        ($historical_average * 0.60)
        +
        ($current_market_price * 0.40)
    );

    /*
    Confidence Level
    */

    $difference =
    abs(
        $historical_average -
        $current_market_price
    );

    if($difference <= 5)
    {
        $confidence =
        "<span class='high'>High</span>";
    }
    elseif($difference <= 15)
    {
        $confidence =
        "<span class='medium'>Medium</span>";
    }
    else
    {
        $confidence =
        "<span class='low'>Low</span>";
    }

    /*
    Trend Analysis
    */

    $change_percent =
    (
        ($predicted_price - $current_price)
        /
        $current_price
    ) * 100;

    if($change_percent > 5)
    {
        $trend =
        "<span class='up'>↑ Strong Rise</span>";
    }
    elseif($change_percent > 0)
    {
        $trend =
        "<span class='up'>↑ Rising</span>";
    }
    elseif($change_percent < -5)
    {
        $trend =
        "<span class='down'>↓ Strong Fall</span>";
    }
    elseif($change_percent < 0)
    {
        $trend =
        "<span class='down'>↓ Falling</span>";
    }
    else
    {
        $trend =
        "<span class='same'>→ Stable</span>";
    }

?>

<tr>

<td>

<?php echo htmlspecialchars($product_name); ?>

</td>

<td>

KES <?php echo number_format($current_price,2); ?>

</td>

<td>

KES <?php echo number_format($current_market_price,2); ?>

</td>

<td>

KES <?php echo number_format($predicted_price,2); ?>

</td>

<td>

<?php echo $trend; ?>

</td>

<td>

<?php echo $confidence; ?>

</td>

</tr>

<?php
}
?>

</table>

<br><br>

<a href="dashboard.php">
Back To Dashboard
</a>

</body>
</html>