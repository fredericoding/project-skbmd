<?php
require_once __DIR__ . '/templates/header.php';

$success_message = '';
$error_message = '';

// Proses form jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $new_username = $_POST['username'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Ambil data admin saat ini dari database
    $stmt = $db->prepare("SELECT username, password FROM admins WHERE id = ?");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $admin_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Validasi password saat ini
    if (!password_verify($current_password, $admin_data['password'])) {
        $error_message = 'Password saat ini salah.';
    } else {
        // Update username jika diisi dan berbeda
        if (!empty($new_username) && $new_username !== $admin_data['username']) {
            // Cek apakah username baru sudah ada
            $stmt_check = $db->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $stmt_check->bind_param('si', $new_username, $admin_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $error_message = 'Username baru sudah digunakan oleh akun lain.';
            } else {
                $stmt_update = $db->prepare("UPDATE admins SET username = ? WHERE id = ?");
                $stmt_update->bind_param('si', $new_username, $admin_id);
                $stmt_update->execute();
                $_SESSION['admin_username'] = $new_username; // Update session
                $success_message = 'Username berhasil diperbarui.';
            }
            $stmt_check->close();
        }

        // Update password jika diisi
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error_message = 'Konfirmasi password baru tidak cocok.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'Password baru minimal harus 6 karakter.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_pass = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt_pass->bind_param('si', $hashed_password, $admin_id);
                $stmt_pass->execute();
                $success_message = ($success_message ? $success_message . ' ' : '') . 'Password berhasil diperbarui.';
            }
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="page-title-admin-content">Profil Saya</h1>
    <p class="page-subtitle-admin">Ubah username atau password Anda.</p>

    <div class="row">
        <div class="col-lg-6">
            <div class="card-admin">
                <div class="card-header-admin">
                    <h3 class="card-title-admin">Ubah Informasi Akun</h3>
                </div>
                <div class="card-body-admin">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="form-group">
                            <label for="username">Username Baru (opsional)</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah">
                        </div>
                        <hr class="form-divider">
                        <div class="form-group">
                            <label for="new_password">Password Baru (opsional)</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        <hr class="form-divider">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini (wajib diisi untuk menyimpan)</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Khusus Halaman Profil */
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 8px; }
.form-control { width: 100%; padding: 12px; border: 1px solid var(--admin-border-light); border-radius: 6px; background-color: var(--admin-bg-light); color: var(--admin-text-dark); }
body.dark-mode .form-control { background-color: var(--admin-border-dark); color: var(--admin-text-light); border-color: #4b5563; }
.form-divider { border: none; border-top: 1px solid var(--admin-border-light); margin: 25px 0; }
body.dark-mode .form-divider { border-top-color: var(--admin-border-dark); }
.btn { padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-primary { background-color: var(--admin-accent); color: white; }
.alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; }
.alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<?php
require_once __DIR__ . '/templates/footer.php';