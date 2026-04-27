<?php
// chat/gemini_api.php - CLEAN RESPONSES ONLY (No drafts, no reasoning)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Function to get database connection
function getDBConnection() {
    $paths = [
        __DIR__ . '/../components/db.php',
        __DIR__ . '/components/db.php',
        dirname(__DIR__) . '/components/db.php',
        $_SERVER['DOCUMENT_ROOT'] . '/components/db.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            include($path);
            return $conn;
        }
    }
    
    $conn = mysqli_connect('localhost', 'root', '', 'power2connect');
    return $conn;
}

// Get API keys from database
function getApiKeysFromDB() {
    $conn = getDBConnection();
    
    $keys = [
        'gemini' => '',
        'openai' => '',
        'claude' => '',
        'groq' => '',
        'deepseek' => ''
    ];
    
    $query = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('gemini_api_key', 'openai_api_key', 'claude_api_key', 'groq_api_key', 'deepseek_api_key')";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            switch ($row['setting_key']) {
                case 'gemini_api_key': $keys['gemini'] = $row['setting_value']; break;
                case 'openai_api_key': $keys['openai'] = $row['setting_value']; break;
                case 'claude_api_key': $keys['claude'] = $row['setting_value']; break;
                case 'groq_api_key': $keys['groq'] = $row['setting_value']; break;
                case 'deepseek_api_key': $keys['deepseek'] = $row['setting_value']; break;
            }
        }
        mysqli_free_result($result);
    }
    
    mysqli_close($conn);
    return $keys;
}

// Extract only the final response, remove drafts
function extractFinalResponse($text) {
    // Remove any "Draft X:" patterns and keep only the last one
    if (preg_match_all('/Draft \d+:(.*?)(?=Draft \d+:|$)/s', $text, $matches)) {
        $lastDraft = trim(end($matches[1]));
        $lastDraft = preg_replace('/\s*\(3 sentences\)\s*$/i', '', $lastDraft);
        $lastDraft = preg_replace('/\s*\(2-3 sentences\)\s*$/i', '', $lastDraft);
        return $lastDraft;
    }
    
    // Remove "Option X:" patterns
    if (preg_match_all('/Option \d+:(.*?)(?=Option \d+:|$)/s', $text, $matches)) {
        $lastOption = trim(end($matches[1]));
        return $lastOption;
    }
    
    // Remove reasoning patterns
    $patterns = [
        '/^The user.*?\.\s*/im',
        '/^As a solar.*?\.\s*/im',
        '/^As an internet.*?\.\s*/im',
        '/^As a Power2Connect assistant.*?\.\s*/im',
        '/^I should.*?\.\s*/im',
        '/^I am a.*?\.\s*/im',
        '/^Let me.*?\.\s*/im',
        '/Friendly\?.*$/im',
        '/Direct\?.*$/im',
        '/No reasoning\?.*$/im',
        '/2-3 sentences\?.*$/im',
        '/^Role:.*$/im',
        '/^Constraint.*$/im',
    ];
    
    foreach ($patterns as $pattern) {
        $text = preg_replace($pattern, '', $text);
    }
    
    // Take the last paragraph
    $paragraphs = preg_split('/\n\s*\n/', $text);
    $text = trim(end($paragraphs));
    
    return $text;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['prompt'])) {
    echo json_encode(['success' => false, 'error' => 'Missing prompt parameter']);
    exit();
}

$userMessage = trim($input['prompt']);
$session_id = isset($input['session_id']) ? $input['session_id'] : 'unknown';

$apiKeys = getApiKeysFromDB();

// STRICT system instruction - NO DRAFTS
$systemInstruction = "You are a friendly Power2Connect assistant specializing in solar energy and internet services. 
CRITICAL RULES:
- NEVER generate multiple drafts or options.
- NEVER use 'Draft 1:', 'Draft 2:', or 'Option 1:'.
- Output ONLY ONE final response directly.
- Keep responses to 2-3 sentences.
- Be helpful and conversational.

Example: 'For solar panel installation, we offer free site surveys to assess your roof. Our fiber internet plans start at 50 Mbps with unlimited data.'";

