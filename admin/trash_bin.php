<?php
require_once __DIR__ . '/templates/header.php';

// Keamanan
if ($admin_role !== 'utama') {
    die("Akses ditolak.");
}

// Ambil data dari semua tabel yang di-soft delete
$pemindahtanganan = $db->query("SELECT id, nama_skpd, nama_barang FROM pemindahtanganan WHERE is_deleted = 1");
$penghapusan = $db->query("SELECT id, nama_skpd, nama_barang FROM penghapusan WHERE is_deleted = 1");
$rekapitulasi = $db->query("SELECT id, nama_skpd, perihal FROM rekapitulasi_progres WHERE is_deleted = 1");
$admins = $db->query("SELECT id, username FROM admins WHERE is_deleted = 1");
?>
<div class="container-fluid">
    <h1 class="page-title-admin-content">Keranjang Sampah</h1>
    <p class="page-subtitle-admin">Data yang dihapus akan masuk ke sini. Anda dapat memulihkan atau menghapusnya secara permanen.</p>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>"><?= $_SESSION['flash_message']['message'] ?></div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php
    function render_trash_table($title, $type, $result) {
        if ($result->num_rows > 0) {
            echo '<div class="card-admin">';
            echo '<div class="card-header-admin"><h3 class="card-title-admin">' . $title . '</h3></div>';
            echo '<div class="card-body-admin"><div class="table-responsive"><table class="table">';
            echo '<thead><tr><th>ID</th><th>Info Utama</th><th>Aksi</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                $info = isset($row['nama_barang']) ? $row['nama_skpd'] . ' - ' . $row['nama_barang'] : (isset($row['perihal']) ? $row['nama_skpd'] . ' - ' . $row['perihal'] : $row['username']);
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($info) . '</td>';
                echo '<td>
                        <a href="process_trash.php?action=restore&type='.$type.'&id='.$row['id'].'" class="btn-action btn-restore">Pulihkan</a>
                        <a href="process_trash.php?action=delete_permanent&type='.$type.'&id='.$row['id'].'" class="btn-action btn-delete" onclick="return confirm(\'DATA INI AKAN HILANG PERMANEN! Lanjutkan?\')">Hapus Permanen</a>
                      </td>';
                echo '</tr>';
            }
            echo '</tbody></table></div></div></div>';
        }
    }

    render_trash_table('Data Pemindahtanganan Terhapus', 'pemindahtanganan', $pemindahtanganan);
    render_trash_table('Data Penghapusan Terhapus', 'penghapusan', $penghapusan);
    render_trash_table('Data Rekapitulasi Terhapus', 'rekapitulasi', $rekapitulasi);
    render_trash_table('Akun Admin Terhapus', 'admin', $admins);
    ?>
</div>
<style>
.btn-restore { background-color: #27ae60; }
</style>
<link rel="stylesheet" href="assets/css/admin_style.css">
<?php require_once __DIR__ . '/templates/footer.php'; ?>
