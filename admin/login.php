<?php
// Memuat file konfigurasi.
require_once __DIR__ . '/../config.php';

// Jika admin sudah login, langsung arahkan ke dashboard admin.
if (isset($_SESSION['admin_id'])) {
    // URL diperbaiki menggunakan BASE_URL
    header("Location: " . BASE_URL . "/admin/index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        $stmt = $db->prepare("SELECT id, username, password, role FROM admins WHERE username = ? AND is_deleted = 0");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];

                // Arahkan ke dashboard admin dengan URL yang benar
                header("Location: " . BASE_URL . "/admin/index.php");
                exit();
            } else {
                $error_message = 'Username atau password salah.';
            }
        } else {
            $error_message = 'Username atau password salah.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - E-RASER</title>
    <!-- Path ke aset diperbaiki menggunakan BASE_URL -->
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/logo_1753758341.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a5a83;
            --bg-color: #f8f9fa;
            --surface-color: #ffffff;
            --text-primary: #1a202c;
            --danger-color: #D32F2F;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            /* Latar belakang diperbarui dengan gambar eksternal */
            background-image: url('https://plus.unsplash.com/premium_photo-1667761634654-7fcf176434b8?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8YmFja2dyb3VuZHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-primary);
            position: relative;
        }
        /* Menambahkan lapisan overlay gelap untuk keterbacaan */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            /* Panel login dibuat sedikit transparan dengan efek blur */
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            animation: fadeIn 0.5s ease-out;
            position: relative; /* Pastikan di atas overlay */
            z-index: 2;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header img {
            height: 60px;
            margin-bottom: 15px;
        }
        .login-header h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 700;
        }
        .login-header p {
            color: #616161;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .form-group .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(10%);
            color: #9e9e9e;
        }
        .form-control {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(58, 90, 131, 0.2);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-login:hover {
            background-color: #2c4a70;
        }
        .error-message {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--danger-color);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid rgba(211, 47, 47, 0.2);
        }
        .back-to-site {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .back-to-site a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="<?= BASE_URL ?>/assets/images/logo_1753758341.png" alt="Logo">
            <h1>Admin Login</h1>
            <p>Selamat datang kembali. Silakan masuk.</p>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <i class="fas fa-user icon"></i>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock icon"></i>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
        <div class="back-to-site">
            <a href="<?= BASE_URL ?>/index.php"><i class="fas fa-arrow-left"></i> Kembali ke Website Utama</a>
        </div>
    </div>
</body>
</html>
