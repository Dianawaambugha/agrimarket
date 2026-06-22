diana 
<?php

session_start();
require_once "../config/db.php";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_report.csv"');

$output = fopen("php://output", "w");

fputcsv(
    $output,
    [
        'Order ID',
        'Buyer ID',
        'Product ID',
        'Quantity',
        'Total Amount',
        'Status',
        'Order Date'
    ]
);

$stmt = $conn->query("
SELECT
order_id,
buyer_id,
product_id,
quantity_ordered,
total_amount,
order_status,
order_date
FROM orders
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    fputcsv($output, $row);
}

fclose($output);
exit();