<?php
require_once __DIR__ . '/../config.php';

// Keamanan
if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ditolak.");
}

$type = $_POST['type'] ?? '';
$skpd_name = $_POST['skpd_name'] ?? '';
// Variabel $items tidak lagi digunakan karena fitur edit dihilangkan
// $items = $_POST['items'] ?? [];
$delete_ids = $_POST['delete_ids'] ?? [];

$typeMap = [
    'pemindahtanganan' => 'pemindahtanganan',
    'penghapusan' => 'penghapusan'
];

if (!array_key_exists($type, $typeMap) || empty($skpd_name)) {
    die("Akses tidak valid.");
}

$tableName = $typeMap[$type];
$redirect_url = "detail_skpd.php?type=$type&skpd=" . urlencode($skpd_name);

// Cek apakah ada data yang dipilih untuk dihapus
if (empty($delete_ids)) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Tidak ada data yang dipilih untuk dihapus.'];
    header("Location: $redirect_url");
    exit();
}

$db->begin_transaction();
try {
    // Proses Hapus (Soft Delete)
    $ids_placeholder = implode(',', array_fill(0, count($delete_ids), '?'));
    $stmt_delete = $db->prepare("UPDATE $tableName SET is_deleted = 1 WHERE id IN ($ids_placeholder)");
    $types = str_repeat('i', count($delete_ids));
    $stmt_delete->bind_param($types, ...$delete_ids);
    $stmt_delete->execute();
    
    $deleted_count = $stmt_delete->affected_rows;

    $db->commit();
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => $deleted_count . ' data berhasil dihapus.'];

} catch (Exception $e) {
    $db->rollback();
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
}

header("Location: $redirect_url");
exit();
?>
