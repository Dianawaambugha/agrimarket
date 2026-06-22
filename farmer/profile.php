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

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
SELECT *
FROM farmers
WHERE user_id=?
");

$stmt->execute([$user_id]);

$farmer = $stmt->fetch();

if(!$farmer)
{
    die("Farmer profile not found.");
}

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $farm_name = trim($_POST["farm_name"]);
    $farm_location = trim($_POST["farm_location"]);
    $farm_size = $_POST["farm_size"];
    $farm_description = trim($_POST["farm_description"]);

    $update = $conn->prepare("
    UPDATE farmers
    SET
        farm_name=?,
        farm_location=?,
        farm_size=?,
        farm_description=?
    WHERE user_id=?
    ");

    $update->execute([
        $farm_name,
        $farm_location,
        $farm_size,
        $farm_description,
        $user_id
    ]);

    $message = "Farm profile updated successfully.";

    $stmt->execute([$user_id]);
    $farmer = $stmt->fetch();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Farm Profile</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Farm Profile</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<label>Farm Name</label>
<br>

<input
type="text"
name="farm_name"
value="<?php echo htmlspecialchars($farmer["farm_name"] ?? ""); ?>"
required>

<br><br>

<label>Farm Location</label>
<br>

<input
type="text"
name="farm_location"
value="<?php echo htmlspecialchars($farmer["farm_location"] ?? ""); ?>"
required>

<br><br>

<label>Farm Size (Acres)</label>
<br>

<input
type="number"
step="0.01"
name="farm_size"
value="<?php echo $farmer["farm_size"] ?? ""; ?>"
required>

<br><br>

<label>Farm Description</label>
<br>

<textarea
name="farm_description"
rows="5"
cols="50"><?php echo htmlspecialchars($farmer["farm_description"] ?? ""); ?></textarea>

<br><br>

<button type="submit">
Update Profile
</button>

</form>

<br><br>

<a href="dashboard.php">
Back to Dashboard
</a>

</body>
</html>