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

/*
|--------------------------------------------------------------------------
| FARMER ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$stmt->execute([$_SESSION["user_id"]]);

$farmer_id = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| DOWNLOAD CSV
|--------------------------------------------------------------------------
*/

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="my_sales_report.csv"');

$output = fopen("php://output", "w");

fputcsv(
    $output,
    [
        'Order ID',
        'Product',
        'Quantity',
        'Amount',
        'Status',
        'Date'
    ]
);

$stmt = $conn->prepare("
SELECT
o.order_id,
p.product_name,
o.quantity_ordered,
o.total_amount,
o.order_status,
o.order_date
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
ORDER BY o.order_date DESC
");

$stmt->execute([$farmer_id]);

while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
