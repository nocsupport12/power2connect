<?php
// knowledge_admin.php
session_start();
include("../components/db.php");

// Protect admin page
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Start output buffering to prevent header errors
ob_start();

include("../components/admin_nav.php");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = "";
    $error = "";
    
    // ============= GREETING OPERATIONS =============
    
    // Add Greeting
    if (isset($_POST['add_greeting'])) {
        $question = mysqli_real_escape_string($conn, $_POST['question']);
        $answer = mysqli_real_escape_string($conn, $_POST['answer']);
        $keywords = mysqli_real_escape_string($conn, $_POST['keywords']);
        $language = mysqli_real_escape_string($conn, $_POST['language']);
        
        $sql = "INSERT INTO knowledge_base (category_id, question, answer, keywords, language) 
                VALUES (NULL, '$question', '$answer', '$keywords', '$language')";
        if (mysqli_query($conn, $sql)) {
            $greeting_id = mysqli_insert_id($conn);
            $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'ai_training_log'");
            if (mysqli_num_rows($checkTable) > 0) {
                mysqli_query($conn, "INSERT INTO ai_training_log (action, item_type, item_id, performed_by) VALUES ('add', 'greeting', $greeting_id, {$_SESSION['user_id']})");
            }
            $msg = "Greeting added successfully";
        } else {
            $error = mysqli_error($conn);
        }
    }
    
    // Update Greeting
    if (isset($_POST['update_greeting'])) {
        $id = (int)$_POST['id'];
        $question = mysqli_real_escape_string($conn, $_POST['question']);
        $answer = mysqli_real_escape_string($conn, $_POST['answer']);
        $keywords = mysqli_real_escape_string($conn, $_POST['keywords']);
        $language = mysqli_real_escape_string($conn, $_POST['language']);
        
        $sql = "UPDATE knowledge_base SET question='$question', answer='$answer', keywords='$keywords', language='$language' WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'ai_training_log'");
            if (mysqli_num_rows($checkTable) > 0) {
                mysqli_query($conn, "INSERT INTO ai_training_log (action, item_type, item_id, performed_by) VALUES ('edit', 'greeting', $id, {$_SESSION['user_id']})");
            }
            $msg = "Greeting updated successfully";
        } else {
            $error = mysqli_error($conn);
        }
    }
    
    // Delete Greeting
    if (isset($_POST['delete_greeting'])) {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM knowledge_base WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'ai_training_log'");
            if (mysqli_num_rows($checkTable) > 0) {
                mysqli_query($conn, "INSERT INTO ai_training_log (action, item_type, item_id, performed_by) VALUES ('delete', 'greeting', $id, {$_SESSION['user_id']})");
            }
            $msg = "Greeting deleted successfully";
        } else {
            $error = mysqli_error($conn);
        }
    }
    
    // Use JavaScript redirect instead of header()
    if (!empty($msg) || !empty($error)) {
        echo "<script>";
        if (!empty($msg)) {
            echo "window.location.href = 'knowledge_admin.php?msg=" . urlencode($msg) . "';";
        } else {
            echo "window.location.href = 'knowledge_admin.php?error=" . urlencode($error) . "';";
        }
        echo "</script>";
        exit;
    }
}

