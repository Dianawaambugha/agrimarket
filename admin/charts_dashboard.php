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

/*
|--------------------------------------------------------------------------
| Monthly Sales
|--------------------------------------------------------------------------
*/

$sales_stmt = $conn->query("
SELECT
DATE_FORMAT(order_date,'%Y-%m') AS month,
SUM(total_amount) AS sales
FROM orders
GROUP BY month
ORDER BY month
");

$months = [];
$sales = [];

while($row = $sales_stmt->fetch())
{
    $months[] = $row["month"];
    $sales[] = $row["sales"] ?? 0;
}

/*
|--------------------------------------------------------------------------
| Product Categories
|--------------------------------------------------------------------------
*/

$cat_stmt = $conn->query("
SELECT
category,
COUNT(*) AS total
FROM products
GROUP BY category
");

$categories = [];
$category_totals = [];

while($row = $cat_stmt->fetch())
{
    $categories[] = $row["category"];
    $category_totals[] = $row["total"];
}

/*
|--------------------------------------------------------------------------
| Top Products
|--------------------------------------------------------------------------
*/

$product_stmt = $conn->query("
SELECT
product_name,
COUNT(*) AS total
FROM products
GROUP BY product_name
ORDER BY total DESC
LIMIT 10
");

$product_names = [];
$product_totals = [];

while($row = $product_stmt->fetch())
{
    $product_names[] = $row["product_name"];
    $product_totals[] = $row["total"];
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Charts Dashboard</title>
<link rel="stylesheet"
href="../assets/css/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>

<body>

<h2>Analytics Charts Dashboard</h2>

<div class="chart-box">

<h3>Monthly Sales Trend</h3>

<canvas id="salesChart"></canvas>

</div>

<div class="chart-box">

<h3>Product Categories Distribution</h3>

<canvas id="categoryChart"></canvas>

</div>

<div class="chart-box">

<h3>Top Listed Products</h3>

<canvas id="productChart"></canvas>

</div>

<br>

<a href="analytics_dashboard.php">
Back To Analytics Dashboard
</a>

<script>

/*
|--------------------------------------------------------------------------
| Monthly Sales
|--------------------------------------------------------------------------
*/

new Chart(
document.getElementById('salesChart'),
{
    type:'line',

    data:{
        labels:
        <?php echo json_encode($months); ?>,

        datasets:[{
            label:'Sales (KES)',
            data:
            <?php echo json_encode($sales); ?>
        }]
    }
});

/*
|--------------------------------------------------------------------------
| Category Distribution
|--------------------------------------------------------------------------
*/

new Chart(
document.getElementById('categoryChart'),
{
    type:'pie',

    data:{
        labels:
        <?php echo json_encode($categories); ?>,

        datasets:[{
            data:
            <?php echo json_encode($category_totals); ?>
        }]
    }
});

/*
|--------------------------------------------------------------------------
| Top Products
|--------------------------------------------------------------------------
*/

new Chart(
document.getElementById('productChart'),
{
    type:'bar',

    data:{
        labels:
        <?php echo json_encode($product_names); ?>,

        datasets:[{
            label:'Listings',
            data:
            <?php echo json_encode($product_totals); ?>
        }]
    }
});

</script>

</body>
</html>