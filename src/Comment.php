<?php
class Comment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Add a new comment to a post
     */
    public function create($postId, $userId, $content) {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (post_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $ok = $stmt->execute([$postId, $userId, $content]);
        return $ok ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Delete a comment (only if the logged in user owns it)
     */
    public function delete($commentId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        return $stmt->execute([$commentId, $userId]);
    }

    /**
     * Get all comments for a specific post with user information
     */
    public function getByPostId($postId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.full_name, u.profile_pic
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get comment count for a post
     */
    public function getCountByPostId($postId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['count']);
    }

    /**
     * Get a single comment by ID with user information
     */
    public function getById($commentId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.full_name, u.profile_pic
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$commentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
