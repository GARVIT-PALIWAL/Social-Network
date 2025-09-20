<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/User.php';

$userModel = new User($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $user = $userModel->verifyPassword($email, $password);
    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        header('Location: profile.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Social Network</title>
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="main-container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="modern-post-card">
          <div class="modern-post-content">
            <h2 class="mb-4 text-center">🔐 Login</h2>
            
            <?php if ($error): ?>
              <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">📧 Email</label>
                <input type="email" class="form-control modern-input" name="email" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">🔒 Password</label>
                <input type="password" class="form-control modern-input" name="password" required>
              </div>
              <button type="submit" class="modern-btn modern-btn-primary w-100">
                <span class="file-upload-icon">🚀</span>
                <span class="file-upload-text">Login</span>
              </button>
            </form>
            <p class="text-center mt-3">New here? <a href="signup.php">Create an account</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
