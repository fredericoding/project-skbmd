<?php
require_once __DIR__ . '/templates/header.php';

// Validasi input
$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tableName = '';
$columns = [];

// Tentukan tabel dan kolom berdasarkan tipe
$typeMap = [
    'pemindahtanganan' => [
        'table' => 'pemindahtanganan',
        'cols' => ['nama_skpd', 'kode_barang', 'nama_barang', 'spesifikasi', 'nibar', 'jumlah_barang', 'lokasi', 'nilai_perolehan', 'bentuk_pemindahtanganan', 'alasan', 'keterangan']
    ],
    'penghapusan' => [
        'table' => 'penghapusan',
        'cols' => ['nama_skpd', 'kode_barang', 'nama_barang', 'spesifikasi', 'nibar', 'jumlah_barang', 'nilai_perolehan', 'alasan', 'keterangan']
    ],
    'rekapitulasi' => [
        'table' => 'rekapitulasi_progres',
        'cols' => ['nama_skpd', 'tanggal_usulan', 'nomor_usulan', 'perihal', 'tanggal_pembahasan', 'nomor_pembahasan', 'tanggal_persetujuan', 'nomor_persetujuan', 'tanggal_pemusnahan_penjualan', 'nomor_pemusnahan_penjualan', 'tanggal_sts', 'nomor_sts', 'nilai_jual', 'tanggal_sk_pengelola', 'nomor_sk_pengelola', 'kib', 'nilai_aset', 'eksekusi_simas']
    ]
];

if (!array_key_exists($type, $typeMap) || $id <= 0) {
    die("Akses tidak valid.");
}

$tableName = $typeMap[$type]['table'];
$columns = $typeMap[$type]['cols'];

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $set_clauses = [];
    $params = [];
    $types = '';
    foreach ($columns as $col) {
        $set_clauses[] = "$col = ?";
        // Untuk tanggal kosong, set ke NULL
        if (strpos($col, 'tanggal') !== false && empty($_POST[$col])) {
            $params[] = null;
        } else {
            $params[] = $_POST[$col];
        }
        $types .= 's';
    }
    $params[] = $id;
    $types .= 'i';

    $sql = "UPDATE $tableName SET " . implode(', ', $set_clauses) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if($stmt->execute()){
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data berhasil diperbarui.'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal memperbarui data.'];
    }
    header("Location: manage_$type.php");
    exit();
}

// Ambil data yang akan diedit
$stmt = $db->prepare("SELECT * FROM $tableName WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan.");
}
?>
<div class="container-fluid">
    <h1 class="page-title-admin-content">Edit Data <?= ucfirst($type) ?></h1>

    <div class="card-admin">
        <div class="card-body-admin">
            <form action="" method="POST">
                <div class="form-grid">
                    <?php foreach ($columns as $col): 
                        $label = ucwords(str_replace('_', ' ', $col));
                        $is_date = strpos($col, 'tanggal') !== false;
                        $input_type = $is_date ? 'date' : 'text';
                        if (in_array($col, ['alasan', 'keterangan', 'spesifikasi', 'perihal'])) {
                            $input_type = 'textarea';
                        }
                    ?>
                    <div class="form-group">
                        <label for="<?= $col ?>"><?= $label ?></label>
                        <?php if($input_type == 'textarea'): ?>
                            <textarea name="<?= $col ?>" id="<?= $col ?>" class="form-control" rows="3"><?= htmlspecialchars($data[$col]) ?></textarea>
                        <?php else: ?>
                            <input type="<?= $input_type ?>" name="<?= $col ?>" id="<?= $col ?>" class="form-control" value="<?= htmlspecialchars($data[$col]) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
                <a href="manage_<?= $type ?>.php" class="btn btn-secondary mt-3">Batal</a>
            </form>
        </div>
    </div>
</div>
<style>
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 8px; }
.form-control { width: 100%; padding: 12px; border: 1px solid var(--admin-border-light); border-radius: 6px; }
.mt-3 { margin-top: 1.5rem; }
.btn { padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: 500; }
.btn-primary { background-color: var(--admin-accent); color: white; border: none; }
.btn-secondary { background-color: var(--admin-bg-light); color: var(--admin-text-dark); border: 1px solid var(--admin-border-light); }
</style>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
