<?php

require_once "auth_check.php";

$products = $conn->query("
SELECT *
FROM product_categories
ORDER BY category_name
")->fetchAll();

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $product_name = trim($_POST["product_name"]);

    if($product_name == "Other")
    {
        $product_name =
        trim($_POST["other_product"]);

        $main_category =
        trim($_POST["main_category"]);

        if(
            empty($product_name) ||
            empty($main_category)
        )
        {
            die("Product and Category required.");
        }

        $check = $conn->prepare("
        SELECT *
        FROM product_categories
        WHERE category_name=?
        ");

        $check->execute([
            $product_name
        ]);

        if(!$check->fetch())
        {
            $insert = $conn->prepare("
            INSERT INTO product_categories
            (
                category_name,
                main_category
            )
            VALUES
            (?,?)
            ");

            $insert->execute([
                $product_name,
                $main_category
            ]);
        }

        $category = $main_category;
    }
    else
    {
        $cat = $conn->prepare("
        SELECT main_category
        FROM product_categories
        WHERE category_name=?
        ");

        $cat->execute([
            $product_name
        ]);

        $category =
        $cat->fetch()["main_category"];
    }

    $quantity =
    $_POST["quantity"];

    $unit =
    trim($_POST["unit"]);

    $price =
    $_POST["price"];

    $description =
    trim($_POST["description"]);

    $allowed =
    ["jpg","jpeg","png","webp"];

    $images = [];

    for($i=1;$i<=3;$i++)
    {
        $field = "image".$i;

        $images[$i] = null;

        if(!empty($_FILES[$field]["name"]))
        {
            $ext = strtolower(
                pathinfo(
                    $_FILES[$field]["name"],
                    PATHINFO_EXTENSION
                )
            );

            if(in_array($ext,$allowed))
            {
                $filename =
                time()."_".$i."_".
                rand(1000,9999).".".$ext;

                move_uploaded_file(
                    $_FILES[$field]["tmp_name"],
                    "../assets/product_images/".$filename
                );

                $images[$i] = $filename;
            }
        }
    }

    $stmt = $conn->prepare("
    INSERT INTO products
    (
        farmer_id,
        product_name,
        category,
        quantity,
        unit,
        price_per_unit,
        description,
        image1,
        image2,
        image3
    )
    VALUES
    (?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $farmer_id,
        $product_name,
        $category,
        $quantity,
        $unit,
        $price,
        $description,
        $images[1],
        $images[2],
        $images[3]
    ]);

    $message =
    "Product Added Successfully";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Products</title>
</head>

<body>

<h2>Add Products</h2>

<p><?php echo $message; ?></p>

<form
method="POST"
enctype="multipart/form-data">

<label>
Product
</label>

<br><br>

<select
name="product_name"
id="product_name"
required
onchange="toggleOtherProduct()">

<option value="">
Select Product
</option>

<?php foreach($products as $product): ?>

<option
value="<?php echo $product['category_name']; ?>">

<?php echo $product['category_name']; ?>

</option>

<?php endforeach; ?>

<option value="Other">
Other
</option>

</select>

<br><br>

<div
id="other_product_div"
style="display:none;">

<input
type="text"
name="other_product"
placeholder="Enter New Product">

<br><br>

<select
name="main_category">

<option value="">
Select Category
</option>

<option value="Vegetables">
Vegetables
</option>

<option value="Fruits">
Fruits
</option>

<option value="Cereals">
Cereals
</option>

<option value="Legumes">
Legumes
</option>

<option value="Cash Crops">
Cash Crops
</option>

<option value="Oil Crops">
Oil Crops
</option>

<option value="Herbs and Spices">
Herbs and Spices
</option>

<option value="Root Crops">
Root Crops
</option>

</select>

</div>

<br><br>

<input
type="number"
step="0.1"
name="quantity"
placeholder="Quantity"
required>

<br><br>

<input
type="text"
name="unit"
placeholder="Unit (Kg, Bags, Crates)"
required>

<br><br>

<input
type="number"
step="1.0"
name="price"
placeholder="Price Per Unit"
required>

<br><br>

<textarea
name="description"
placeholder="Description">
</textarea>

<br><br>

Image 1

<br>

<input
type="file"
name="image1">

<br><br>

Image 2

<br>

<input
type="file"
name="image2">

<br><br>

Image 3

<br>

<input
type="file"
name="image3">

<br><br>

<button type="submit">
Save Product
</button>

</form>

<br>

<a href="dashboard.php">
Back to Dashboard
</a>

<script>

function toggleOtherProduct()
{
    let product =
    document.getElementById(
    "product_name"
    ).value;

    let div =
    document.getElementById(
    "other_product_div"
    );

    if(product=="Other")
    {
        div.style.display="block";
    }
    else
    {
        div.style.display="none";
    }
}

</script>

</body>
</html>