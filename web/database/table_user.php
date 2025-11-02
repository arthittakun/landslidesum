<?php
require_once __DIR__ . '/connect.php';

class Table_user
{
    private $conn;

    public function __construct()
    {
        $this->conn = new database();
    }

    public function getAllUsers($include_deleted = false)
    {
        try {
            if ($include_deleted) {
                $sql = "SELECT * FROM users ORDER BY id";
            } else {
                $sql = "SELECT * FROM users WHERE void = 0 ORDER BY id";
            }
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get all users error: ' . $e->getMessage());
            return [];
        }
    }

    public function getUserById($id)
    {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get user by ID error: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserByUsername($username)
    {
        try {
            $sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get user by username error: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail($email)
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get user by email error: ' . $e->getMessage());
            return false;
        }
    }

    public function userExistsById($id)
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE id = :id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('User exists check error: ' . $e->getMessage());
            return false;
        }
    }

    public function usernameExists($username, $exclude_id = null)
    {
        try {
            if ($exclude_id) {
                $sql = "SELECT COUNT(*) FROM users WHERE username = :username AND id != :id";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':id', $exclude_id, PDO::PARAM_INT);
            } else {
                $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Username exists check error: ' . $e->getMessage());
            return false;
        }
    }

    public function emailExists($email, $exclude_id = null)
    {
        try {
            if ($exclude_id) {
                $sql = "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':id', $exclude_id, PDO::PARAM_INT);
            } else {
                $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Email exists check error: ' . $e->getMessage());
            return false;
        }
    }

    public function createUser($username, $password_hash, $email, $role = 0)
    {
        try {
            $sql = "INSERT INTO users (username, password, email, role, void) VALUES (:username, :password, :email, :role, 0)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Create user error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateUser($id, $username, $email, $role, $password_hash = null)
    {
        try {
            if ($password_hash !== null && $password_hash !== '') {
                $sql = "UPDATE users SET username = :username, email = :email, role = :role, password = :password WHERE id = :id";
            } else {
                $sql = "UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id";
            }
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_INT);
            if ($password_hash !== null && $password_hash !== '') {
                $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Update user error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($id)
    {
        try {
            $sql = "UPDATE users SET void = 1 WHERE id = :id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Soft delete user error: ' . $e->getMessage());
            return false;
        }
    }

    public function hardDeleteUser($id)
    {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Hard delete user error: ' . $e->getMessage());
            return false;
        }
    }

    public function restoreUser($id)
    {
        try {
            $sql = "UPDATE users SET void = 0 WHERE id = :id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Restore user error: ' . $e->getMessage());
            return false;
        }
    }

    public function getCounts()
    {
        try {
            $pdo = $this->conn->getConnection();
            $active = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE void = 0")->fetchColumn();
            $deleted = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE void = 1")->fetchColumn();
            $total = $active + $deleted;
            $admins = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 1 AND void = 0")->fetchColumn();
            $normal = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 0 AND void = 0")->fetchColumn();
            return [
                'total_users' => $total,
                'active_users' => $active,
                'deleted_users' => $deleted,
                'roles' => ['admin' => $admins, 'user' => $normal]
            ];
        } catch (PDOException $e) {
            error_log('Get user counts error: ' . $e->getMessage());
            return [
                'total_users' => 0,
                'active_users' => 0,
                'deleted_users' => 0,
                'roles' => ['admin' => 0, 'user' => 0]
            ];
        }
    }

    public function searchUsers($keyword, $include_deleted = false)
    {
        try {
            $kw = '%' . $keyword . '%';
            $whereVoid = $include_deleted ? '' : ' AND void = 0';
            $sql = "SELECT * FROM users WHERE (username LIKE :kw OR email LIKE :kw OR LPAD(id,10,'0') LIKE :kw) $whereVoid ORDER BY id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':kw', $kw, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Search users error: ' . $e->getMessage());
            return [];
        }
    }
}
?>
