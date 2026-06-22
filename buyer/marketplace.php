diana 
<?php

session_start();

require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Buyer")
{
    die("Access Denied");
}

/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$search = $_GET["search"] ?? "";
$category = $_GET["category"] ?? "";
$sort = $_GET["sort"] ?? "";

/*
|--------------------------------------------------------------------------
| BUILD QUERY
|--------------------------------------------------------------------------
*/

$sql = "
SELECT *
FROM products
WHERE status='Available'
";

$params = [];

if($search != "")
{
    $sql .= " AND product_name LIKE ?";
    $params[] = "%".$search."%";
}

if($category != "")
{
    $sql .= " AND category=?";
    $params[] = $category;
}

if($sort == "low")
{
    $sql .= " ORDER BY price_per_unit ASC";
}
elseif($sort == "high")
{
    $sql .= " ORDER BY price_per_unit DESC";
}
else
{
    $sql .= " ORDER BY product_id DESC";
}

$stmt = $conn->prepare($sql);

$stmt->execute($params);

$products = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| CATEGORY DROPDOWN
|--------------------------------------------------------------------------
*/

$categories_stmt = $conn->query("
SELECT DISTINCT category
FROM products
ORDER BY category
");

$categories = $categories_stmt->fetchAll();

?>

<!DOCTYPE html>

<html>
<head>

<title>Marketplace</title>
<link rel="stylesheet"
href="../assets/css/style.css">


</head>

<body>
<div class="container">
<div class="header">
    <h2>Marketplace</h2>
</div>
<div class="card">

<form method="GET">

<input
type="text"
name="search"
placeholder="Search Product"
value="<?php echo htmlspecialchars($search); ?>">

<select name="category">

<option value="">
All Categories
</option>

<?php foreach($categories as $cat): ?>

<option
value="<?php echo $cat["category"]; ?>"
<?php if($category == $cat["category"]) echo "selected"; ?>
>

<?php echo htmlspecialchars($cat["category"]); ?>

</option>

<?php endforeach; ?>

</select>

<select name="sort">

<option value="">
Default Sorting
</option>

<option
value="low"
<?php if($sort=="low") echo "selected"; ?>
>
Price Low → High
</option>

<option
value="high"
<?php if($sort=="high") echo "selected"; ?>
>
Price High → Low
</option>

</select>

<button type="submit">
Search
</button>

</form>
</div>
<table>

<tr>
<th>Image</th>
<th>Product</th>
<th>Category</th>
<th>Quantity</th>
<th>Unit</th>
<th>Price (KES)</th>
<th>Action</th>

</tr>

<?php foreach($products as $product): ?>

<tr>

<td>
    <?php if(!empty($product["image1"])): ?>

<img
src="../uploads/products/<?php echo $product["image1"]; ?>"
class="product-image">

<?php else: ?>

No Image

<?php endif; ?>

</td>

<td>
<?php echo htmlspecialchars($product["product_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($product["category"]); ?>
</td>

<td>
<?php echo $product["quantity"]; ?>
</td>

<td>
<?php echo htmlspecialchars($product["unit"]); ?>
</td>

<td>
<?php echo number_format($product["price_per_unit"],2); ?>
</td>

<td>

<a
class="btn"
href="place_order.php?id=<?php echo $product["product_id"]; ?>">
Order </a>

</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a class="btn" href="dashboard.php">
← Back To Dashboard
</a>
</div>
</body>
</html>
