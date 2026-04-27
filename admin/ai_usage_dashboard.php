<?php
// admin/ai_usage_dashboard.php - WITH MULTI-PROVIDER SUPPORT (FIXED)
session_start();
include("../components/db.php");

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include("../components/admin_nav.php");

// Check if table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'ai_usage_log'");
$tableExists = mysqli_num_rows($tableCheck) > 0;

if (!$tableExists) {
    $createTable = "CREATE TABLE IF NOT EXISTS ai_usage_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(100),
        user_message TEXT,
        ai_response TEXT,
        tokens_estimated INT DEFAULT 0,
        response_time FLOAT DEFAULT 0,
        source VARCHAR(30) DEFAULT 'unknown',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_source (source)
    )";
    mysqli_query($conn, $createTable);
} else {
    // Update table to support multiple providers - CHECK IF COLUMNS/INDEXES EXIST FIRST
    $alterQueries = [
        "ALTER TABLE ai_usage_log MODIFY COLUMN source VARCHAR(30) DEFAULT 'unknown'",
    ];
    
    foreach ($alterQueries as $query) {
        // Check if the column modification is needed
        $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM ai_usage_log LIKE 'source'");
        $columnInfo = mysqli_fetch_assoc($checkColumn);
        if ($columnInfo && $columnInfo['Type'] !== 'varchar(30)') {
            @mysqli_query($conn, $query);
        }
    }
    
    // Add index only if it doesn't exist
    $checkIndex = mysqli_query($conn, "SHOW INDEX FROM ai_usage_log WHERE Key_name = 'idx_provider'");
    if (mysqli_num_rows($checkIndex) == 0) {
        @mysqli_query($conn, "ALTER TABLE ai_usage_log ADD INDEX idx_provider (source)");
    }
}

// Get usage statistics for all providers
$totalQueries = 0;
$geminiQueries = 0;
$openaiQueries = 0;
$claudeQueries = 0;
$groqQueries = 0;
$deepseekQueries = 0;
$dbQueries = 0;
$localQueries = 0;
$totalTokens = 0;
$avgResponseTime = 0;

if ($tableExists) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log");
    if ($result) $totalQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='gemini'");
    if ($result) $geminiQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='openai'");
    if ($result) $openaiQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='claude'");
    if ($result) $claudeQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='groq'");
    if ($result) $groqQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='deepseek'");
    if ($result) $deepseekQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='database'");
    if ($result) $dbQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM ai_usage_log WHERE source='local' OR source='fallback'");
    if ($result) $localQueries = $result->fetch_assoc()['count'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT SUM(tokens_estimated) as total FROM ai_usage_log WHERE source IN ('gemini', 'openai', 'claude', 'groq', 'deepseek')");
    if ($result) $totalTokens = $result->fetch_assoc()['total'] ?? 0;
    
    $result = mysqli_query($conn, "SELECT AVG(response_time) as avg FROM ai_usage_log");
    if ($result) $avgResponseTime = $result->fetch_assoc()['avg'] ?? 0;
}

