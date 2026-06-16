<?php

session_start();

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Buyer")
{
    die("Access denied");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Buyer Dashboard</title>
</head>

<body>

<h2>Welcome <?php echo $_SESSION["name"]; ?></h2>

<hr>

<a href="marketplace.php">
Browse Products
</a>

<br><br>

<a href="my_orders.php">
My Orders
</a>

<br><br>
<a href="market_insights.php">
📈 Market Insights
</a>

<br><br>
<a href="../auth/logout.php">
Logout
</a>

</body>
</html>