// Get all greetings (knowledge with no category)
$greetings = mysqli_query($conn, "
    SELECT * FROM knowledge_base 
    WHERE category_id IS NULL 
    ORDER BY language, question
");

// Get stats
$total_greetings = mysqli_query($conn, "SELECT COUNT(*) as count FROM knowledge_base WHERE category_id IS NULL")->fetch_assoc()['count'] ?? 0;
$en_greetings = mysqli_query($conn, "SELECT COUNT(*) as count FROM knowledge_base WHERE language='en' AND category_id IS NULL")->fetch_assoc()['count'] ?? 0;
$tl_greetings = mysqli_query($conn, "SELECT COUNT(*) as count FROM knowledge_base WHERE language='tl' AND category_id IS NULL")->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greeting Management - Power2Connect Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
          body {
    margin: 0; /* remove default body margin */
    height: 100vh; /* make body full height */
    background: linear-gradient(to right, #b3cde0, #e0f7fa);
    background-attachment: fixed; /* keeps gradient fixed on scroll */
}
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .greeting-card {
            transition: all 0.3s ease;
        }
        .greeting-card:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        
        <!-- Header with Stats -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Greeting Management</h1>
                    <p class="text-gray-600 mt-2">Manage your AI chatbot greetings and common phrases</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="openGreetingModal()" class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition-all flex items-center gap-2 shadow-md">
                        <i class="fas fa-hand-sparkles"></i> Add Greeting
                    </button>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-purple-100 text-xs">Total Greetings</p>
                    <p class="text-2xl font-bold"><?= $total_greetings ?></p>
                </div>
                <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-blue-100 text-xs">English Greetings</p>
                    <p class="text-2xl font-bold"><?= $en_greetings ?></p>
                </div>
                <div class="stat-card bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-4 text-white shadow-lg">
                    <p class="text-amber-100 text-xs">Tagalog Greetings</p>
                    <p class="text-2xl font-bold"><?= $tl_greetings ?></p>
                </div>
            </div>
        </div>

        <!-- Greetings List -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-hand-sparkles text-purple-600"></i>
                    All Greetings & Common Phrases
                </h2>
                <button onclick="openGreetingModal()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-plus"></i> Add Greeting
                </button>
            </div>
            
            <div class="space-y-4">
                <?php if (mysqli_num_rows($greetings) == 0): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-smile text-4xl mb-3 opacity-50"></i>
                    <p>No greetings yet. Click "Add Greeting" to create one.</p>
                </div>
                <?php else: ?>
                <?php while($greeting = mysqli_fetch_assoc($greetings)): ?>
                <div class="greeting-card border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all bg-white">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="text-2xl">💬</span>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $greeting['language'] == 'en' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' ?>">
                                    <?= strtoupper($greeting['language']) ?>
                                </span>
                                <span class="text-xs text-gray-400">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    <?= date('M d, Y', strtotime($greeting['created_at'])) ?>
                                </span>
                            </div>
                            <div class="mb-4">
                                <span class="text-sm text-gray-500 block mb-1">When user says:</span>
                                <p class="font-bold text-gray-900 text-lg">"<?= htmlspecialchars($greeting['question']) ?>"</p>
                            </div>
                            <div class="mb-3">
                                <span class="text-sm text-gray-500 block mb-1">Bot responds:</span>
                                <p class="text-gray-700 bg-gray-50 p-3 rounded-lg"><?= nl2br(htmlspecialchars($greeting['answer'])) ?></p>
                            </div>
                            <?php if ($greeting['keywords']): ?>
                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                <i class="fas fa-tags"></i>
                                <span><?= htmlspecialchars($greeting['keywords']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <!-- <button onclick="editGreeting(<?= $greeting['id'] ?>, '<?= htmlspecialchars(addslashes($greeting['question'])) ?>', '<?= htmlspecialchars(addslashes($greeting['answer'])) ?>', '<?= htmlspecialchars(addslashes($greeting['keywords'] ?? '')) ?>', '<?= $greeting['language'] ?>')" class="text-blue-600 hover:text-blue-800 bg-blue-50 p-2 rounded-lg transition-colors">
                                <i class="fas fa-edit"></i>
                            </button> -->
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Greeting Modal -->
    <div id="greetingModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 id="greetingModalTitle" class="text-2xl font-bold text-gray-800">Add Greeting</h2>
                <button type="button" onclick="closeModal('greetingModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" id="greetingForm">
                <input type="hidden" name="id" id="greetingId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-comment mr-1"></i> When user says:
                    </label>
                    <input type="text" name="question" id="greetingQuestion" required 
                           placeholder="e.g., hello, hi, good morning, kamusta"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">The phrase or question the user might type</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-robot mr-1"></i> Bot response:
                    </label>
                    <textarea name="answer" id="greetingAnswer" rows="4" required 
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    <p class="text-xs text-gray-500 mt-1">What the AI should respond with</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-tags mr-1"></i> Keywords (comma separated)
                    </label>
                    <input type="text" name="keywords" id="greetingKeywords" 
                           placeholder="hello, hi, hey, kamusta"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Optional: Related keywords for better matching</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-globe mr-1"></i> Language
                    </label>
                    <select name="language" id="greetingLanguage" required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="en">🇺🇸 English</option>
                        <option value="tl">🇵🇭 Tagalog</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeModal('greetingModal')" 
                            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="add_greeting" id="greetingSubmitBtn" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add Greeting
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
            resetForm();
        }
        
        function resetForm() {
            document.getElementById('greetingForm')?.reset();
            document.getElementById('greetingId').value = '';
            document.getElementById('greetingModalTitle').textContent = 'Add Greeting';
            document.getElementById('greetingSubmitBtn').innerHTML = '<i class="fas fa-plus"></i> Add Greeting';
            document.getElementById('greetingSubmitBtn').name = 'add_greeting';
        }
        
        // Greeting Functions
        function openGreetingModal() {
            resetForm();
            openModal('greetingModal');
        }
        
        function editGreeting(id, question, answer, keywords, language) {
            document.getElementById('greetingId').value = id;
            document.getElementById('greetingQuestion').value = question;
            document.getElementById('greetingAnswer').value = answer;
            document.getElementById('greetingKeywords').value = keywords || '';
            document.getElementById('greetingLanguage').value = language;
            document.getElementById('greetingModalTitle').textContent = 'Edit Greeting';
            document.getElementById('greetingSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Greeting';
            document.getElementById('greetingSubmitBtn').name = 'update_greeting';
            openModal('greetingModal');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal('greetingModal');
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal('greetingModal');
            }
        });
        
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
<?php 
ob_end_flush();
$conn->close(); 
?>