// Try providers in order
$providersToTry = [];
if (!empty($apiKeys['groq'])) $providersToTry[] = 'groq';
if (!empty($apiKeys['gemini'])) $providersToTry[] = 'gemini';
if (!empty($apiKeys['deepseek'])) $providersToTry[] = 'deepseek';
if (!empty($apiKeys['openai'])) $providersToTry[] = 'openai';
if (!empty($apiKeys['claude'])) $providersToTry[] = 'claude';

$aiText = null;
$providerUsed = null;

foreach ($providersToTry as $provider) {
    $result = callProvider($provider, $apiKeys[$provider], $userMessage, $systemInstruction);
    if ($result['success']) {
        $aiText = $result['response'];
        $providerUsed = $provider;
        break;
    }
}

if ($aiText) {
    // Clean the response - EXTRACT ONLY FINAL RESPONSE
    $aiText = trim($aiText);
    $aiText = extractFinalResponse($aiText);
    $aiText = preg_replace('/^Assistant:\s*/i', '', $aiText);
    $aiText = preg_replace('/\*{1,2}/', '', $aiText);
    $aiText = preg_replace('/^["\']|["\']$/u', '', $aiText);
    
    if (empty($aiText) || strlen($aiText) < 5) {
        $aiText = getSmartFallback($userMessage);
    }
    
    logToDatabase($session_id, $userMessage, $aiText, $providerUsed);
    
    echo json_encode([
        'success' => true,
        'response' => $aiText,
        'provider' => $providerUsed
    ]);
} else {
    $fallback = getSmartFallback($userMessage);
    echo json_encode([
        'success' => true,
        'response' => $fallback,
        'provider' => 'fallback'
    ]);
}

function callProvider($provider, $apiKey, $message, $systemInstruction) {
    switch ($provider) {
        case 'groq': return callGroq($apiKey, $message, $systemInstruction);
        case 'gemini': return callGemini($apiKey, $message, $systemInstruction);
        case 'deepseek': return callDeepSeek($apiKey, $message, $systemInstruction);
        case 'openai': return callOpenAI($apiKey, $message, $systemInstruction);
        case 'claude': return callClaude($apiKey, $message, $systemInstruction);
        default: return ['success' => false];
    }
}

// GROQ API
function callGroq($apiKey, $message, $systemInstruction) {
    $payload = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 150,
        'temperature' => 0.3
    ];
    
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return ['success' => true, 'response' => $data['choices'][0]['message']['content']];
        }
    }
    return ['success' => false];
}

// GEMINI API
function callGemini($apiKey, $message, $systemInstruction) {
    $model = 'gemma-4-31b-it';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    
    $fullPrompt = $systemInstruction . "\n\nUser: " . $message . "\nAssistant:";
    
    $payload = [
        'contents' => [
            ['parts' => [['text' => $fullPrompt]]]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 150,
            'temperature' => 0.3,
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => true, 'response' => $data['candidates'][0]['content']['parts'][0]['text']];
        }
    }
    return ['success' => false];
}

// DEEPSEEK API
function callDeepSeek($apiKey, $message, $systemInstruction) {
    $payload = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 150,
        'temperature' => 0.3
    ];
    
    $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return ['success' => true, 'response' => $data['choices'][0]['message']['content']];
        }
    }
    return ['success' => false];
}

// OPENAI API
function callOpenAI($apiKey, $message, $systemInstruction) {
    $payload = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 150,
        'temperature' => 0.3
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return ['success' => true, 'response' => $data['choices'][0]['message']['content']];
        }
    }
    return ['success' => false];
}

// CLAUDE API
function callClaude($apiKey, $message, $systemInstruction) {
    $payload = [
        'model' => 'claude-3-haiku-20240307',
        'max_tokens' => 150,
        'temperature' => 0.3,
        'system' => $systemInstruction,
        'messages' => [['role' => 'user', 'content' => $message]]
    ];
    
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['content'][0]['text'])) {
            return ['success' => true, 'response' => $data['content'][0]['text']];
        }
    }
    return ['success' => false];
}

// Log to database
function logToDatabase($session_id, $user_message, $ai_response, $provider) {
    $conn = getDBConnection();
    
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS ai_usage_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(100),
        user_message TEXT,
        ai_response TEXT,
        tokens_estimated INT DEFAULT 0,
        source VARCHAR(30) DEFAULT 'unknown',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $tokens = round(strlen($ai_response) / 4);
    
    $query = "INSERT INTO ai_usage_log (session_id, user_message, ai_response, tokens_estimated, source) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssis", $session_id, $user_message, $ai_response, $tokens, $provider);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Smart fallback responses - UPDATED FOR SOLAR AND INTERNET
