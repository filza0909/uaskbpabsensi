<?php
// Tentukan path relatif ke root berdasarkan lokasi file ini
$depth = substr_count(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$root  = str_repeat('../', max(0, $depth - 1));

$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>
<nav class="navbar">
    <div class="navbar-brand">
        <span class="brand-icon">📋</span>
        <span>SistemAbsensi</span>
    </div>
    <div class="navbar-menu">
        <a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>
        <a href="absensi.php"   class="nav-link <?= $currentPage === 'absensi.php'   ? 'active' : '' ?>">📝 Absensi</a>
        <a href="students.php"  class="nav-link <?= $currentPage === 'students.php'  ? 'active' : '' ?>">👥 Siswa</a>
        <a href="laporan.php"   class="nav-link <?= $currentPage === 'laporan.php'   ? 'active' : '' ?>">📊 Laporan</a>
        <?php if ($role === 'admin'): ?>
        <a href="users.php"     class="nav-link <?= $currentPage === 'users.php'     ? 'active' : '' ?>">⚙️ Pengguna</a>
        <?php endif; ?>
    </div>
    <div class="navbar-user">
        <span class="user-info">👤 <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?></span>
        <a href="logout.php" class="btn btn-sm btn-outline">Keluar</a>
    </div>
</nav>
