diana 
<?php

session_start();
require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION["role"] != "Admin")
{
    die("Access Denied");
}

$user_id = $_SESSION["user_id"];

$message = "";

$contacts = $conn->query("
SELECT
user_id,
full_name,
role
FROM users
WHERE user_id != $user_id
ORDER BY full_name
")->fetchAll();

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $receiver_id = $_POST["receiver_id"];
    $message_text = trim($_POST["message_text"]);

    $stmt = $conn->prepare("
    INSERT INTO messages
    (
        sender_id,
        receiver_id,
        message_text,
        conversation_started_by
    )
    VALUES
    (?,?,?,?)
    ");

    $stmt->execute([
        $user_id,
        $receiver_id,
        $message_text,
        $user_id
    ]);

    $message = "Message sent.";
}

$inbox = $conn->prepare("
SELECT
m.*,
u.full_name

FROM messages m

INNER JOIN users u
ON m.sender_id=u.user_id

WHERE m.receiver_id=?

ORDER BY m.sent_at DESC
");

$inbox->execute([$user_id]);

$messages = $inbox->fetchAll();

?>

<!DOCTYPE html>

<html>
<head>
<title>Admin Messages</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>Admin Messaging Center</h2>

<p><?= $message ?></p>

<form method="POST">

<select name="receiver_id" required>

<option value="">
Select User
</option>

<?php foreach($contacts as $contact): ?>

<option value="<?= $contact["user_id"] ?>">

<?= htmlspecialchars($contact["full_name"]) ?>

(<?= $contact["role"] ?>)

</option>

<?php endforeach; ?>

</select>

<br><br>

<textarea
name="message_text"
required
rows="5"
cols="50">
</textarea>

<br><br>

<button type="submit">
Send
</button>

</form>

<hr>

<table border="1" cellpadding="10">

<tr>
<th>From</th>
<th>Message</th>
<th>Date</th>
</tr>

<?php foreach($messages as $msg): ?>

<tr>

<td>
<?= htmlspecialchars($msg["full_name"]) ?>
</td>

<td>
<?= htmlspecialchars($msg["message_text"]) ?>
</td>

<td>
<?= $msg["sent_at"] ?>
</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<a href="dashboard.php">
Back
</a>

</body>
</html>
