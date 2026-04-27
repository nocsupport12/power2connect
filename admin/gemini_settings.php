<?php
// admin/gemini_settings.php - With Remove Buttons & Provider Order
session_start();
include("../components/db.php");

// Check admin access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include("../components/admin_nav.php");

// Create settings table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    masked_value VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createTable);

// Create provider order table
$createOrderTable = "CREATE TABLE IF NOT EXISTS api_provider_order (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) UNIQUE NOT NULL,
    priority INT DEFAULT 0,
    is_active TINYINT DEFAULT 1
)";
mysqli_query($conn, $createOrderTable);

// Insert default provider order if empty
$checkOrder = mysqli_query($conn, "SELECT COUNT(*) as count FROM api_provider_order");
$orderCount = mysqli_fetch_assoc($checkOrder)['count'];
if ($orderCount == 0) {
    $defaultProviders = [
        ['gemini', 1, 1],
        ['openai', 2, 1],
        ['claude', 3, 0],
        ['groq', 4, 1],
        ['deepseek', 5, 0]
    ];
    foreach ($defaultProviders as $provider) {
        $insert = "INSERT INTO api_provider_order (provider, priority, is_active) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sii", $provider[0], $provider[1], $provider[2]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle Remove API Key
    if (isset($_POST['remove_api_key'])) {
        $provider = $_POST['remove_provider'];
        $keyColumn = $provider . '_api_key';
        
        $delete = "DELETE FROM system_settings WHERE setting_key = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, "s", $keyColumn);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ {$provider} API Key removed successfully!";
        } else {
            $error = "❌ Failed to remove: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    
    // Handle Update Provider Order
    if (isset($_POST['update_order'])) {
        $priorities = $_POST['priority'] ?? [];
        foreach ($priorities as $provider => $priority) {
            $update = "UPDATE api_provider_order SET priority = ? WHERE provider = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "is", $priority, $provider);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $message = "✅ Provider order updated successfully!";
    }
    
    // Handle Toggle Provider Active Status
    if (isset($_POST['toggle_provider'])) {
        $provider = $_POST['toggle_provider'];
        $currentStatus = $_POST['current_status'];
        $newStatus = $currentStatus == 1 ? 0 : 1;
        
        $update = "UPDATE api_provider_order SET is_active = ? WHERE provider = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "is", $newStatus, $provider);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        $statusText = $newStatus == 1 ? "enabled" : "disabled";
        $message = "✅ {$provider} provider {$statusText}!";
    }
    
    // Handle Gemini API Key
    if (isset($_POST['update_gemini_key'])) {
        $new_api_key = trim($_POST['gemini_api_key']);
        if (!empty($new_api_key)) {
            $masked = maskApiKey($new_api_key);
            $update = "INSERT INTO system_settings (setting_key, setting_value, masked_value) 
                       VALUES ('gemini_api_key', ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), masked_value = VALUES(masked_value)";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "ss", $new_api_key, $masked);
            mysqli_stmt_execute($stmt) ? $message = "✅ Gemini API Key saved!" : $error = "❌ Failed to save";
            mysqli_stmt_close($stmt);
        }
    }
    
    // Handle OpenAI API Key
    if (isset($_POST['update_openai_key'])) {
        $new_api_key = trim($_POST['openai_api_key']);
        if (!empty($new_api_key)) {
            $masked = maskApiKey($new_api_key);
            $update = "INSERT INTO system_settings (setting_key, setting_value, masked_value) 
                       VALUES ('openai_api_key', ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), masked_value = VALUES(masked_value)";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "ss", $new_api_key, $masked);
            mysqli_stmt_execute($stmt) ? $message = "✅ OpenAI API Key saved!" : $error = "❌ Failed to save";
            mysqli_stmt_close($stmt);
        }
    }
    
    // Handle Claude API Key
    if (isset($_POST['update_claude_key'])) {
        $new_api_key = trim($_POST['claude_api_key']);
        if (!empty($new_api_key)) {
            $masked = maskApiKey($new_api_key);
            $update = "INSERT INTO system_settings (setting_key, setting_value, masked_value) 
                       VALUES ('claude_api_key', ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), masked_value = VALUES(masked_value)";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "ss", $new_api_key, $masked);
            mysqli_stmt_execute($stmt) ? $message = "✅ Claude API Key saved!" : $error = "❌ Failed to save";
            mysqli_stmt_close($stmt);
        }
    }
    
    // Handle Groq API Key
    if (isset($_POST['update_groq_key'])) {
        $new_api_key = trim($_POST['groq_api_key']);
        if (!empty($new_api_key)) {
            $masked = maskApiKey($new_api_key);
            $update = "INSERT INTO system_settings (setting_key, setting_value, masked_value) 
                       VALUES ('groq_api_key', ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), masked_value = VALUES(masked_value)";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "ss", $new_api_key, $masked);
            mysqli_stmt_execute($stmt) ? $message = "✅ Groq API Key saved!" : $error = "❌ Failed to save";
            mysqli_stmt_close($stmt);
        }
    }
    
    // Handle DeepSeek API Key
    if (isset($_POST['update_deepseek_key'])) {
        $new_api_key = trim($_POST['deepseek_api_key']);
        if (!empty($new_api_key)) {
            $masked = maskApiKey($new_api_key);
            $update = "INSERT INTO system_settings (setting_key, setting_value, masked_value) 
                       VALUES ('deepseek_api_key', ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), masked_value = VALUES(masked_value)";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "ss", $new_api_key, $masked);
            mysqli_stmt_execute($stmt) ? $message = "✅ DeepSeek API Key saved!" : $error = "❌ Failed to save";
            mysqli_stmt_close($stmt);
        }
    }
    
    // Test API Key
    if (isset($_POST['test_api_key'])) {
        $provider = $_POST['test_provider'];
        $api_key = '';
        
        switch ($provider) {
            case 'gemini':
                $query = "SELECT setting_value FROM system_settings WHERE setting_key = 'gemini_api_key'";
                $result = mysqli_query($conn, $query);
                $api_key = ($result && $row = mysqli_fetch_assoc($result)) ? $row['setting_value'] : '';
                $test_result = testGeminiApi($api_key);
                break;
            case 'openai':
                $query = "SELECT setting_value FROM system_settings WHERE setting_key = 'openai_api_key'";
                $result = mysqli_query($conn, $query);
                $api_key = ($result && $row = mysqli_fetch_assoc($result)) ? $row['setting_value'] : '';
                $test_result = testOpenAI($api_key);
                break;
            case 'groq':
                $query = "SELECT setting_value FROM system_settings WHERE setting_key = 'groq_api_key'";
                $result = mysqli_query($conn, $query);
                $api_key = ($result && $row = mysqli_fetch_assoc($result)) ? $row['setting_value'] : '';
                $test_result = testGroq($api_key);
                break;
            default:
                $test_result = ['success' => false, 'error' => 'Unknown provider'];
        }
        
        if ($test_result['success']) {
            $message = "✅ {$provider} API Key is valid! Response: " . htmlspecialchars($test_result['response']);
        } else {
            $error = "❌ {$provider} API Key test failed: " . $test_result['error'];
        }
    }
}

function maskApiKey($key) {
    $length = strlen($key);
    if ($length <= 12) return '***';
    $showStart = min(8, floor($length / 3));
    $showEnd = min(8, floor($length / 3));
    return substr($key, 0, $showStart) . '...' . substr($key, -$showEnd);
}

// Get all API keys
$apiKeys = [
    'gemini' => ['masked' => 'Not set', 'updated' => ''],
    'openai' => ['masked' => 'Not set', 'updated' => ''],
    'claude' => ['masked' => 'Not set', 'updated' => ''],
    'groq' => ['masked' => 'Not set', 'updated' => ''],
    'deepseek' => ['masked' => 'Not set', 'updated' => '']
];

$query = "SELECT setting_key, masked_value, updated_at FROM system_settings 
          WHERE setting_key IN ('gemini_api_key', 'openai_api_key', 'claude_api_key', 'groq_api_key', 'deepseek_api_key')";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $key = str_replace('_api_key', '', $row['setting_key']);
        $apiKeys[$key] = ['masked' => $row['masked_value'] ?: 'Set', 'updated' => $row['updated_at']];
    }
}

// Get provider order
$providerOrder = [];
$orderQuery = "SELECT * FROM api_provider_order ORDER BY priority ASC";
$orderResult = mysqli_query($conn, $orderQuery);
if ($orderResult) {
    while ($row = mysqli_fetch_assoc($orderResult)) {
        $providerOrder[$row['provider']] = ['priority' => $row['priority'], 'is_active' => $row['is_active']];
    }
}

// Test functions
function testGeminiApi($api_key) {
    if (empty($api_key)) return ['success' => false, 'error' => 'No API key configured'];
    $model = 'gemma-4-31b-it';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
    $payload = ['contents' => [['parts' => [['text' => 'Say "OK"']]]]];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) return ['success' => true, 'response' => 'OK'];
    $errorData = json_decode($response, true);
    return ['success' => false, 'error' => $errorData['error']['message'] ?? "HTTP {$httpCode}"];
}

