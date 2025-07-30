<?php
// Memuat file konfigurasi terlebih dahulu untuk akses database
require_once __DIR__ . '/../config.php';

// --- BLOK PROSES FORM DIPINDAHKAN KE ATAS ---
// Proses tambah admin baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    // Pastikan hanya admin utama yang bisa melakukan aksi ini
    if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'utama') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Validasi sederhana
        if (empty($username) || empty($password)) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Username dan password tidak boleh kosong.'];
        } elseif (strlen($password) < 6) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Password minimal harus 6 karakter.'];
        } else {
            // Cek apakah username sudah ada
            $stmt_check = $db->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt_check->bind_param('s', $username);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Username sudah ada. Silakan gunakan yang lain.'];
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_add = $db->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'input')");
                $stmt_add->bind_param('ss', $username, $hashed_password);
                if($stmt_add->execute()){
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Admin baru berhasil ditambahkan.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal menambahkan admin.'];
                }
            }
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'];
    }
    
    // Redirect untuk mencegah re-submit form
    header("Location: manage_admins.php");
    exit();
}

// --- AKHIR BLOK PROSES FORM ---


// Sekarang baru memuat tampilan (header, sidebar, dll.)
require_once __DIR__ . '/templates/header.php';

// Keamanan: Hanya Admin Utama yang bisa mengakses halaman ini
if ($admin_role !== 'utama') {
    // Tampilkan pesan error dan hentikan eksekusi jika bukan admin utama
    echo '<div class="container-fluid"><div class="alert alert-danger">Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.</div></div>';
    require_once __DIR__ . '/templates/footer.php';
    exit();
}

// Ambil daftar admin untuk ditampilkan di tabel
$admins = $db->query("SELECT id, username, role, created_at FROM admins WHERE is_deleted = 0 ORDER BY id DESC");
?>
<div class="container-fluid">
    <h1 class="page-title-admin-content">Manajemen Akun Admin</h1>
    <div class="row">
        <div class="col-lg-8">
            <div class="card-admin">
                <div class="card-header-admin"><h3 class="card-title-admin">Daftar Admin</h3></div>
                <div class="card-body-admin">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>"><?= $_SESSION['flash_message']['message'] ?></div>
                        <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Tgl Dibuat</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php while($admin = $admins->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $admin['id'] ?></td>
                                    <td><?= htmlspecialchars($admin['username']) ?></td>
                                    <td><span class="role-badge role-<?= $admin['role'] ?>"><?= ucfirst($admin['role']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($admin['created_at'])) ?></td>
                                    <td>
                                        <?php if($admin['role'] !== 'utama'): ?>
                                        <a href="delete_data.php?type=admin&id=<?= $admin['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Anda yakin?')"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-admin">
                <div class="card-header-admin"><h3 class="card-title-admin">Tambah Admin Baru</h3></div>
                <div class="card-body-admin">
                    <form action="manage_admins.php" method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" name="add_admin" class="btn btn-primary">Tambah Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="assets/css/admin_style.css">
<?php require_once __DIR__ . '/templates/footer.php'; ?>
