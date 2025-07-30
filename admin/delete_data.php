<?php
require_once __DIR__ . '/../config.php';

// Keamanan
if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak.");
}

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$skpd_name = $_GET['skpd'] ?? '';

$typeMap = [
    'pemindahtanganan' => 'pemindahtanganan',
    'penghapusan' => 'penghapusan',
    'rekapitulasi' => 'rekapitulasi_progres',
    'admin' => 'admins'
];

if (!array_key_exists($type, $typeMap) || ($id <= 0 && empty($skpd_name))) {
    die("Akses atau parameter tidak valid.");
}

$tableName = $typeMap[$type];
$sql = '';
$params = [];
$types = '';

if ($id > 0) {
    // Hapus satu item berdasarkan ID
    if ($type == 'admin' && $id == $_SESSION['admin_id']) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'];
        header("Location: manage_admins.php");
        exit();
    }
    $sql = "UPDATE $tableName SET is_deleted = 1 WHERE id = ?";
    $params = [$id];
    $types = 'i';
} elseif (!empty($skpd_name)) {
    // Hapus semua item berdasarkan nama SKPD
    $sql = "UPDATE $tableName SET is_deleted = 1 WHERE nama_skpd = ?";
    $params = [$skpd_name];
    $types = 's';
}

if (!empty($sql)) {
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data berhasil dipindahkan ke keranjang sampah.'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal menghapus data.'];
    }
}

$redirect_page = ($type == 'admin') ? 'manage_admins.php' : "manage_$type.php";
header("Location: $redirect_page");
exit();
?>
