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
| MONTHLY SALES
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
DATE_FORMAT(o.order_date,'%Y-%m') AS month,
SUM(o.total_amount) AS revenue
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
GROUP BY month
ORDER BY month
");

$stmt->execute([$farmer_id]);

$months = [];
$revenues = [];

while($row = $stmt->fetch())
{
    $months[] = $row["month"];
    $revenues[] = $row["revenue"] ?? 0;
}

/*
|--------------------------------------------------------------------------
| TOP PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
p.product_name,
SUM(o.quantity_ordered) total_sold
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
GROUP BY p.product_id
ORDER BY total_sold DESC
LIMIT 10
");

$stmt->execute([$farmer_id]);

$product_names = [];
$product_sales = [];

while($row = $stmt->fetch())
{
    $product_names[] = $row["product_name"];
    $product_sales[] = $row["total_sold"];
}

/*
|--------------------------------------------------------------------------
| ORDER STATUS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
o.order_status,
COUNT(*) total
FROM orders o
INNER JOIN products p
ON o.product_id = p.product_id
WHERE p.farmer_id=?
GROUP BY o.order_status
");

$stmt->execute([$farmer_id]);

$status_names = [];
$status_totals = [];

while($row = $stmt->fetch())
{
    $status_names[] = $row["order_status"];
    $status_totals[] = $row["total"];
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Sales Charts</title>
<link rel="stylesheet"
href="../assets/css/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<h2>Farmer Sales Charts</h2>

<div class="chart-box">

<h3>Revenue by Month</h3>

<canvas id="revenueChart"></canvas>

</div>

<div class="chart-box">

<h3>Top Selling Products</h3>

<canvas id="productChart"></canvas>

</div>

<div class="chart-box">

<h3>Order Status Distribution</h3>

<canvas id="statusChart"></canvas>

</div>

<br>

<a href="dashboard.php">
Back To Dashboard
</a>

<script>

/* Revenue */

new Chart(
document.getElementById('revenueChart'),
{
    type:'line',

    data:{
        labels:
        <?= json_encode($months); ?>,

        datasets:[{
            label:'Revenue (KES)',
            data:
            <?= json_encode($revenues); ?>
        }]
    }
});

/* Products */

new Chart(
document.getElementById('productChart'),
{
    type:'bar',

    data:{
        labels:
        <?= json_encode($product_names); ?>,

        datasets:[{
            label:'Units Sold',
            data:
            <?= json_encode($product_sales); ?>
        }]
    }
});

/* Status */

new Chart(
document.getElementById('statusChart'),
{
    type:'pie',

    data:{
        labels:
        <?= json_encode($status_names); ?>,

        datasets:[{
            data:
            <?= json_encode($status_totals); ?>
        }]
    }
});

</script>

</body>
</html>
