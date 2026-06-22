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
    die("Access denied");
}

if(!isset($_GET["id"]))
{
    die("Invalid product.");
}

$product_id = $_GET["id"];
$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| GET FARMER ID
|--------------------------------------------------------------------------
*/

$farm = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$farm->execute([$user_id]);

$farmer = $farm->fetch();

if(!$farmer)
{
    die("Farmer account not found.");
}

$farmer_id = $farmer["farmer_id"];

/*
|--------------------------------------------------------------------------
| GET PRODUCT (OWNERSHIP CHECK)
|--------------------------------------------------------------------------
*/

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
    die("You cannot edit this product.");
}

$message = "";

/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT
|--------------------------------------------------------------------------
*/

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $product_name = trim($_POST["product_name"]);
    $category = trim($_POST["category"]);
    $quantity = $_POST["quantity"];
    $unit = trim($_POST["unit"]);
    $price = $_POST["price"];
    $description = trim($_POST["description"]);
    $status = $_POST["status"];

    $image1 = $product["image1"];
    $image2 = $product["image2"];
    $image3 = $product["image3"];

    $upload_dir = "../assets/products/";

    if(!file_exists($upload_dir))
    {
        mkdir($upload_dir,0777,true);
    }

    /*
    ----------------------------------------------------
    IMAGE 1
    ----------------------------------------------------
    */

    if(!empty($_FILES["image1"]["name"]))
    {
        $image1 =
        time()."_1_".
        basename($_FILES["image1"]["name"]);

        move_uploaded_file(
            $_FILES["image1"]["tmp_name"],
            $upload_dir.$image1
        );
    }

    /*
    ----------------------------------------------------
    IMAGE 2
    ----------------------------------------------------
    */

    if(!empty($_FILES["image2"]["name"]))
    {
        $image2 =
        time()."_2_".
        basename($_FILES["image2"]["name"]);

        move_uploaded_file(
            $_FILES["image2"]["tmp_name"],
            $upload_dir.$image2
        );
    }

    /*
    ----------------------------------------------------
    IMAGE 3
    ----------------------------------------------------
    */

    if(!empty($_FILES["image3"]["name"]))
    {
        $image3 =
        time()."_3_".
        basename($_FILES["image3"]["name"]);

        move_uploaded_file(
            $_FILES["image3"]["tmp_name"],
            $upload_dir.$image3
        );
    }

    /*
    ----------------------------------------------------
    UPDATE DATABASE
    ----------------------------------------------------
    */

    $update = $conn->prepare("
    UPDATE products
    SET
        product_name=?,
        category=?,
        quantity=?,
        unit=?,
        price_per_unit=?,
        description=?,
        status=?,
        image1=?,
        image2=?,
        image3=?
    WHERE product_id=?
    AND farmer_id=?
    ");

    $update->execute([
        $product_name,
        $category,
        $quantity,
        $unit,
        $price,
        $description,
        $status,
        $image1,
        $image2,
        $image3,
        $product_id,
        $farmer_id
    ]);

    $message = "Product updated successfully.";

    /*
    ----------------------------------------------------
    REFRESH PRODUCT DATA
    ----------------------------------------------------
    */

    $stmt->execute([
        $product_id,
        $farmer_id
    ]);

    $product = $stmt->fetch();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Product</title>
<link rel="stylesheet"
href="../assets/css/style.css">
</head>

<body>

<h2>Edit Product</h2>

<p><?php echo $message; ?></p>

<form method="POST" enctype="multipart/form-data">

<label>Product Name</label>
<br>

<input
type="text"
name="product_name"
value="<?php echo htmlspecialchars($product['product_name']); ?>"
required>

<br><br>

<label>Category</label>
<br>

<input
type="text"
name="category"
value="<?php echo htmlspecialchars($product['category']); ?>"
required>

<br><br>

<label>Quantity</label>
<br>

<input
type="number"
step="0.01"
name="quantity"
value="<?php echo $product['quantity']; ?>"
required>

<br><br>

<label>Unit</label>
<br>

<input
type="text"
name="unit"
value="<?php echo htmlspecialchars($product['unit']); ?>"
required>

<br><br>

<label>Price Per Unit</label>
<br>

<input
type="number"
step="0.01"
name="price"
value="<?php echo $product['price_per_unit']; ?>"
required>

<br><br>

<label>Description</label>
<br>

<textarea
name="description"
rows="5"
cols="40"><?php echo htmlspecialchars($product['description']); ?></textarea>

<br><br>

<label>Status</label>
<br>

<select name="status">

<option value="Available"
<?php if($product["status"]=="Available") echo "selected"; ?>>
Available
</option>

<option value="Sold Out"
<?php if($product["status"]=="Sold Out") echo "selected"; ?>>
Sold Out
</option>

</select>

<br><br>

<h3>Current Images</h3>

<?php if($product["image1"]): ?>
<img src="../assets/products/<?php echo $product["image1"]; ?>" width="120">
<?php endif; ?>

<?php if($product["image2"]): ?>
<img src="../assets/products/<?php echo $product["image2"]; ?>" width="120">
<?php endif; ?>

<?php if($product["image3"]): ?>
<img src="../assets/products/<?php echo $product["image3"]; ?>" width="120">
<?php endif; ?>

<br><br>

<label>Replace Image 1</label>
<br>
<input type="file" name="image1">

<br><br>

<label>Replace Image 2</label>
<br>
<input type="file" name="image2">

<br><br>

<label>Replace Image 3</label>
<br>
<input type="file" name="image3">

<br><br>

<button type="submit">
Update Product
</button>

</form>

<br><br>

<a href="view_products.php">
Back to Products
</a>

</body>
</html>