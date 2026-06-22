diana 
<?php

session_start();
require_once "../config/db.php";

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if(!isset($_GET["user"]))
{
    die("No recipient selected.");
}

$other_user = (int)$_GET["user"];

$name_stmt = $conn->prepare("
SELECT full_name,role
FROM users
WHERE user_id=?
");

$name_stmt->execute([$other_user]);

$contact = $name_stmt->fetch();

if(!$contact)
{
    die("User not found.");
}

$canSend = true;

/*
Farmer cannot start conversation with buyer
*/

if($contact["role"]=="Buyer")
{
    $check = $conn->prepare("
    SELECT *
    FROM messages
    WHERE sender_id=?
    AND receiver_id=?
    LIMIT 1
    ");

    $check->execute([
        $other_user,
        $user_id
    ]);

    if($check->rowCount()==0)
    {
        $canSend = false;
    }
}

/*
SEND MESSAGE
*/

if($_SERVER["REQUEST_METHOD"]=="POST" && $canSend)
{
    $message = trim($_POST["message"]);

    if($message!="")
    {
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
            $other_user,
            $message,
            $user_id
        ]);
    }

    header(
    "Location: conversation.php?user=".$other_user
    );

    exit();
}

/*
CHAT HISTORY
*/

$chat = $conn->prepare("
SELECT *
FROM messages

WHERE
(
sender_id=?
AND receiver_id=?
)

OR

(
sender_id=?
AND receiver_id=?
)

ORDER BY sent_at ASC
");

$chat->execute([
    $user_id,
    $other_user,
    $other_user,
    $user_id
]);

$messages = $chat->fetchAll();

?>

<!DOCTYPE html>

<html>
<head>
<title>Conversation</title>
<link rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<h2>

Chat with

<?php echo htmlspecialchars($contact["full_name"]); ?>

</h2>

<hr>

<?php foreach($messages as $msg): ?>

<p>

<b>

<?php

if($msg["sender_id"]==$user_id)
{
    echo "You";
}
else
{
    echo htmlspecialchars($contact["full_name"]);
}

?>

:</b>

<?php echo htmlspecialchars($msg["message_text"]); ?>

<br>

<small>

<?php echo $msg["sent_at"]; ?>

</small>

</p>

<hr>

<?php endforeach; ?>

<?php if($canSend): ?>

<form method="POST">

<textarea
name="message"
rows="4"
cols="60"
required>
</textarea>

<br><br>

<button type="submit">
Send
</button>

</form>

<?php else: ?>

<p>

Buyer has not started a conversation yet.

</p>

<?php endif; ?>

<br>

<a href="messages.php">
Back
</a>

</body>
</html>
