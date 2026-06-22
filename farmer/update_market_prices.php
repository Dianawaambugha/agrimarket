diana 
<?php

require_once "../config/db.php";

$stmt = $conn->prepare("
INSERT INTO market_prices
(
    product_name,
    average_price,
    market_region,
    recorded_date
)

SELECT
product_name,
AVG(price_per_unit),
'Kenya',
CURDATE()

FROM products
GROUP BY product_name
");

$stmt->execute();

echo "Market prices updated.";