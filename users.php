<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
if ($_SESSION['role'] !== 'admin') { header("Location: dashboard.php"); exit; }

require_once '../classes/Admin.php';

$admin   = new Admin();
$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $nama     = trim($_POST['nama_lengkap'] ?? '');
        if ($username && $password && $nama) {
            if ($admin->addTeacher($username, $password, $nama)) {
                $message = "Guru berhasil ditambahkan!";
            } else { $error = "Gagal. Username mungkin sudah digunakan."; }
        } else { $error = "Semua field wajib diisi!"; }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        if ($admin->deleteTeacher($id)) {
            $message = "Guru berhasil dihapus.";
        } else { $error = "Gagal menghapus guru."; }
    }
}

$users = $admin->getAllUsers();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna – Sistem Absensi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../assets/partials/navbar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h2>Kelola Pengguna</h2>
        <p>Halaman ini hanya dapat diakses oleh Admin</p>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Form tambah guru -->
    <div class="section-card">
        <h3 class="section-title">Tambah Guru Baru</h3>
        <form method="POST" action="users.php">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Username login" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" placeholder="Nama lengkap guru" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">➕ Tambah Guru</button>
        </form>
    </div>

    <!-- Daftar pengguna -->
    <div class="section-card">
        <h3 class="section-title">Daftar Pengguna (<?= count($users) ?>)</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                        <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'badge-danger' : 'badge-info' ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['role'] === 'teacher' && $u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" action="users.php" style="display:inline"
                                  onsubmit="return confirm('Hapus pengguna ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️ Hapus</button>
                            </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
