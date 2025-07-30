<?php
require_once __DIR__ . '/templates/header.php';

// Validasi input
$type = $_GET['type'] ?? '';
$skpd_name = $_GET['skpd'] ?? '';
$tableName = '';
$columns = [];

$typeMap = [
    'pemindahtanganan' => [
        'table' => 'pemindahtanganan',
        'cols' => ['kode_barang', 'nama_barang', 'spesifikasi', 'nibar', 'jumlah_barang', 'lokasi', 'nilai_perolehan', 'bentuk_pemindahtanganan', 'alasan', 'keterangan']
    ],
    'penghapusan' => [
        'table' => 'penghapusan',
        'cols' => ['kode_barang', 'nama_barang', 'spesifikasi', 'nibar', 'jumlah_barang', 'nilai_perolehan', 'alasan', 'keterangan']
    ]
];

if (!array_key_exists($type, $typeMap) || empty($skpd_name)) {
    die("Akses tidak valid.");
}

$tableName = $typeMap[$type]['table'];
$columns = $typeMap[$type]['cols'];

// Ambil semua data untuk SKPD ini
$stmt = $db->prepare("SELECT * FROM $tableName WHERE nama_skpd = ? AND is_deleted = 0 ORDER BY id ASC");
$stmt->bind_param('s', $skpd_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">
    <h1 class="page-title-admin-content">Detail Data: <?= htmlspecialchars($skpd_name) ?></h1>
    <p class="page-subtitle-admin">Edit data di bawah ini secara massal dan klik simpan. Centang kotak untuk menghapus data.</p>

    <div class="card-admin">
        <form action="process_bulk_edit.php" method="POST" id="bulk-edit-form">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="hidden" name="skpd_name" value="<?= htmlspecialchars($skpd_name) ?>">
            
            <div class="card-body-admin">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>"><?= $_SESSION['flash_message']['message'] ?></div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-edit">
                        <thead>
                            <tr>
                                <th>Hapus</th>
                                <?php foreach ($columns as $col): ?>
                                    <th><?= ucwords(str_replace('_', ' ', $col)) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="delete_ids[]" value="<?= $row['id'] ?>" class="form-check">
                                </td>
                                <?php foreach ($columns as $col): 
                                    $is_textarea = in_array($col, ['alasan', 'keterangan', 'spesifikasi']);
                                ?>
                                    <td>
                                        <?php if($is_textarea): ?>
                                            <textarea name="items[<?= $row['id'] ?>][<?= $col ?>]" class="form-control"><?= htmlspecialchars($row[$col]) ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="items[<?= $row['id'] ?>][<?= $col ?>]" value="<?= htmlspecialchars($row[$col]) ?>" class="form-control">
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer-admin">
                <a href="manage_<?= $type ?>.php" class="btn btn-secondary">Kembali</a>
                <button type="button" id="save-changes-btn" class="btn btn-primary btn-save-changes">
                    <i class="fas fa-save"></i> Simpan Semua Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Overlay Konfirmasi Kustom -->
<div class="confirm-overlay" id="confirm-overlay">
    <div class="confirm-modal">
        <i class="fas fa-exclamation-triangle confirm-icon"></i>
        <h3>Konfirmasi Perubahan</h3>
        <p>Anda yakin ingin menyimpan semua perubahan dan menghapus data yang dicentang?</p>
        <div class="confirm-actions">
            <button type="button" id="confirm-cancel-btn" class="btn btn-secondary">Batal</button>
            <button type="button" id="confirm-yes-btn" class="btn btn-danger">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('save-changes-btn');
    const form = document.getElementById('bulk-edit-form');
    const overlay = document.getElementById('confirm-overlay');
    const cancelBtn = document.getElementById('confirm-cancel-btn');
    const confirmBtn = document.getElementById('confirm-yes-btn');

    if (saveBtn) {
        saveBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Mencegah form submit langsung
            overlay.classList.add('active'); // Tampilkan modal
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            overlay.classList.remove('active'); // Sembunyikan modal
        });
    }

    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active'); // Sembunyikan modal jika klik di luar
            }
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            form.submit(); // Lanjutkan submit form jika dikonfirmasi
        });
    }
});
</script>
<link rel="stylesheet" href="assets/css/admin_style.css">
<?php require_once __DIR__ . '/templates/footer.php'; ?>
