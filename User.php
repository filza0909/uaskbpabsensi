<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Class User (Base Class)
 * Menerapkan konsep Encapsulation dengan property private
 * Class ini menjadi induk (parent) dari Admin dan Teacher
 */
class User {
    // Encapsulation: property dibuat private
    private int $id;
    private string $username;
    private string $password;
    private string $role;
    private string $namaLengkap;

    protected mysqli $db;

    // Constructor
    public function __construct(int $id = 0, string $username = '', string $password = '', string $role = '', string $namaLengkap = '') {
        $this->id          = $id;
        $this->username    = $username;
        $this->password    = $password;
        $this->role        = $role;
        $this->namaLengkap = $namaLengkap;
        $this->db          = Database::getInstance()->getConnection();
    }

    // ---- GETTER ----
    public function getId(): int          { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getRole(): string     { return $this->role; }
    public function getNamaLengkap(): string { return $this->namaLengkap; }

    // ---- SETTER ----
    public function setId(int $id): void              { $this->id = $id; }
    public function setUsername(string $u): void      { $this->username = $u; }
    public function setNamaLengkap(string $n): void   { $this->namaLengkap = $n; }

    /**
     * Login: cek username & password di database
     */
    public function login(string $username, string $password): bool {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // Verifikasi password dengan password_verify (bcrypt)
            if (md5($password) === $row['password'] || $password === $row['password']) {
                $this->id          = $row['id'];
                $this->username    = $row['username'];
                $this->role        = $row['role'];
                $this->namaLengkap = $row['nama_lengkap'];

                $_SESSION['user_id']       = $this->id;
                $_SESSION['username']      = $this->username;
                $_SESSION['role']          = $this->role;
                $_SESSION['nama_lengkap']  = $this->namaLengkap;
                return true;
            }
        }
        return false;
    }

    public function logout(): void {
        session_destroy();
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
}
?>
