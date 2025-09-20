<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Post.php';
require_once __DIR__ . '/../src/Comment.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$userModel = new User($pdo);
$postModel = new Post($pdo);
$commentModel = new Comment($pdo);
$user = $userModel->getById($_SESSION['user_id']);
$posts = $postModel->fetchAllWithUser();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Profile - <?=htmlspecialchars($user['full_name'])?></title>
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Hello, <?=htmlspecialchars($user['full_name'])?></h2>
      <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>

    <!-- Profile Card -->
    <div class="card mb-4 shadow-sm border-0 rounded-4 overflow-hidden">
  <div class="bg-primary text-white p-4">
    <div class="d-flex align-items-center">
      <img src="<?=htmlspecialchars($user['profile_pic'] ?: 'assets/default.png')?>" 
           class="rounded-circle border border-3 border-white me-3" 
           style="width:100px;height:100px;object-fit:cover;">
      <div>
        <h3 class="mb-0"><?=htmlspecialchars($user['full_name'])?></h3>
        <p class="mb-1"><?=htmlspecialchars($user['email'])?></p>
        <small>Age: <?=htmlspecialchars($user['age'])?></small>
      </div>
    </div>
  </div>
</div>


    <!-- Profile Edit -->
    <div class="card mb-4 profile-edit-section">
      <div class="card-body">
        <h5>✏️ Edit Profile</h5>
        <form id="updateProfileForm" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted mb-2">Full Name</label>
              <input type="text" name="name" class="form-control modern-input" value="<?=htmlspecialchars($user['full_name'])?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold text-muted mb-2">Age</label>
              <input type="number" name="age" class="form-control modern-input" value="<?=htmlspecialchars($user['age'])?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold text-muted mb-2">Profile Picture</label>
              <div class="file-upload-wrapper">
                <input type="file" name="profile_pic" class="file-upload-input" id="profile-pic-upload" accept="image/*">
                <label for="profile-pic-upload" class="file-upload-label">
                  <span class="file-upload-icon">📷</span>
                  <span class="file-upload-text">Choose Photo</span>
                </label>
                <div class="file-name" id="profile-pic-name"></div>
              </div>
            </div>
          </div>
          <div class="text-end mt-4">
            <button type="submit" class="modern-btn modern-btn-primary">
              <span class="file-upload-icon">💾</span>
              <span class="file-upload-text">Update Profile</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Post -->
    <div class="card mb-4 create-post-section">
      <div class="card-body">
        <h5>📝 Create Post</h5>
        <form id="addPostForm" enctype="multipart/form-data">
          <div class="modern-form-row">
            <label class="form-label fw-semibold text-muted mb-2">What's on your mind?</label>
            <textarea name="description" class="form-control modern-textarea" placeholder="Share your thoughts, ideas, or experiences..." rows="4"></textarea>
          </div>
          <div class="modern-form-row">
            <label class="form-label fw-semibold text-muted mb-2">Add an Image (Optional)</label>
            <div class="file-upload-wrapper">
              <input type="file" name="image" class="file-upload-input" id="post-image-upload" accept="image/*">
              <label for="post-image-upload" class="file-upload-label">
                <span class="file-upload-icon">🖼️</span>
                <span class="file-upload-text">Choose Image</span>
              </label>
              <div class="file-name" id="post-image-name"></div>
            </div>
          </div>
          <div class="text-end">
            <button type="submit" class="modern-btn modern-btn-success">
              <span class="file-upload-icon">🚀</span>
              <span class="file-upload-text">Share Post</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Posts Feed -->
    <div class="posts-feed-section">
      <h4 class="posts-feed-title">📱 All Posts</h4>
      <div class="row">
        <?php foreach($posts as $p): 
            $reaction = $postModel->getUserReaction($p['id'], $_SESSION['user_id']);
        ?>
          <div class="col-md-6 mb-4">
            <div class="modern-post-card" id="post-<?=$p['id']?>">
              <div class="modern-post-header">
                <div class="modern-post-author">
                  <img src="<?=htmlspecialchars($p['profile_pic'] ?: 'assets/default.png')?>" class="modern-post-avatar">
                  <div class="modern-post-info">
                    <h6><?=htmlspecialchars($p['full_name'])?></h6>
                    <p class="modern-post-time"><?=htmlspecialchars($p['created_at'])?></p>
                  </div>
                </div>
                <?php if ($p['user_id'] == $_SESSION['user_id']): ?>
                  <button class="modern-post-delete delete-post" data-id="<?=$p['id']?>">🗑️ Delete</button>
                <?php endif; ?>
              </div>
              <div class="modern-post-content">
                <?php if ($p['image']): ?>
                  <img src="<?=htmlspecialchars($p['image'])?>" class="modern-post-image mb-3">
                <?php endif; ?>
                <p class="modern-post-text"><?=nl2br(htmlspecialchars($p['description']))?></p>
              </div>
            <div class="post-actions">
              <button class="btn btn-like react-btn <?= $reaction === 1 ? 'active' : '' ?>" data-id="<?=$p['id']?>" data-val="1">
                👍 <span><?=$p['likes']?></span>
              </button>
              <button class="btn btn-dislike react-btn <?= $reaction === -1 ? 'active' : '' ?>" data-id="<?=$p['id']?>" data-val="-1">
                👎 <span><?=$p['dislikes']?></span>
              </button>
              <button class="btn btn-comment comment-toggle" data-id="<?=$p['id']?>">
                💬 <span><?=$p['comment_count']?></span>
              </button>
            </div>

            <!-- Comments Section -->
            <div class="comments-section" id="comments-<?=$p['id']?>">
              <div class="comment-form">
                <textarea class="form-control comment-text" placeholder="Write a comment..." rows="2"></textarea>
                <button class="comment-submit" data-id="<?=$p['id']?>">Post Comment</button>
              </div>
              <div class="comments-list" id="comments-list-<?=$p['id']?>">
                <!-- Comments will be loaded here via AJAX -->
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/css/js/main.js"></script>
</body>
</html>
