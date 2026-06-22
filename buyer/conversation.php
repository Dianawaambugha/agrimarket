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
    die("User not selected.");
}

$other_user = (int)$_GET["user"];

/*
|--------------------------------------------------------------------------
| GET RECEIVER DETAILS
|--------------------------------------------------------------------------
*/

$user_stmt = $conn->prepare("
SELECT *
FROM users
WHERE user_id=?
");

$user_stmt->execute([$other_user]);

$receiver = $user_stmt->fetch();

if(!$receiver)
{
    die("User not found.");
}

/*
|--------------------------------------------------------------------------
| SEND MESSAGE
|--------------------------------------------------------------------------
*/

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $message = trim($_POST["message"]);

    if(!empty($message))
    {
        $insert = $conn->prepare("
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

        $insert->execute([
            $user_id,
            $other_user,
            $message,
            $user_id
        ]);
    }

    header("Location: conversation.php?user=".$other_user);
    exit();
}

/*
|--------------------------------------------------------------------------
| GET CHAT
|--------------------------------------------------------------------------
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

<div class="container">

<div class="header">

<h2>
💬 Chat with
<?php echo htmlspecialchars($receiver["full_name"]); ?>
</h2>

</div>

<div class="chat-box">

<?php foreach($messages as $msg): ?>

<div class="message <?php echo ($msg["sender_id"]==$user_id) ? 'sent' : 'received'; ?>">

<?php echo nl2br(htmlspecialchars($msg["message_text"])); ?>

<div class="time">

<?php echo $msg["sent_at"]; ?>

</div>

</div>

<?php endforeach; ?>

</div>

<form method="POST" class="chat-form">

<textarea
name="message"
placeholder="Type your message..."
required></textarea>

<button class="btn" type="submit">
Send
</button>

</form>

<br>

<a class="btn" href="messages.php">
← Back To Messages
</a>

</div>

</body>
</html>