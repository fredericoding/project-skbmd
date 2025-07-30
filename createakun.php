<?php
/**
 * =================================================================
 * Halaman Pembuatan Akun Admin Utama (Sekali Pakai)
 * =================================================================
 * PERINGATAN: HAPUS FILE INI SEGERA SETELAH ANDA MEMBUAT AKUN!
 * MENINGGALKAN FILE INI DI SERVER SANGAT BERISIKO KEAMANAN.
 *
 * Cara Penggunaan:
 * 1. Letakkan file ini di direktori utama proyek Anda.
 * 2. Buka file ini di browser (misal: http://localhost/proyek-skbmd/create_super_admin.php).
 * 3. Isi username dan password, lalu klik "Buat Akun".
 * 4. Setelah akun berhasil dibuat, HAPUS FILE INI dari server Anda.
 * =================================================================
 */

// Memuat file konfigurasi untuk koneksi database
require_once __DIR__ . '/config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = 'Username dan password tidak boleh kosong.';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password minimal harus 6 karakter.';
        $message_type = 'error';
    } else {
        // Cek apakah username sudah ada
        $stmt_check = $db->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt_check->bind_param('s', $username);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $message = 'Username "' . htmlspecialchars($username) . '" sudah digunakan. Silakan pilih yang lain.';
            $message_type = 'error';
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'utama';

            // Masukkan admin baru ke database
            $stmt_insert = $db->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
            $stmt_insert->bind_param('sss', $username, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $message = 'Akun Admin Utama "' . htmlspecialchars($username) . '" berhasil dibuat. <strong>SEGERA HAPUS FILE INI!</strong>';
                $message_type = 'success';
            } else {
                $message = 'Gagal membuat akun. Terjadi kesalahan pada database.';
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun Admin Utama</title>
    <style>
        body { font-family: sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .warning { text-align: center; margin-top: 20px; color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Buat Akun Admin Utama</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Buat Akun</button>
        </form>
        <p class="warning">PENTING: Hapus file ini setelah selesai!</p>
    </div>
</body>
</html>