// Get daily usage for chart (last 14 days)
$dailyUsage = false;
if ($tableExists) {
    $dailyUsage = mysqli_query($conn, "
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total,
            SUM(CASE WHEN source='gemini' THEN 1 ELSE 0 END) as gemini,
            SUM(CASE WHEN source='openai' THEN 1 ELSE 0 END) as openai,
            SUM(CASE WHEN source='claude' THEN 1 ELSE 0 END) as claude,
            SUM(CASE WHEN source='groq' THEN 1 ELSE 0 END) as groq,
            SUM(CASE WHEN source='deepseek' THEN 1 ELSE 0 END) as deepseek,
            SUM(CASE WHEN source='database' THEN 1 ELSE 0 END) as db_count,
            SUM(CASE WHEN source='local' OR source='fallback' THEN 1 ELSE 0 END) as local_count
        FROM ai_usage_log 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
}

// Get recent logs
$recentLogs = false;
if ($tableExists) {
    $recentLogs = mysqli_query($conn, "
        SELECT * FROM ai_usage_log 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
}

// Get provider breakdown for pie chart
$providerBreakdown = [
    'gemini' => $geminiQueries,
    'openai' => $openaiQueries,
    'claude' => $claudeQueries,
    'groq' => $groqQueries,
    'deepseek' => $deepseekQueries,
    'database' => $dbQueries,
    'local' => $localQueries
];
$providerBreakdown = array_filter($providerBreakdown, function($v) { return $v > 0; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Usage Dashboard - Power2Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
      body {
    margin: 0; /* remove default body margin */
    height: 100vh; /* make body full height */
    background: linear-gradient(to right, #b3cde0, #e0f7fa);
    background-attachment: fixed; /* keeps gradient fixed on scroll */
}   
</style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-grey-800 flex">
                AI Usage Dashboard
            </h1>
            <div class="space-x-2">
                <!-- <a href="gemini_settings.php" class="bg-purple-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-purple-700">
                    <i class="fas fa-key mr-1"></i>API Settings
                </a> -->
                <a href="knowledge_admin.php" class="bg-gray-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-gray-700">
                    <i class="fas fa-database mr-1"></i>Knowledge Base
                </a>
            </div>
        </div>
        
        <!-- Stats Cards - Multi-Provider -->
        <!-- <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs">Total</h3>
                <p class="text-xl font-bold"><?= number_format($totalQueries) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs"><i class="fab fa-google text-blue-500 mr-1"></i>Gemini</h3>
                <p class="text-xl font-bold text-blue-600"><?= number_format($geminiQueries) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs"><i class="fab fa-openid text-green-500 mr-1"></i>OpenAI</h3>
                <p class="text-xl font-bold text-green-600"><?= number_format($openaiQueries) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs"><i class="fas fa-brain text-purple-500 mr-1"></i>Claude</h3>
                <p class="text-xl font-bold text-purple-600"><?= number_format($claudeQueries) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs"><i class="fas fa-bolt text-orange-500 mr-1"></i>Groq</h3>
                <p class="text-xl font-bold text-orange-600"><?= number_format($groqQueries) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs"><i class="fas fa-database text-green-500 mr-1"></i>KB</h3>
                <p class="text-xl font-bold text-green-600"><?= number_format($dbQueries) ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-3">
                <h3 class="text-gray-500 text-xs"><i class="fas fa-server text-gray-500 mr-1"></i>Local</h3>
                <p class="text-xl font-bold text-gray-600"><?= number_format($localQueries) ?></p>
            </div>
        </div> -->
        

        
        <!-- Recent Logs Table -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-bold mb-3">Recent AI Interactions</h2>
            <?php if ($recentLogs && mysqli_num_rows($recentLogs) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 text-left">Time</th>
                            <th class="p-2 text-left">User Message</th>
                            <th class="p-2 text-left">Response Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($log = mysqli_fetch_assoc($recentLogs)): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-2 text-xs"><?= date('M d, H:i:s', strtotime($log['created_at'])) ?></td>
                            <td class="p-2 max-w-[150px] truncate text-xs" title="<?= htmlspecialchars($log['user_message']) ?>"><?= htmlspecialchars(substr($log['user_message'], 0, 40)) ?>...</td>
                            <td class="p-2 max-w-[200px] truncate text-xs" title="<?= htmlspecialchars($log['ai_response']) ?>"><?= htmlspecialchars(substr($log['ai_response'], 0, 50)) ?>...</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-6 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p class="text-sm">No recent activity logs.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        <?php if ($dailyUsage && mysqli_num_rows($dailyUsage) > 0): ?>
        // Line Chart Data
        const dates = [];
        const geminiData = [];
        const openaiData = [];
        const groqData = [];
        const databaseData = [];
        const localData = [];
        
        <?php 
        mysqli_data_seek($dailyUsage, 0);
        $chartDates = [];
        $chartGemini = [];
        $chartOpenAI = [];
        $chartGroq = [];
        $chartDatabase = [];
        $chartLocal = [];
        while($row = mysqli_fetch_assoc($dailyUsage)) {
            $chartDates[] = "'" . $row['date'] . "'";
            $chartGemini[] = $row['gemini'];
            $chartOpenAI[] = $row['openai'];
            $chartGroq[] = $row['groq'];
            $chartDatabase[] = $row['db_count'];
            $chartLocal[] = $row['local_count'];
        }
        ?>
        
        const allDates = [<?php echo implode(',', array_reverse($chartDates)); ?>];
        
        new Chart(document.getElementById('usageChart'), {
            type: 'line',
            data: {
                labels: allDates,
                datasets: [
                    {
                        label: 'Gemini',
                        data: [<?php echo implode(',', array_reverse($chartGemini)); ?>],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'OpenAI',
                        data: [<?php echo implode(',', array_reverse($chartOpenAI)); ?>],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Groq',
                        data: [<?php echo implode(',', array_reverse($chartGroq)); ?>],
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Database',
                        data: [<?php echo implode(',', array_reverse($chartDatabase)); ?>],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Local',
                        data: [<?php echo implode(',', array_reverse($chartLocal)); ?>],
                        borderColor: '#6b7280',
                        backgroundColor: 'rgba(107, 114, 128, 0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 10 } } },
                    x: { ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 45 } }
                }
            }
        });
        <?php endif; ?>
        
        <?php if (!empty($providerBreakdown)): ?>
        // Pie Chart Data
        new Chart(document.getElementById('providerChart'), {
            type: 'pie',
            data: {
                labels: [
                    <?php 
                    $labels = [];
                    $values = [];
                    foreach ($providerBreakdown as $provider => $count) {
                        $displayName = ucfirst($provider);
                        if ($provider === 'gemini') $displayName = 'Gemini';
                        if ($provider === 'openai') $displayName = 'OpenAI';
                        if ($provider === 'claude') $displayName = 'Claude';
                        if ($provider === 'groq') $displayName = 'Groq';
                        if ($provider === 'deepseek') $displayName = 'DeepSeek';
                        if ($provider === 'database') $displayName = 'Knowledge Base';
                        if ($provider === 'local') $displayName = 'Local AI';
                        $labels[] = "'{$displayName}'";
                        $values[] = $count;
                    }
                    echo implode(',', $labels);
                    ?>
                ],
                datasets: [{
                    data: [<?php echo implode(',', $values); ?>],
                    backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f97316', '#14b8a6', '#059669', '#6b7280'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'right', labels: { font: { size: 11 } } },
                    tooltip: { callbacks: { label: function(context) { return context.label + ': ' + context.raw + ' requests'; } } }
                }
            }
        });
        <?php endif; ?>
        
        function refreshData() { window.location.reload(); }
        function exportData() { window.location.href = 'export_usage.php'; }
        function clearOldData() { if (confirm('⚠️ Clear old data? This keeps the last 30 days.')) { window.location.href = 'clear_usage.php'; } }
    </script>
</body>
</html>