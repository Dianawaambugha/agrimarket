<?php

require_once "auth_check.php";

if(!isset($_GET["id"]))
{
    die("Invalid Request");
}

$product_id = $_GET["id"];

$stmt = $conn->prepare("
SELECT *
FROM products
WHERE product_id=?
AND farmer_id=?
");

$stmt->execute([
    $product_id,
    $farmer_id
]);

$product = $stmt->fetch();

if(!$product)
{
    die("Unauthorized Access");
}

for($i=1;$i<=3;$i++)
{
    $img = $product["image".$i];

    if($img &&
       file_exists(
       "../assets/product_images/".$img))
    {
        unlink(
        "../assets/product_images/".$img
        );
    }
}

$delete = $conn->prepare("
DELETE FROM products
WHERE product_id=?
");

$delete->execute([$product_id]);

header("Location:view_products.php");
exit();