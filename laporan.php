<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

require_once '../classes/Teacher.php';
require_once '../classes/Student.php';

$teacher  = new Teacher();
$student  = new Student();

$kelas    = $_GET['kelas']   ?? '';
$bulan    = $_GET['bulan']   ?? date('m');
$tahun    = $_GET['tahun']   ?? date('Y');

$kelasList = $teacher->getKelas();
$laporan   = $teacher->generateReport($kelas, $bulan, $tahun);

$namaBulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
              '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehadiran – Sistem Absensi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../assets/partials/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h2>Laporan Kehadiran</h2>
    </div>

    <!-- Filter -->
    <div class="section-card">
        <form method="GET" action="laporan.php" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k ?>" <?= $kelas === $k ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Bulan</label>
                    <select name="bulan">
                        <?php foreach ($namaBulan as $num => $nm): ?>
                            <option value="<?= $num ?>" <?= $bulan === $num ? 'selected' : '' ?>><?= $nm ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tahun</label>
                    <input type="number" name="tahun" value="<?= $tahun ?>" min="2020" max="2030">
                </div>
                <div class="form-group form-group-btn">
                    <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Laporan -->
    <div class="section-card">
        <div class="table-toolbar">
            <h3 class="section-title">
                Rekap <?= $kelas ? 'Kelas '.$kelas : 'Semua Kelas' ?> — <?= $namaBulan[$bulan] ?> <?= $tahun ?>
            </h3>
            <button onclick="window.print()" class="btn btn-secondary">🖨️ Cetak</button>
        </div>

        <?php if (empty($laporan)): ?>
            <p class="empty-state">Tidak ada data absensi untuk filter ini.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th class="text-center" style="color:#22c55e">✅ Hadir</th>
                        <th class="text-center" style="color:#f59e0b">🤒 Sakit</th>
                        <th class="text-center" style="color:#3b82f6">📋 Izin</th>
                        <th class="text-center" style="color:#ef4444">❌ Alpha</th>
                        <th class="text-center">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan as $i => $row): ?>
                    <?php
                        $total = $row['total_hari'];
                        $persen = $total > 0 ? round(($row['hadir'] / $total) * 100) : 0;
                        $persenClass = $persen >= 75 ? 'badge-success' : ($persen >= 50 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['nis']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><span class="badge badge-primary"><?= $row['kelas'] ?></span></td>
                        <td class="text-center"><strong><?= $row['hadir'] ?></strong></td>
                        <td class="text-center"><?= $row['sakit'] ?></td>
                        <td class="text-center"><?= $row['izin'] ?></td>
                        <td class="text-center"><?= $row['alpha'] ?></td>
                        <td class="text-center">
                            <span class="badge <?= $persenClass ?>"><?= $persen ?>%</span>
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
