<?php
class UploadHandler {
    private $allowed = ['image/jpeg','image/png','image/gif'];
    private $maxBytes = 2 * 1024 * 1024; // 2MB

    public function saveProfilePic($file) {
        return $this->save($file, 'profile_');
    }

    public function savePostImage($file) {
        return $this->save($file, 'post_');
    }

    private function save($file, $prefix) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $this->maxBytes) return null;

        // Use finfo to get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $this->allowed)) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safe = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $dest = UPLOAD_DIR . $safe;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/' . $safe; // path relative to public/
        }
        return null;
    }
}
