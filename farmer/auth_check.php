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

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$stmt->execute([$user_id]);

$farmer = $stmt->fetch();

if(!$farmer)
{
    die("Farmer account not found.");
}

$farmer_id = $farmer["farmer_id"];
?>