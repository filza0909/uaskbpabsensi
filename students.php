<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

require_once '../classes/Student.php';

$student = new Student();
$message = '';
$error   = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nis   = trim($_POST['nis'] ?? '');
        $nama  = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        if ($nis && $nama && $kelas) {
            if ($student->addStudent($nis, $nama, $kelas)) {
                $message = "Siswa berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan siswa. NIS mungkin sudah digunakan.";
            }
        } else { $error = "Semua field wajib diisi!"; }

    } elseif ($action === 'edit') {
        $id    = (int)$_POST['id'];
        $nis   = trim($_POST['nis'] ?? '');
        $nama  = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        if ($id && $nis && $nama && $kelas) {
            if ($student->updateStudent($id, $nis, $nama, $kelas)) {
                $message = "Data siswa berhasil diperbarui!";
            } else { $error = "Gagal memperbarui data."; }
        }

    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        if ($student->deleteStudent($id)) {
            $message = "Siswa berhasil dihapus.";
        } else { $error = "Gagal menghapus siswa."; }
    }
}

$keyword = trim($_GET['search'] ?? '');
$students = $keyword ? $student->searchStudent($keyword) : $student->getAllStudents();

$editData = null;
if (isset($_GET['edit'])) {
    $editData = $student->getStudentById((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa – Sistem Absensi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../assets/partials/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h2>Data Siswa</h2>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Form Tambah / Edit -->
    <div class="section-card">
        <h3 class="section-title"><?= $editData ? 'Edit Siswa' : 'Tambah Siswa Baru' ?></h3>
        <form method="POST" action="students.php">
            <input type="hidden" name="action" value="<?= $editData ? 'edit' : 'add' ?>">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>NIS</label>
                    <input type="text" name="nis" value="<?= htmlspecialchars($editData['nis'] ?? '') ?>" placeholder="Nomor Induk Siswa" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" placeholder="Nama siswa" required>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" name="kelas" value="<?= htmlspecialchars($editData['kelas'] ?? '') ?>" placeholder="Contoh: X-A" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Siswa' ?></button>
                <?php if ($editData): ?>
                    <a href="students.php" class="btn btn-secondary">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Pencarian + Tabel -->
    <div class="section-card">
        <div class="table-toolbar">
            <h3 class="section-title">Daftar Siswa (<?= count($students) ?>)</h3>
            <form method="GET" action="students.php" class="search-form">
                <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari nama / NIS...">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <?php if ($keyword): ?><a href="students.php" class="btn btn-secondary">Reset</a><?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="5" class="empty-state">Tidak ada data siswa.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($s['nis']) ?></td>
                            <td><?= htmlspecialchars($s['nama']) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($s['kelas']) ?></span></td>
                            <td class="action-col">
                                <a href="students.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                <form method="POST" action="students.php" style="display:inline" onsubmit="return confirm('Hapus siswa ini?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️ Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
