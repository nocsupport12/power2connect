<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('components/db.php');

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM usr_tbl WHERE email = ?");
  if (!$stmt) die("DB error: " . $conn->error);

  $stmt->bind_param("s", $email);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    $role = strtolower($user['role']);
    $redirect = [
      'admin' => 'admin/index.php',
      'staff' => 'staff/index.php',
    ][$role] ?? false;

    if ($redirect) {
      header("Location: $redirect");
      exit;
    } else {
      $error_message = "There is a conflict on your account.";
    }
  } else {
    $error_message = $user ? "Invalid password." : "No user found with this email.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Login - Power2Connect</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  }
</style>
</head>

<body class="font-sans flex items-center justify-center min-h-screen p-4">
  <!-- Copyright -->
  <p class="fixed bottom-4 left-4 text-xs text-white/70">© 2026 SolarConnect</p>

  <!-- Main Login Card -->
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      
      <!-- Header with Logo -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-center">
        <div class="flex justify-center mb-3">
          <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas fa-solar-panel text-4xl text-white"></i>
          </div>
        </div>
        <h2 class="text-2xl font-bold text-white">Power2Connect</h2>
        <p class="text-white/80 text-sm mt-1">Solar & Internet</p>
      </div>

      <!-- Form Section -->
      <div class="p-6">
        <form method="POST" action="login.php">
          <?php if(!empty($error_message)): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-4 text-sm">
              <?= htmlspecialchars($error_message) ?>
            </div>
          <?php endif; ?>

          <!-- Email Field -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fas fa-envelope"></i>
              </span>
              <input 
                name="email"
                type="email" 
                placeholder="your@email.com" 
                required autofocus
                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fas fa-lock"></i>
              </span>
              <input 
                name="password"
                type="password" 
                placeholder="••••••••" 
                required
                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
              />
            </div>
          </div>

          <!-- Forgot Password -->
          <div class="text-right mb-4">
            <a href="forgot_password.php" class="text-sm text-blue-600 hover:text-blue-700">
              Forgot password?
            </a>
          </div>

          <!-- Login Button -->
          <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-800 text-white py-2.5 rounded-lg font-medium hover:from-blue-700 hover:to-blue-900 transition-colors">
            <i class="fas fa-sign-in-alt mr-2"></i>Login
          </button>
        </form>

        <!-- Footer Icons -->
        <div class="flex justify-center gap-6 mt-6 text-gray-400">
          <i class="fas fa-solar-panel"></i>
          <i class="fas fa-wifi"></i>
          <i class="fas fa-cloud-sun"></i>
        </div>
      </div>
    </div>
  </div>
</body>
</html>