<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../classes/Attendance.php';
require_once '../classes/Student.php';

$attendance = new Attendance();
$student    = new Student();

$stats      = $attendance->getStatistik();
$recent     = $attendance->getRecentAttendance(5);
$totalSiswa = count($student->getAllStudents());
$role       = $_SESSION['role'];
$nama       = $_SESSION['nama_lengkap'];

$statusBadge = ['hadir' => 'badge-success', 'sakit' => 'badge-warning', 'izin' => 'badge-info', 'alpha' => 'badge-danger', 'belum' => 'badge-secondary'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Sistem Absensi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../assets/partials/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h2>Dashboard</h2>
        <p>Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong> 
           <span class="badge badge-primary"><?= ucfirst($role) ?></span>
        </p>
    </div>

    <!-- Statistik Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?= $totalSiswa ?></h3>
                <p>Total Siswa</p>
            </div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?= $stats['hadir'] ?></h3>
                <p>Total Hadir</p>
            </div>
        </div>
        <div class="stat-card stat-yellow">
            <div class="stat-icon">🤒</div>
            <div class="stat-info">
                <h3><?= $stats['sakit'] ?></h3>
                <p>Total Sakit</p>
            </div>
        </div>
        <div class="stat-card stat-red">
            <div class="stat-icon">❌</div>
            <div class="stat-info">
                <h3><?= $stats['alpha'] ?></h3>
                <p>Total Alpha</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-card">
        <h3 class="section-title">Aksi Cepat</h3>
        <div class="quick-actions">
            <a href="absensi.php" class="action-btn action-green">
                <span>📝</span> Input Absensi Hari Ini
            </a>
            <a href="students.php" class="action-btn action-blue">
                <span>👤</span> Data Siswa
            </a>
            <a href="laporan.php" class="action-btn action-purple">
                <span>📊</span> Laporan Kehadiran
            </a>
            <?php if ($role === 'admin'): ?>
            <a href="users.php" class="action-btn action-orange">
                <span>⚙️</span> Kelola Pengguna
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Absensi Terbaru -->
    <div class="section-card">
        <h3 class="section-title">Absensi Terbaru</h3>
        <?php if (empty($recent)): ?>
            <p class="empty-state">Belum ada data absensi.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <span class="badge <?= $statusBadge[$row['status_absensi']] ?? 'badge-secondary' ?>">
                                <?= ucfirst($row['status_absensi']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
