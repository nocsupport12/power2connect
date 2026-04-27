<?php
// chat/check_keys.php - Check what API keys are in database
include("../components/db.php");

echo "<h2>API Keys in Database</h2>";

$providers = [
    'gemini_api_key' => 'Gemini',
    'openai_api_key' => 'OpenAI',
    'claude_api_key' => 'Claude',
    'groq_api_key' => 'Groq',
    'deepseek_api_key' => 'DeepSeek'
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Provider</th><th>Has Key?</th><th>Key Preview</th></tr>";

foreach ($providers as $key => $name) {
    $query = "SELECT setting_value, masked_value FROM system_settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $hasKey = !empty($row['setting_value']);
        $preview = $row['masked_value'] ?: (substr($row['setting_value'], 0, 15) . '...');
        $color = $hasKey ? 'green' : 'red';
        echo "<tr>";
        echo "<td><strong>$name</strong></td>";
        echo "<td style='color:$color'>" . ($hasKey ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td><code>" . htmlspecialchars($preview) . "</code></td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td><strong>$name</strong></td>";
        echo "<td style='color:red'>❌ No (not in table)</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Check provider order
echo "<h3>Provider Order (from api_provider_order)</h3>";
$orderQuery = "SELECT * FROM api_provider_order ORDER BY priority ASC";
$orderResult = mysqli_query($conn, $orderQuery);

if (mysqli_num_rows($orderResult) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Priority</th><th>Provider</th><th>Active</th></tr>";
    while ($row = mysqli_fetch_assoc($orderResult)) {
        $active = $row['is_active'] ? '✅ Active' : '❌ Inactive';
        echo "<tr>";
        echo "<td>{$row['priority']}</td>";
        echo "<td>{$row['provider']}</td>";
        echo "<td>$active</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No provider order set. Using default order.</p>";
}
?>