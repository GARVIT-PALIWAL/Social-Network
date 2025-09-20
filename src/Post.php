<?php
class Post {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new post
     */
    public function create($userId, $imagePath, $description) {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (user_id, image, description, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $ok = $stmt->execute([$userId, $imagePath, $description]);
        return $ok ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Delete a post (only if the logged in user owns it)
     */
    public function delete($postId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        return $stmt->execute([$postId, $userId]);
    }

    /**
     * Add or update reaction (like/dislike)
     * value = 1 (like), -1 (dislike)
     */
    public function addReaction($postId, $userId, $value) {
        $stmt = $this->pdo->prepare("
            INSERT INTO post_likes (post_id, user_id, value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
        return $stmt->execute([$postId, $userId, $value]);
    }

    /**
     * Fetch all posts with user info, reaction counts, and comment counts
     */
    public function fetchAllWithUser() {
        $stmt = $this->pdo->query("
            SELECT p.*, 
                   u.full_name, 
                   u.profile_pic,
                   COALESCE(SUM(CASE WHEN pl.value = 1 THEN 1 ELSE 0 END), 0) AS likes,
                   COALESCE(SUM(CASE WHEN pl.value = -1 THEN 1 ELSE 0 END), 0) AS dislikes,
                   COALESCE(comment_counts.comment_count, 0) AS comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as comment_count 
                FROM comments 
                GROUP BY post_id
            ) comment_counts ON p.id = comment_counts.post_id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single user’s reaction to a post
     */
    public function getUserReaction($postId, $userId) {
        $stmt = $this->pdo->prepare("SELECT value FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? intval($row['value']) : 0; // 1 = like, -1 = dislike, 0 = no reaction
    }
}
