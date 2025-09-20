<?php
class User {
    private $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create($full_name, $email, $password, $age, $profile_pic=null) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name,email,password_hash,age,profile_pic) VALUES (?,?,?,?,?)");
        $stmt->execute([$full_name, $email, $hash, $age, $profile_pic]);
        return $this->pdo->lastInsertId();
    }

    public function getByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateProfile($id, $name, $age, $profile_pic=null) {
        if ($profile_pic) {
            $stmt = $this->pdo->prepare("UPDATE users SET full_name=?, age=?, profile_pic=? WHERE id=?");
            return $stmt->execute([$name, $age, $profile_pic, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET full_name=?, age=? WHERE id=?");
            return $stmt->execute([$name, $age, $id]);
        }
    }

    public function verifyPassword($email, $password) {
        $user = $this->getByEmail($email);
        if (!$user) return false;
        if (password_verify($password, $user['password_hash'])) return $user;
        return false;
    }
}