function testOpenAI($api_key) {
    if (empty($api_key)) return ['success' => false, 'error' => 'No API key configured'];
    $payload = ['model' => 'gpt-3.5-turbo', 'messages' => [['role' => 'user', 'content' => 'Say "OK"']], 'max_tokens' => 10];
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) return ['success' => true, 'response' => 'OK'];
    $errorData = json_decode($response, true);
    return ['success' => false, 'error' => $errorData['error']['message'] ?? "HTTP {$httpCode}"];
}

function testGroq($api_key) {
    if (empty($api_key)) return ['success' => false, 'error' => 'No API key configured'];
    $payload = ['model' => 'mixtral-8x7b-32768', 'messages' => [['role' => 'user', 'content' => 'Say "OK"']], 'max_tokens' => 10];
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) return ['success' => true, 'response' => 'OK'];
    $errorData = json_decode($response, true);
    return ['success' => false, 'error' => $errorData['error']['message'] ?? "HTTP {$httpCode}"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI API Settings - Multi-Provider</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">
                <i class="fas fa-key text-blue-600 mr-2"></i>AI API Settings
            </h1>
            <div class="space-x-2">
                <a href="ai_usage_dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-chart-line mr-2"></i>Usage Dashboard
                </a>
                <a href="knowledge_admin.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-database mr-2"></i>Knowledge Base
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- WHICH AI TO USE - Provider Order & Priority -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b px-6 py-4 bg-gradient-to-r from-purple-50 to-white">
                <h2 class="text-xl font-bold"><i class="fas fa-sort-amount-down text-purple-600 mr-2"></i>Which AI Should the Chatbot Use?</h2>
                <p class="text-sm text-gray-600">Set the order and enable/disable providers. The chatbot tries providers in priority order (1st to 5th).</p>
            </div>
            <div class="p-6">
                <form method="POST">
                    <div class="space-y-3">
                        <?php 
                        $providerNames = [
                            'gemini' => ['name' => 'Google Gemini', 'icon' => 'fab fa-google', 'color' => 'blue', 'free' => '1,500 req/day FREE'],
                            'openai' => ['name' => 'OpenAI (ChatGPT)', 'icon' => 'fab fa-openid', 'color' => 'green', 'free' => 'Paid - ~$0.002/req'],
                            'claude' => ['name' => 'Claude (Anthropic)', 'icon' => 'fas fa-brain', 'color' => 'purple', 'free' => 'Limited free tier'],
                            'groq' => ['name' => 'Groq (Fastest)', 'icon' => 'fas fa-bolt', 'color' => 'orange', 'free' => '30 req/min FREE'],
                            'deepseek' => ['name' => 'DeepSeek', 'icon' => 'fas fa-chart-line', 'color' => 'teal', 'free' => '500k tokens FREE']
                        ];
                        
                        foreach ($providerOrder as $provider => $data): 
                            $info = $providerNames[$provider];
                            $isActive = $data['is_active'];
                        ?>
                        <div class="flex items-center gap-4 p-3 rounded-lg <?= $isActive ? 'bg-' . $info['color'] . '-50' : 'bg-gray-100 opacity-60' ?>">
                            <div class="w-48 flex items-center gap-2">
                                <i class="<?= $info['icon'] ?> text-<?= $info['color'] ?>-600 text-xl"></i>
                                <div>
                                    <div class="font-bold"><?= $info['name'] ?></div>
                                    <div class="text-xs text-gray-500"><?= $info['free'] ?></div>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center gap-4">
                                <label class="flex items-center gap-1"><input type="radio" name="priority[<?= $provider ?>]" value="1" <?= $data['priority'] == 1 ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm">1st</span></label>
                                <label class="flex items-center gap-1"><input type="radio" name="priority[<?= $provider ?>]" value="2" <?= $data['priority'] == 2 ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm">2nd</span></label>
                                <label class="flex items-center gap-1"><input type="radio" name="priority[<?= $provider ?>]" value="3" <?= $data['priority'] == 3 ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm">3rd</span></label>
                                <label class="flex items-center gap-1"><input type="radio" name="priority[<?= $provider ?>]" value="4" <?= $data['priority'] == 4 ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm">4th</span></label>
                                <label class="flex items-center gap-1"><input type="radio" name="priority[<?= $provider ?>]" value="5" <?= $data['priority'] == 5 ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm">5th</span></label>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" name="toggle_provider" value="<?= $provider ?>" 
                                    onclick="this.form.toggle_provider.value='<?= $provider ?>'; this.form.current_status.value='<?= $isActive ?>'" 
                                    class="px-3 py-1 rounded text-sm <?= $isActive ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 hover:bg-gray-500' ?> text-white">
                                    <i class="fas <?= $isActive ? 'fa-toggle-on' : 'fa-toggle-off' ?> mr-1"></i><?= $isActive ? 'Active' : 'Inactive' ?>
                                </button>
                                <input type="hidden" name="toggle_provider" value="">
                                <input type="hidden" name="current_status" value="">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="update_order" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                            <i class="fas fa-save mr-2"></i>Save Provider Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- API Key Sections -->
        <?php 
        $providers = [
            'gemini' => ['name' => 'Google Gemini', 'icon' => 'fab fa-google', 'color' => 'blue', 'keyHint' => 'AIzaSy...', 'testable' => true],
            'openai' => ['name' => 'OpenAI (ChatGPT)', 'icon' => 'fab fa-openid', 'color' => 'green', 'keyHint' => 'sk-proj-... or sk-...', 'testable' => true],
            'groq' => ['name' => 'Groq (Fastest)', 'icon' => 'fas fa-bolt', 'color' => 'orange', 'keyHint' => 'gsk_...', 'testable' => true],
            'claude' => ['name' => 'Claude (Anthropic)', 'icon' => 'fas fa-brain', 'color' => 'purple', 'keyHint' => 'sk-ant-...', 'testable' => false],
            'deepseek' => ['name' => 'DeepSeek', 'icon' => 'fas fa-chart-line', 'color' => 'teal', 'keyHint' => 'Enter your key', 'testable' => false]
        ];
        
        foreach ($providers as $key => $provider): 
            $isActive = isset($providerOrder[$key]) && $providerOrder[$key]['is_active'];
        ?>
        <div class="bg-white rounded-lg shadow mb-6 <?= !$isActive ? 'opacity-60' : '' ?>">
            <div class="border-b px-6 py-4 bg-gradient-to-r from-<?= $provider['color'] ?>-50 to-white">
                <h2 class="text-xl font-bold"><i class="<?= $provider['icon'] ?> text-<?= $provider['color'] ?>-600 mr-2"></i><?= $provider['name'] ?> API</h2>
                <p class="text-sm text-gray-600"><?= $provider['testable'] ? 'Test your API key to verify it works' : 'Save your API key to enable this provider' ?></p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><label class="block text-gray-600 text-sm mb-1">Current Status</label><div class="bg-gray-50 p-2 rounded font-mono text-sm"><?= htmlspecialchars($apiKeys[$key]['masked']) ?></div></div>
                    <div><label class="block text-gray-600 text-sm mb-1">Last Updated</label><div class="bg-gray-50 p-2 rounded text-sm"><?= $apiKeys[$key]['updated'] ? date('M d, Y h:i A', strtotime($apiKeys[$key]['updated'])) : 'Never' ?></div></div>
                </div>
                <form method="POST" class="flex gap-3">
                    <input type="password" name="<?= $key ?>_api_key" class="flex-1 border border-gray-300 rounded-lg p-2 font-mono text-sm" placeholder="<?= $provider['keyHint'] ?>">
                    <button type="submit" name="update_<?= $key ?>_key" class="bg-<?= $provider['color'] ?>-600 text-white px-4 py-2 rounded-lg hover:bg-<?= $provider['color'] ?>-700">Save</button>
                    <?php if ($provider['testable']): ?>
                    <button type="submit" name="test_api_key" value="<?= $key ?>" onclick="this.form.test_provider.value='<?= $key ?>'" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Test</button>
                    <?php endif; ?>
                    <button type="submit" name="remove_api_key" value="<?= $key ?>" onclick="this.form.remove_provider.value='<?= $key ?>'; return confirm('Remove <?= $provider['name'] ?> API key?')" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"><i class="fas fa-trash mr-1"></i>Remove</button>
                    <input type="hidden" name="remove_provider" value="">
                    <input type="hidden" name="test_provider" value="">
                </form>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Get your key from: 
                    <?php if ($key == 'gemini'): ?><a href="https://aistudio.google.com/apikey" target="_blank" class="text-blue-600">Google AI Studio</a>
                    <?php elseif ($key == 'openai'): ?><a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600">OpenAI Platform</a>
                    <?php elseif ($key == 'groq'): ?><a href="https://console.groq.com/keys" target="_blank" class="text-blue-600">Groq Console (FREE)</a>
                    <?php elseif ($key == 'claude'): ?><a href="https://console.anthropic.com/" target="_blank" class="text-blue-600">Anthropic Console</a>
                    <?php elseif ($key == 'deepseek'): ?><a href="https://platform.deepseek.com/" target="_blank" class="text-blue-600">DeepSeek Platform</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Recommendation Box -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-3"><i class="fas fa-star text-yellow-500 mr-2"></i>Recommendation: Which AI Should You Use?</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-3 border border-green-200">
                    <div class="font-bold text-green-600"><i class="fab fa-google mr-1"></i>Best Free Option</div>
                    <p class="text-sm mt-1"><strong>Google Gemini</strong> - 1,500 requests/day FREE</p>
                    <p class="text-xs text-gray-500">Get key from Google AI Studio, no credit card needed</p>
                </div>
                <div class="bg-white rounded-lg p-3 border border-orange-200">
                    <div class="font-bold text-orange-600"><i class="fas fa-bolt mr-1"></i>Fastest Free Option</div>
                    <p class="text-sm mt-1"><strong>Groq</strong> - 30 requests/minute FREE</p>
                    <p class="text-xs text-gray-500">Extremely fast responses, no credit card needed</p>
                </div>
                <div class="bg-white rounded-lg p-3 border border-blue-200">
                    <div class="font-bold text-blue-600"><i class="fas fa-chart-line mr-1"></i>Backup Option</div>
                    <p class="text-sm mt-1"><strong>DeepSeek</strong> - 500k tokens FREE</p>
                    <p class="text-xs text-gray-500">Good fallback when others are busy</p>
                </div>
            </div>
            <div class="mt-3 p-2 bg-yellow-50 rounded text-sm">
                <i class="fas fa-lightbulb text-yellow-600 mr-1"></i>
                <strong>Tip:</strong> Enable multiple providers and set priority order. The chatbot will automatically try them in order until one works!
            </div>
        </div>
    </div>
</body>
</html>