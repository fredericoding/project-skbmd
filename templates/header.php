<?php
// Memuat file konfigurasi utama.
require_once __DIR__ . '/../config.php';

// Ambil semua pengaturan dari database
$settings = [];
$result = $db->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fungsi untuk tanggal Indonesia
function getIndonesianDate()
{
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $day = $days[date('w')];
    $date = date('d');
    $month = $months[date('n') - 1];
    $year = date('Y');
    return "$day, $date $month $year";
}

// Menentukan halaman aktif
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$isRkbmdPage = in_array($currentPage, ['pemindahtanganan.php', 'penghapusan.php']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_title'] ?? 'SKBMD Jatim') ?> - BPKAD Provinsi Jawa Timur</title>
    <link rel="icon"
        href="<?= BASE_URL ?>/<?= htmlspecialchars($settings['site_logo'] ?? 'assets/images/logo-placeholder.png') ?>"
        type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=<?= time() ?>">
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>

<body class="light-mode">

    <div class="overlay" id="page-overlay">
        <nav class="mobile-nav" id="mobile-navigation">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                </li>
                <!-- Menu Dropdown Mobile -->
                <li class="nav-item-dropdown-mobile">
                    <a href="#" class="dropdown-toggle-mobile">
                        <i class="fas fa-file-alt"></i><span>RKBMD</span><i
                            class="fas fa-chevron-down arrow-mobile"></i>
                    </a>
                    <ul class="dropdown-menu-mobile">
                        <li><a href="<?= BASE_URL ?>/pemindahtanganan.php"><i
                                    class="fas fa-exchange-alt"></i><span>RKBMD Pemindahtanganan</span></a></li>
                        <li><a href="<?= BASE_URL ?>/penghapusan.php"><i class="fas fa-trash-alt"></i><span>RKBMD
                                    Penghapusan</span></a></li>
                    </ul>
                </li>
                <li><a href="<?= BASE_URL ?>/rekapitulasi.php"><i
                            class="fas fa-chart-bar"></i><span>Progres</span></a></li>
                <li><a href="<?= BASE_URL ?>/admin/login.php"><i
                            class="fas fa-sign-in-alt"></i><span>Login Admin</span></a></li>
            </ul>
        </nav>
    </div>

    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($settings['site_logo'] ?? 'assets/images/logo-placeholder.png') ?>"
                        alt="Logo Pemerintah" class="logo">
                    <h1 class="site-title"><?= htmlspecialchars($settings['site_title'] ?? 'E-RASER') ?></h1>
                </div>
                <div class="header-center">
                    <div class="date-display"><i class="fas fa-calendar-alt"></i><span><?= getIndonesianDate() ?></span>
                    </div>
                </div>
                <div class="header-right">
                    <button id="theme-toggle" class="theme-toggle-btn"><i class="fas fa-moon"></i><i
                            class="fas fa-sun"></i></button>
                    <a href="<?= BASE_URL ?>/admin/login.php" class="login-btn"><i
                            class="fas fa-sign-in-alt"></i><span>Login</span></a>
                    <button id="mobile-menu-toggle" class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
                </div>
            </div>
        </div>
    </header>

    <!-- Menu Navigasi Utama (Desktop) -->
    <nav class="main-nav" id="main-navigation-desktop">
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>"><i
                        class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <!-- Menu Dropdown Desktop -->
            <li class="nav-item-dropdown">
                <a href="#" class="dropdown-toggle <?= $isRkbmdPage ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i><span>RKBMD</span><i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?= BASE_URL ?>/pemindahtanganan.php">RKBMD Pemindahtanganan</a></li>
                    <li><a href="<?= BASE_URL ?>/penghapusan.php"> RKBMD Penghapusan</a></li>
                </ul>
            </li>
            <li><a href="<?= BASE_URL ?>/rekapitulasi.php"
                    class="<?= ($currentPage == 'rekapitulasi.php') ? 'active' : '' ?>"><i
                        class="fas fa-chart-bar"></i><span>Progres</span></a></li>
        </ul>
    </nav>

    <main class="main-content">