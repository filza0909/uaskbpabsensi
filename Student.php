<?php
require_once __DIR__ . '/../config/database.php';

class Student {    // untuk mengolah data siswa
    // Encapsulation: property private
    private int $id;
    private string $nis;
    private string $nama;
    private string $kelas;

    private mysqli $db;

    // Constructor utk ngisi data dan koneksi awal
    public function __construct(int $id = 0, string $nis = '', string $nama = '', string $kelas = '') {
        $this->id    = $id;
        $this->nis   = $nis;
        $this->nama  = $nama;
        $this->kelas = $kelas;
        $this->db    = Database::getInstance()->getConnection();
    }

    //GETTER
    public function getId(): int       { return $this->id; }
    public function getNis(): string   { return $this->nis; }
    public function getNama(): string  { return $this->nama; }
    public function getKelas(): string { return $this->kelas; }

    //SETTER
    public function setNis(string $nis): void      { $this->nis = $nis; }
    public function setNama(string $nama): void    { $this->nama = $nama; }
    public function setKelas(string $kelas): void  { $this->kelas = $kelas; }

    //tamabh data siswa
    public function addStudent(string $nis, string $nama, string $kelas): bool {
        $stmt = $this->db->prepare("INSERT INTO students (nis, nama, kelas) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nis, $nama, $kelas);
        return $stmt->execute();
    }

    //update data siswa
    public function updateStudent(int $id, string $nis, string $nama, string $kelas): bool {
        $stmt = $this->db->prepare("UPDATE students SET nis = ?, nama = ?, kelas = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nis, $nama, $kelas, $id);
        return $stmt->execute();
    }

    //hapus data siswa
    public function deleteStudent(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    //ambil semua data siswa, bisa filter berdasarkan kelas
    public function getAllStudents(string $kelas = ''): array {
        if ($kelas) {
            $stmt = $this->db->prepare("SELECT * FROM students WHERE kelas = ? ORDER BY nama");
            $stmt->bind_param("s", $kelas);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        $result = $this->db->query("SELECT * FROM students ORDER BY kelas, nama");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //cari siswa berdasarkan nama atau NIS
    public function searchStudent(string $keyword): array {
        $like = "%$keyword%";
        $stmt = $this->db->prepare("SELECT * FROM students WHERE nama LIKE ? OR nis LIKE ? ORDER BY nama");
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //Ambil siswa berdasarkan ID
    public function getStudentById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
}
?>
