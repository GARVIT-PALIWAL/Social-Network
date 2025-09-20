<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/UploadHandler.php';

$userModel = new User($pdo);
$uploader = new UploadHandler();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $age = intval($_POST['age'] ?? 0);

    if (!$name) $errors[] = 'Name required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($age <= 0) $errors[] = 'Invalid age';

    if ($userModel->getByEmail($email)) $errors[] = 'Email already registered';

    $profilePath = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $profilePath = $uploader->saveProfilePic($_FILES['profile_pic']);
        if (!$profilePath) $errors[] = 'Profile image invalid or too large';
    }

    if (empty($errors)) {
        $id = $userModel->create($name, $email, $password, $age, $profilePath);
        $_SESSION['user_id'] = $id;
        header('Location: profile.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Signup - Social Network</title>
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="main-container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="modern-post-card">
          <div class="modern-post-content">
            <h2 class="mb-4 text-center">✨ Create Account</h2>
            
            <?php if(!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
              </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" id="signupForm">
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">👤 Full Name</label>
                <input type="text" class="form-control modern-input" name="full_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">📧 Email</label>
                <input type="email" class="form-control modern-input" name="email" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">🔒 Password</label>
                <input type="password" class="form-control modern-input" name="password" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">🎂 Age</label>
                <input type="number" class="form-control modern-input" name="age" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold text-muted mb-2">📷 Profile Picture</label>
                <div class="file-upload-wrapper">
                  <input type="file" class="file-upload-input" name="profile_pic" id="signup-profile-upload" accept="image/*">
                  <label for="signup-profile-upload" class="file-upload-label">
                    <span class="file-upload-icon">📷</span>
                    <span class="file-upload-text">Choose Photo</span>
                  </label>
                  <div class="file-name" id="signup-profile-name"></div>
                </div>
              </div>
              <button type="submit" class="modern-btn modern-btn-primary w-100">
                <span class="file-upload-icon">✨</span>
                <span class="file-upload-text">Create Account</span>
              </button>
            </form>
            <p class="text-center mt-3">Already registered? <a href="login.php">Login</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
