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

$message = "";

/*
Get allowed contacts

1. Admins
2. Buyers who already messaged farmer
*/

$contacts = $conn->prepare("
SELECT DISTINCT
u.user_id,
u.full_name,
u.role

FROM users u

WHERE u.role='Admin'

UNION

SELECT DISTINCT
u.user_id,
u.full_name,
u.role

FROM users u

INNER JOIN messages m
ON m.sender_id=u.user_id

WHERE u.role='Buyer'
AND m.receiver_id=?
");

$contacts->execute([$user_id]);

$contacts = $contacts->fetchAll();

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $receiver_id = $_POST["receiver_id"];
    $message_text = trim($_POST["message_text"]);

    $allowed = false;

    $roleCheck = $conn->prepare("
    SELECT role
    FROM users
    WHERE user_id=?
    ");

    $roleCheck->execute([$receiver_id]);

    $receiver = $roleCheck->fetch();

    if($receiver)
    {
        /*
        Admin always allowed
        */

        if($receiver["role"]=="Admin")
        {
            $allowed = true;
        }

        /*
        Buyer only if buyer started conversation
        */

        if($receiver["role"]=="Buyer")
        {
            $check = $conn->prepare("
            SELECT message_id
            FROM messages
            WHERE sender_id=?
            AND receiver_id=?
            LIMIT 1
            ");

            $check->execute([
                $receiver_id,
                $user_id
            ]);

            if($check->rowCount()>0)
            {
                $allowed = true;
            }
        }
    }

    if($allowed)
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
            $receiver_id,
            $message_text,
            $user_id
        ]);

        $message = "Message sent.";
    }
    else
    {
        $message = "You cannot start a conversation with this buyer.";
    }
}

/*
Inbox
*/

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
<title>Messages</title>
</head>

<body>

<h2>Messages</h2>

<p><?php echo $message; ?></p>

<h3>Send Message</h3>

<form method="POST">

<select name="receiver_id" required>

<option value="">
Select Recipient
</option>

<?php foreach($contacts as $contact): ?>

<option value="<?php echo $contact['user_id']; ?>">

<?php echo htmlspecialchars($contact['full_name']); ?>
(<?php echo $contact['role']; ?>)

</option>

<?php endforeach; ?>

</select>

<br><br>

<textarea
name="message_text"
required
placeholder="Type message">
</textarea>

<br><br>

<button type="submit">
Send
</button>

</form>

<hr>

<h3>Inbox</h3>

<table border="1" cellpadding="10">

<tr>
<th>From</th>
<th>Message</th>
<th>Date</th>
</tr>

<?php foreach($messages as $msg): ?>

<tr>

<td>
<?php echo htmlspecialchars($msg["full_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($msg["message_text"]); ?>
</td>

<td>
<?php echo $msg["sent_at"]; ?>
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