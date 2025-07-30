<?php
// File ini dipanggil dari header.php, jadi semua variabel sesi sudah tersedia.
$current_page_admin = basename($_SERVER['SCRIPT_NAME']);
$query_params = $_GET; // Ambil parameter GET

// Menentukan apakah dropdown RKBMD harus aktif
$isManageRkbmdPage = in_array($current_page_admin, ['manage_pemindahtanganan.php', 'manage_penghapusan.php', 'detail_skpd.php']);
$isInputRkbmdPage = ($current_page_admin == 'input_data.php' && isset($query_params['type']) && in_array($query_params['type'], ['pemindahtanganan', 'penghapusan']));
$isRekapitulasiPage = ($current_page_admin == 'input_data.php' && isset($query_params['type']) && $query_params['type'] == 'rekapitulasi');
?>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-logo-link">
            <img src="<?= BASE_URL ?>/assets/images/logo_1753758341.png" alt="Logo" class="sidebar-logo">
            <span class="sidebar-title">Admin Panel</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-category">Utama</li>
            <li>
                <a href="<?= BASE_URL ?>/admin/index.php"
                    class="<?= ($current_page_admin == 'index.php') ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-category">Manajemen Data</li>
            <!-- Dropdown Input Data -->
            <li class="nav-item-dropdown-admin <?= $isInputRkbmdPage ? 'active' : '' ?>">
                <a href="#" class="dropdown-toggle-admin">
                    <i class="fas fa-plus-circle"></i>
                    <span>Input Data RKBMD</span>
                    <i class="fas fa-chevron-down arrow-admin"></i>
                </a>
                <ul class="dropdown-menu-admin">
                    <li><a href="<?= BASE_URL ?>/admin/input_data.php?type=pemindahtanganan">Pemindahtanganan</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/input_data.php?type=penghapusan">Penghapusan</a></li>
                </ul>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/input_data.php?type=rekapitulasi"
                    class="<?= $isRekapitulasiPage ? 'active' : '' ?>">
                    <i class="fas fa-cogs"></i>
                    <span>Input Data Progres</span>
                </a>
            </li>
            <!-- Dropdown Kelola Data -->
            <li class="nav-item-dropdown-admin <?= $isManageRkbmdPage ? 'active' : '' ?>">
                <a href="#" class="dropdown-toggle-admin">
                    <i class="fas fa-edit"></i>
                    <span>Kelola Data RKBMD</span>
                    <i class="fas fa-chevron-down arrow-admin"></i>
                </a>
                <ul class="dropdown-menu-admin">
                    <li><a href="<?= BASE_URL ?>/admin/manage_pemindahtanganan.php">Pemindahtanganan</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/manage_penghapusan.php">Penghapusan</a></li>
                </ul>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/manage_rekapitulasi.php"
                    class="<?= ($current_page_admin == 'manage_rekapitulasi.php') ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Kelola Data Progres</span>
                </a>
            </li>

            <?php if ($admin_role === 'utama'): ?>
                <li class="nav-category">Administrasi</li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/control_panel.php"
                        class="<?= ($current_page_admin == 'control_panel.php') ? 'active' : '' ?>">
                        <i class="fas fa-cogs"></i>
                        <span>Pusat Pengaturan</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/manage_admins.php"
                        class="<?= ($current_page_admin == 'manage_admins.php') ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Manajemen Admin</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/admin/trash_bin.php"
                        class="<?= ($current_page_admin == 'trash_bin.php') ? 'active' : '' ?>">
                        <i class="fas fa-trash-restore"></i>
                        <span>Keranjang Sampah</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-category">Akun</li>
            <li>
                <a href="<?= BASE_URL ?>/admin/profile.php"
                    class="<?= ($current_page_admin == 'profile.php') ? 'active' : '' ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>