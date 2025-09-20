<?php
require_once __DIR__ . '/../src/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Not logged in";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = intval($_POST['post_id'] ?? 0);
    $value  = intval($_POST['value'] ?? 0); // 1 = like, -1 = dislike

    if ($postId > 0 && ($value === 1 || $value === -1)) {
        $stmt = $pdo->prepare("
            INSERT INTO post_likes (post_id, user_id, value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
        $stmt->execute([$postId, $_SESSION['user_id'], $value]);

        echo "ok";
    } else {
        http_response_code(400);
        echo "Invalid data";
    }
}
