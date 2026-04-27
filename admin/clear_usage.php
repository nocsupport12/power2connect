<?php
// admin/clear_usage.php
session_start();
include("../components/db.php");

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Keep last 30 days of data
$days_to_keep = 30;
$delete = "DELETE FROM ai_usage_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
$stmt = mysqli_prepare($conn, $delete);
mysqli_stmt_bind_param($stmt, "i", $days_to_keep);
mysqli_stmt_execute($stmt);
$deleted = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

$_SESSION['message'] = "Cleared {$deleted} old records (kept last {$days_to_keep} days)";
header("Location: ai_usage_dashboard.php");
exit();
?>