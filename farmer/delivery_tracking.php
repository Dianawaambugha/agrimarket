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

$farmer = $conn->prepare("
SELECT farmer_id
FROM farmers
WHERE user_id=?
");

$farmer->execute([$user_id]);

$farmer_id = $farmer->fetch()["farmer_id"];

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $order_id = $_POST["order_id"];
    $status = $_POST["status"];
    $notes = trim($_POST["notes"]);

    /*
    SECURITY CHECK:
    Farmer can update ONLY
    orders belonging to own products
    */

    $check = $conn->prepare("
    SELECT o.order_id
    FROM orders o
    INNER JOIN products p
    ON o.product_id=p.product_id
    WHERE o.order_id=?
    AND p.farmer_id=?
    ");

    $check->execute([
        $order_id,
        $farmer_id
    ]);

    if($check->rowCount()==0)
    {
        die("Unauthorized");
    }

    $existing = $conn->prepare("
    SELECT tracking_id
    FROM delivery_tracking
    WHERE order_id=?
    ");

    $existing->execute([$order_id]);

    if($existing->rowCount()>0)
    {
        $update = $conn->prepare("
        UPDATE delivery_tracking
        SET current_status=?,
            tracking_notes=?
        WHERE order_id=?
        ");

        $update->execute([
            $status,
            $notes,
            $order_id
        ]);
    }
    else
    {
        $insert = $conn->prepare("
        INSERT INTO delivery_tracking
        (
            order_id,
            current_status,
            tracking_notes
        )
        VALUES
        (?,?,?)
        ");

        $insert->execute([
            $order_id,
            $status,
            $notes
        ]);
    }

    /*
    Sync order status
    */

    $orderStatus = "Dispatched";

    if($status=="Delivered")
    {
        $orderStatus = "Delivered";
    }

    $sync = $conn->prepare("
    UPDATE orders
    SET order_status=?
    WHERE order_id=?
    ");

    $sync->execute([
        $orderStatus,
        $order_id
    ]);

    $message = "Tracking updated successfully.";
}

$orders = $conn->prepare("
SELECT
o.order_id,
o.order_status,
p.product_name,
dt.current_status
FROM orders o

INNER JOIN products p
ON o.product_id=p.product_id

LEFT JOIN delivery_tracking dt
ON o.order_id=dt.order_id

WHERE p.farmer_id=?

ORDER BY o.order_id DESC
");

$orders->execute([$farmer_id]);

$results = $orders->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<title>Delivery Tracking</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>
<body>

<h2>Delivery Tracking</h2>

<p><?php echo $message; ?></p>

<table border="1" cellpadding="10">

<tr>
<th>Order</th>
<th>Product</th>
<th>Order Status</th>
<th>Tracking</th>
<th>Update</th>
</tr>

<?php foreach($results as $row): ?>

<tr>

<td><?php echo $row["order_id"]; ?></td>

<td><?php echo htmlspecialchars($row["product_name"]); ?></td>

<td><?php echo $row["order_status"]; ?></td>

<td>
<?php echo $row["current_status"] ?? "Not Started"; ?>
</td>

<td>

<form method="POST">

<input
type="hidden"
name="order_id"
value="<?php echo $row["order_id"]; ?>">

<select name="status">

<option value="Preparing">
Preparing
</option>

<option value="Dispatched">
Dispatched
</option>

<option value="In Transit">
In Transit
</option>

<option value="Delivered">
Delivered
</option>

</select>

<br><br>

<textarea
name="notes"
placeholder="Tracking Notes">
</textarea>

<br><br>

<button type="submit">
Update
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back to Dashboard
</a>

</body>
</html>