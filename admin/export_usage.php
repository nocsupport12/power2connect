<?php
// admin/export_usage.php
session_start();
include("../components/db.php");

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_usage_export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Session ID', 'User Message', 'AI Response', 'Tokens', 'Response Time (ms)', 'Source', 'Date/Time']);

$query = "SELECT * FROM ai_usage_log ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id'],
        $row['session_id'],
        $row['user_message'],
        $row['ai_response'],
        $row['tokens_estimated'],
        $row['response_time'],
        $row['source'],
        $row['created_at']
    ]);
}

fclose($output);
exit();
?>