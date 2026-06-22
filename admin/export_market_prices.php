diana 
<?php

session_start();
require_once "../config/db.php";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="market_prices_report.csv"');

$output = fopen("php://output", "w");

fputcsv(
    $output,
    [
        'Price ID',
        'Product',
        'Average Price',
        'Region',
        'Date',
        'Source'
    ]
);

$stmt = $conn->query("
SELECT
price_id,
product_name,
average_price,
market_region,
recorded_date,
source
FROM market_prices
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    fputcsv($output, $row);
}

fclose($output);
exit();