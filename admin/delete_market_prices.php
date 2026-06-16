<?php

session_start();
require_once "../config/db.php";

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

if(isset($_GET["id"]))
{
    $stmt = $conn->prepare("
    DELETE FROM market_prices
    WHERE price_id=?
    ");

    $stmt->execute([
        $_GET["id"]
    ]);
}

header("Location: market_prices.php");
exit();
?>