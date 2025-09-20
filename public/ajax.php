<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Post.php';
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Comment.php';
require_once __DIR__ . '/../src/UploadHandler.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Not authenticated']);
    exit;
}

$action = $_POST['action'] ?? '';
$postModel = new Post($pdo);
$userModel = new User($pdo);
$commentModel = new Comment($pdo);
$uploader = new UploadHandler();
$uid = $_SESSION['user_id'];

switch ($action) {
    case 'add_post':
        $desc = trim($_POST['description'] ?? '');
        $imgPath = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imgPath = $uploader->savePostImage($_FILES['image']);
            if (!$imgPath) {
                echo json_encode(['success' => false, 'msg' => 'Invalid image']);
                exit;
            }
        }

        $id = $postModel->create($uid, $imgPath, $desc);
        echo json_encode(['success' => (bool)$id, 'post_id' => $id]);
        break;

    case 'delete_post':
        $post_id = intval($_POST['post_id'] ?? 0);
        $ok = $postModel->delete($post_id, $uid);
        echo json_encode(['success' => $ok]);
        break;

    case 'react':
        $post_id = intval($_POST['post_id'] ?? 0);
        $value = intval($_POST['value'] ?? 0);
        
        // Debug logging
        error_log("React action: post_id=$post_id, value=$value, user_id=$uid");
        
        if (!in_array($value, [1, -1, 0])) {
            echo json_encode(['success' => false, 'msg' => 'Invalid reaction']);
            exit;
        }
        
        if ($value === 0) {
            // Remove reaction
            $stmt = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
            $ok = $stmt->execute([$post_id, $uid]);
            error_log("Removed reaction: " . ($ok ? 'success' : 'failed'));
        } else {
            // Add or update reaction
            $ok = $postModel->addReaction($post_id, $uid, $value);
            error_log("Added reaction: " . ($ok ? 'success' : 'failed'));
        }
        echo json_encode(['success' => $ok]);
        break;

    case 'update_profile':
        $name = trim($_POST['name'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $profilePath = null;

        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
            $profilePath = $uploader->saveProfilePic($_FILES['profile_pic']);
        }

        $ok = $userModel->updateProfile($uid, $name, $age, $profilePath);
        echo json_encode(['success' => $ok]);
        break;

    case 'add_comment':
        $postId = intval($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'msg' => 'Comment cannot be empty']);
            exit;
        }
        
        $commentId = $commentModel->create($postId, $uid, $content);
        if ($commentId) {
            $comment = $commentModel->getById($commentId);
            echo json_encode(['success' => true, 'comment' => $comment]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Failed to add comment']);
        }
        break;

    case 'get_comments':
        $postId = intval($_POST['post_id'] ?? 0);
        $comments = $commentModel->getByPostId($postId);
        echo json_encode(['success' => true, 'comments' => $comments]);
        break;

    case 'delete_comment':
        $commentId = intval($_POST['comment_id'] ?? 0);
        $ok = $commentModel->delete($commentId, $uid);
        echo json_encode(['success' => $ok]);
        break;

    case 'get_post_counts':
        $postId = intval($_POST['post_id'] ?? 0);
        
        // Get like/dislike counts
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END), 0) AS likes,
                COALESCE(SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END), 0) AS dislikes
            FROM post_likes 
            WHERE post_id = ?
        ");
        $stmt->execute([$postId]);
        $reactions = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get comment count
        $commentCount = $commentModel->getCountByPostId($postId);
        
        echo json_encode([
            'success' => true,
            'likes' => intval($reactions['likes']),
            'dislikes' => intval($reactions['dislikes']),
            'comments' => $commentCount
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'msg' => 'Unknown action']);
        break;
}
