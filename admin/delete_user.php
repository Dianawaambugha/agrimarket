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

if(isset($_GET["id"]))
{
    $user_id = (int)$_GET["id"];

    /*
    Prevent admin from deleting self
    */

    if($user_id == $_SESSION["user_id"])
    {
        die("You cannot delete your own account.");
    }

    /*
    Check role first
    */

    $check = $conn->prepare("
    SELECT role, full_name
    FROM users
    WHERE user_id=?
    ");

    $check->execute([$user_id]);

    $target = $check->fetch();

    if(!$target)
    {
        die("User not found.");
    }

    /*
    Prevent deleting admins
    */

    if($target["role"] == "Admin")
    {
        die("Admin accounts cannot be deleted.");
    }

    /*
    Audit Log
    */

    $log = $conn->prepare("
    INSERT INTO audit_logs
    (
        user_id,
        action_performed
    )
    VALUES
    (?,?)
    ");

    $log->execute([
        $_SESSION["user_id"],
        "Deleted user: ".$target["full_name"]
    ]);

    /*
    Delete user
    */

    $delete = $conn->prepare("
    DELETE FROM users
    WHERE user_id=?
    ");

    $delete->execute([$user_id]);
}

header("Location: manage_users.php");
exit();

?>