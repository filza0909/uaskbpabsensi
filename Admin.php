<?php
require_once __DIR__ . '/User.php';

/**
 * Class Admin
 * Inheritance: Admin extends User
 * Admin bisa mengelola data guru dan siswa
 */
class Admin extends User {

    // Constructor memanggil constructor parent
    public function __construct(int $id = 0, string $username = '', string $password = '', string $namaLengkap = '') {
        parent::__construct($id, $username, $password, 'admin', $namaLengkap);
    }

    /**
     * Tambah user baru (guru)
     */
    public function addTeacher(string $username, string $password, string $namaLengkap): bool {
        $hashedPassword = md5($password);
        $role = 'teacher';
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashedPassword, $role, $namaLengkap);
        return $stmt->execute();
    }

    /**
     * Hapus user (guru)
     */
    public function deleteTeacher(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    /**
     * Ambil semua data guru
     */
    public function getAllTeachers(): array {
        $result = $this->db->query("SELECT id, username, nama_lengkap FROM users WHERE role = 'teacher' ORDER BY nama_lengkap");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Ambil semua data user
     */
    public function getAllUsers(): array {
        $result = $this->db->query("SELECT id, username, role, nama_lengkap, created_at FROM users ORDER BY role, nama_lengkap");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
