<?php
require_once __DIR__ . '/User.php';

/**
 * Class Teacher
 * Inheritance: Teacher extends User
 * Teacher bisa input absensi dan lihat laporan
 */
class Teacher extends User {

    public function __construct(int $id = 0, string $username = '', string $password = '', string $namaLengkap = '') {
        parent::__construct($id, $username, $password, 'teacher', $namaLengkap);
    }

    /**
     * Input / simpan absensi siswa
     */
    public function markAttendance(int $studentId, string $tanggal, string $status, string $keterangan = ''): bool {
        // Cek apakah sudah ada absensi hari ini untuk siswa ini
        $stmt = $this->db->prepare("SELECT id FROM attendance WHERE student_id = ? AND tanggal = ?");
        $stmt->bind_param("is", $studentId, $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();

        $createdBy = $_SESSION['user_id'] ?? 0;

        if ($result->num_rows > 0) {
            // Update jika sudah ada
            $stmt = $this->db->prepare("UPDATE attendance SET status_absensi = ?, keterangan = ? WHERE student_id = ? AND tanggal = ?");
            $stmt->bind_param("ssis", $status, $keterangan, $studentId, $tanggal);
        } else {
            // Insert baru
            $stmt = $this->db->prepare("INSERT INTO attendance (student_id, tanggal, status_absensi, keterangan, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $studentId, $tanggal, $status, $keterangan, $createdBy);
        }
        return $stmt->execute();
    }

    /**
     * Ambil rekap absensi berdasarkan kelas dan tanggal
     */
    public function getAttendanceByDate(string $tanggal, string $kelas = ''): array {
        if ($kelas) {
            $stmt = $this->db->prepare(
                "SELECT s.id, s.nis, s.nama, s.kelas, 
                        COALESCE(a.status_absensi, 'belum') as status_absensi,
                        a.keterangan
                 FROM students s
                 LEFT JOIN attendance a ON s.id = a.student_id AND a.tanggal = ?
                 WHERE s.kelas = ?
                 ORDER BY s.nama"
            );
            $stmt->bind_param("ss", $tanggal, $kelas);
        } else {
            $stmt = $this->db->prepare(
                "SELECT s.id, s.nis, s.nama, s.kelas,
                        COALESCE(a.status_absensi, 'belum') as status_absensi,
                        a.keterangan
                 FROM students s
                 LEFT JOIN attendance a ON s.id = a.student_id AND a.tanggal = ?
                 ORDER BY s.kelas, s.nama"
            );
            $stmt->bind_param("s", $tanggal);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Generate laporan kehadiran siswa
     */
    public function generateReport(string $kelas = '', string $bulan = '', string $tahun = ''): array {
        $where = ["1=1"];
        $params = [];
        $types  = "";

        if ($kelas) {
            $where[] = "s.kelas = ?";
            $params[] = $kelas;
            $types .= "s";
        }
        if ($bulan && $tahun) {
            $where[] = "MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?";
            $params[] = (int)$bulan;
            $params[] = (int)$tahun;
            $types .= "ii";
        }

        $whereStr = implode(" AND ", $where);

        $sql = "SELECT s.nis, s.nama, s.kelas,
                    COUNT(CASE WHEN a.status_absensi = 'hadir' THEN 1 END) as hadir,
                    COUNT(CASE WHEN a.status_absensi = 'sakit' THEN 1 END) as sakit,
                    COUNT(CASE WHEN a.status_absensi = 'izin'  THEN 1 END) as izin,
                    COUNT(CASE WHEN a.status_absensi = 'alpha' THEN 1 END) as alpha,
                    COUNT(a.id) as total_hari
                FROM students s
                LEFT JOIN attendance a ON s.id = a.student_id
                WHERE $whereStr
                GROUP BY s.id
                ORDER BY s.kelas, s.nama";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Ambil daftar kelas yang tersedia
     */
    public function getKelas(): array {
        $result = $this->db->query("SELECT DISTINCT kelas FROM students ORDER BY kelas");
        return array_column($result->fetch_all(MYSQLI_ASSOC), 'kelas');
    }
}
?>
