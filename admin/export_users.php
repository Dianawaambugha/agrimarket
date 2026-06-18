<?php

session_start();
require_once "../config/db.php";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_report.csv"');

$output = fopen("php://output", "w");

fputcsv(
    $output,
    [
        'User ID',
        'Full Name',
        'Email',
        'Phone',
        'Role',
        'Status'
    ]
);

$stmt = $conn->query("
SELECT
user_id,
full_name,
email,
phone_number,
role,
status
FROM users
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    fputcsv($output, $row);
}

fclose($output);
exit();