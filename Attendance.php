<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Class Attendance
 * Merepresentasikan data absensi siswa
 */
class Attendance {
    // Encapsulation: property private
    private int $id;
    private int $studentId;
    private string $tanggal;
    private string $statusAbsensi;
    private string $keterangan;

    private mysqli $db;

    // Constructor
    public function __construct(int $id = 0, int $studentId = 0, string $tanggal = '', string $statusAbsensi = 'hadir', string $keterangan = '') {
        $this->id            = $id;
        $this->studentId     = $studentId;
        $this->tanggal       = $tanggal ?: date('Y-m-d');
        $this->statusAbsensi = $statusAbsensi;
        $this->keterangan    = $keterangan;
        $this->db            = Database::getInstance()->getConnection();
    }

    // ---- GETTER ----
    public function getId(): int              { return $this->id; }
    public function getStudentId(): int       { return $this->studentId; }
    public function getTanggal(): string      { return $this->tanggal; }
    public function getStatusAbsensi(): string { return $this->statusAbsensi; }
    public function getKeterangan(): string   { return $this->keterangan; }

    // ---- SETTER ----
    public function setStudentId(int $sid): void       { $this->studentId = $sid; }
    public function setTanggal(string $tgl): void      { $this->tanggal = $tgl; }
    public function setStatusAbsensi(string $s): void  { $this->statusAbsensi = $s; }
    public function setKeterangan(string $k): void     { $this->keterangan = $k; }

    /**
     * Simpan absensi
     */
    public function save(): bool {
        // Cek duplikat
        $stmt = $this->db->prepare("SELECT id FROM attendance WHERE student_id = ? AND tanggal = ?");
        $stmt->bind_param("is", $this->studentId, $this->tanggal);
        $stmt->execute();
        $result = $stmt->get_result();

        $createdBy = $_SESSION['user_id'] ?? 0;

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $stmt = $this->db->prepare("UPDATE attendance SET status_absensi = ?, keterangan = ? WHERE id = ?");
            $stmt->bind_param("ssi", $this->statusAbsensi, $this->keterangan, $id);
        } else {
            $stmt = $this->db->prepare("INSERT INTO attendance (student_id, tanggal, status_absensi, keterangan, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $this->studentId, $this->tanggal, $this->statusAbsensi, $this->keterangan, $createdBy);
        }
        return $stmt->execute();
    }

    /**
     * Ambil rekap absensi per siswa
     */
    public function getRekapSiswa(int $studentId): array {
        $stmt = $this->db->prepare(
            "SELECT tanggal, status_absensi, keterangan FROM attendance 
             WHERE student_id = ? ORDER BY tanggal DESC"
        );
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Hitung statistik kehadiran
     */
    public function getStatistik(): array {
        $result = $this->db->query(
            "SELECT status_absensi, COUNT(*) as jumlah 
             FROM attendance 
             GROUP BY status_absensi"
        );
        $stats = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status_absensi']] = (int)$row['jumlah'];
        }
        return $stats;
    }

    /**
     * Ambil absensi terbaru
     */
    public function getRecentAttendance(int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, s.nama, s.nis, s.kelas 
             FROM attendance a
             JOIN students s ON a.student_id = s.id
             ORDER BY a.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
