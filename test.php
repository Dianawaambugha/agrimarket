<?php

$host = "127.0.0.1";
$dbname = "agri_market_connect";
$username = "root";
$password = "";

try {

    $conn = new PDO(
        "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    echo "Database connected successfully";

} catch(PDOException $e) {

    die("Connection failed: " . $e->getMessage());

}