function getSmartFallback($message) {
    $lower = strtolower(trim($message));
    
    // Solar-related responses
    if (strpos($lower, 'solar') !== false || strpos($lower, 'panel') !== false || strpos($lower, 'sun') !== false) {
        return "We offer high-quality solar panel installation with free site surveys. Our solar packages include inverters and optional battery storage for energy independence.";
    }
    
    if (strpos($lower, 'battery') !== false || strpos($lower, 'storage') !== false) {
        return "Our solar battery storage solutions keep your home powered during outages and at night. We offer lithium-ion batteries with 10-year warranties.";
    }
    
    if (strpos($lower, 'install') !== false && (strpos($lower, 'solar') !== false || strpos($lower, 'panel') !== false)) {
        return "Solar installation typically takes 2-3 days depending on system size. We handle all permits and inspections for a hassle-free experience.";
    }
    
    if (strpos($lower, 'cost') !== false || strpos($lower, 'price') !== false || strpos($lower, 'how much') !== false) {
        return "Solar system costs vary based on size and equipment. We offer flexible financing options and can provide a free quote after a site assessment.";
    }
    
    if (strpos($lower, 'net metering') !== false || strpos($lower, 'bill') !== false) {
        return "With net metering, excess solar energy is sent to the grid and credited to your bill. This can significantly reduce or eliminate your monthly electricity costs.";
    }
    
    // Internet-related responses
    if (strpos($lower, 'internet') !== false || strpos($lower, 'wifi') !== false) {
        return "Power2Connect offers high-speed fiber internet with plans from 50 Mbps to 1 Gbps. All plans include unlimited data and free router installation.";
    }
    
    if (strpos($lower, 'fiber') !== false) {
        return "Our fiber internet provides reliable, high-speed connectivity perfect for streaming, gaming, and remote work. Check availability in your area today!";
    }
    
    if (strpos($lower, 'speed') !== false || strpos($lower, 'slow') !== false || strpos($lower, 'lag') !== false) {
        return "Slow internet can be frustrating. Try restarting your router first. If issues persist, our technical support team can run diagnostics on your connection.";
    }
    
    if (strpos($lower, 'connection') !== false && strpos($lower, 'no') !== false) {
        return "If you have no internet connection, please check if all cables are secure and the router lights are on. Contact our support team if the issue continues.";
    }
    
    if (strpos($lower, 'router') !== false) {
        return "We provide a free high-performance router with all fiber internet plans. Our dual-band routers ensure strong WiFi coverage throughout your home.";
    }
    
    if (strpos($lower, 'plan') !== false || strpos($lower, 'package') !== false) {
        return "We offer internet plans starting at 50 Mbps for basic use, 200 Mbps for families, and 1 Gbps for power users. Solar bundles are also available for combined savings!";
    }
    
    if (strpos($lower, 'availability') !== false || strpos($lower, 'available') !== false) {
        return "Service availability depends on your location. Please provide your address or contact our support team to check if fiber internet is available in your area.";
    }
    
    if (strpos($lower, 'data') !== false || strpos($lower, 'cap') !== false) {
        return "All Power2Connect internet plans include unlimited data with no caps or throttling. Stream, game, and work without worrying about data limits.";
    }
    
    // General inquiries
    if (strpos($lower, 'appointment') !== false || strpos($lower, 'schedule') !== false) {
        return "You can schedule a free site survey or installation appointment by clicking 'Talk to Human Agent' or calling our support line. We'll find a time that works for you.";
    }
    
    if (strpos($lower, 'support') !== false || strpos($lower, 'help') !== false) {
        return "Our support team is available to help with solar or internet issues. Click 'Talk to Human Agent' to connect with a specialist who can assist you.";
    }
    
    // Greeting responses
    if (preg_match('/\b(hi|hello|hey)\b/i', $lower)) {
        return "Hello! Welcome to Power2Connect. How can I help with your solar or internet needs today?";
    }
    
    // Default response
    return "I'm here to help with Power2Connect solar and internet services. What would you like to know about our solar panels, battery storage, or fiber internet plans?";
}
?>