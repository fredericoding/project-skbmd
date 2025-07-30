<?php
// Memuat file konfigurasi utama
require_once __DIR__ . '/../../config.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "/admin/login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

$current_page_admin = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-RASER</title>

    <link rel="icon" href="<?= BASE_URL ?>/assets/images/logo-placeholder.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Stylesheet Khusus Admin (Path diperbaiki) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/admin/assets/css/admin_style.css?v=<?= time() ?>">

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>

<body class="light-mode">

    <div class="admin-wrapper">
        <?php require_once 'sidebar.php'; ?>

        <div class="main-content-admin">
            <header class="admin-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title-admin">Dashboard</h1>
                </div>
                <div class="header-right">
                    <button id="theme-toggle-admin" class="theme-toggle-btn-admin" aria-label="Toggle Dark Mode">
                        <i class="fas fa-moon"></i>
                        <i class="fas fa-sun"></i>
                    </button>
                    <div class="admin-profile-dropdown">
                        <button class="profile-btn">
                            <i class="fas fa-user-circle"></i>
                            <span><?= htmlspecialchars($admin_username) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?= BASE_URL ?>/admin/profile.php"><i class="fas fa-user-edit"></i> Profil Saya</a>
                            <a href="<?= BASE_URL ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-page-content">