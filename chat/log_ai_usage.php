<?php
// chat/log_ai_usage.php - For logging database/knowledge base responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No data provided']);
    exit();
}

$paths = [
    __DIR__ . '/../components/db.php',
    __DIR__ . '/components/db.php',
    dirname(__DIR__) . '/components/db.php'
];

$conn = null;
foreach ($paths as $path) {
    if (file_exists($path)) {
        include($path);
        break;
    }
}

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Create table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS ai_usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    user_message TEXT,
    ai_response TEXT,
    tokens_estimated INT DEFAULT 0,
    response_time FLOAT DEFAULT 0,
    source VARCHAR(20) DEFAULT 'unknown',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_source (source)
)";
mysqli_query($conn, $createTable);

$session_id = $input['session_id'] ?? 'unknown';
$user_message = $input['user_message'] ?? '';
$ai_response = $input['ai_response'] ?? '';
$source = $input['source'] ?? 'database';
$response_time = isset($input['response_time']) ? (float)$input['response_time'] : 0;
$tokens = isset($input['tokens_estimated']) ? (int)$input['tokens_estimated'] : round(strlen($ai_response) / 4);

$query = "INSERT INTO ai_usage_log (session_id, user_message, ai_response, tokens_estimated, response_time, source) 
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sssids", $session_id, $user_message, $ai_response, $tokens, $response_time, $source);

$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);

echo json_encode(['success' => $success]);
?>