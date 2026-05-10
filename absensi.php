<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

require_once '../classes/Teacher.php';
require_once '../classes/Student.php';
require_once '../classes/Attendance.php';

$teacher  = new Teacher();
$student  = new Student();
$message  = '';
$error    = '';

$tanggal  = $_GET['tanggal'] ?? date('Y-m-d');
$kelas    = $_GET['kelas']   ?? '';
$kelasList = $teacher->getKelas();

// Handle POST: simpan absensi bulk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggalPost = $_POST['tanggal'] ?? date('Y-m-d');
    $absensiData = $_POST['absensi'] ?? [];
    $keteranganData = $_POST['keterangan'] ?? [];

    $successCount = 0;
    foreach ($absensiData as $studentId => $status) {
        $ket = $keteranganData[$studentId] ?? '';
        $att = new Attendance(0, (int)$studentId, $tanggalPost, $status, $ket);
        if ($att->save()) $successCount++;
    }

    if ($successCount > 0) {
        $message = "Absensi untuk $successCount siswa berhasil disimpan!";
    } else {
        $error = "Tidak ada data absensi yang disimpan.";
    }
    $tanggal = $tanggalPost;
}

$students = $kelas ? $student->getAllStudents($kelas) : $student->getAllStudents();
$absensiHariIni = $teacher->getAttendanceByDate($tanggal, $kelas);

// Index by student_id
$absensiMap = [];
foreach ($absensiHariIni as $row) {
    $absensiMap[$row['id']] = $row;
}

$statusOptions = ['hadir' => '✅ Hadir', 'sakit' => '🤒 Sakit', 'izin' => '📋 Izin', 'alpha' => '❌ Alpha'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Absensi – Sistem Absensi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../assets/partials/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h2>Input Absensi</h2>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Filter -->
    <div class="section-card">
        <form method="GET" action="absensi.php" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $tanggal ?>">
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k ?>" <?= $kelas === $k ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group-btn">
                    <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Form Absensi -->
    <div class="section-card">
        <h3 class="section-title">
            Absensi Tanggal: <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>
            <?php if ($kelas): ?> — Kelas: <strong><?= htmlspecialchars($kelas) ?></strong><?php endif; ?>
        </h3>

        <?php if (empty($students)): ?>
            <p class="empty-state">Tidak ada data siswa. <a href="students.php">Tambah siswa dulu.</a></p>
        <?php else: ?>

        <!-- Tombol pilih cepat -->
        <div class="quick-select">
            <span>Pilih cepat:</span>
            <button type="button" onclick="setAll('hadir')" class="btn btn-sm btn-success">Semua Hadir</button>
            <button type="button" onclick="setAll('alpha')" class="btn btn-sm btn-danger">Semua Alpha</button>
        </div>

        <form method="POST" action="absensi.php">
            <input type="hidden" name="tanggal" value="<?= $tanggal ?>">

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s): ?>
                        <?php $currentStatus = $absensiMap[$s['id']]['status_absensi'] ?? 'hadir'; ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($s['nis']) ?></td>
                            <td><?= htmlspecialchars($s['nama']) ?></td>
                            <td><span class="badge badge-primary"><?= $s['kelas'] ?></span></td>
                            <td>
                                <select name="absensi[<?= $s['id'] ?>]" class="status-select status-<?= $currentStatus ?>">
                                    <?php foreach ($statusOptions as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $currentStatus === $val ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="keterangan[<?= $s['id'] ?>]" 
                                       value="<?= htmlspecialchars($absensiMap[$s['id']]['keterangan'] ?? '') ?>"
                                       placeholder="Opsional..." class="input-keterangan">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">💾 Simpan Absensi</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function setAll(status) {
    document.querySelectorAll('.status-select').forEach(sel => {
        sel.value = status;
        sel.className = `status-select status-${status}`;
    });
}
document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', function() {
        this.className = `status-select status-${this.value}`;
    });
});
</script>
</body>
</html>
