diana 
<?php

function logActivity($conn,$user_id,$action)
{
    $stmt = $conn->prepare("
    INSERT INTO audit_logs
    (
        user_id,
        action_performed
    )
    VALUES
    (?,?)
    ");

    $stmt->execute([
        $user_id,
        $action
    ]);
}