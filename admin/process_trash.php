<?php
require_once __DIR__ . '/../config.php';

// Keamanan
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'utama') {
    die("Akses ditolak.");
}

$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$typeMap = [
    'pemindahtanganan' => 'pemindahtanganan',
    'penghapusan' => 'penghapusan',
    'rekapitulasi' => 'rekapitulasi_progres',
    'admin' => 'admins'
];

if (!array_key_exists($type, $typeMap) || $id <= 0 || !in_array($action, ['restore', 'delete_permanent'])) {
    die("Akses tidak valid.");
}

$tableName = $typeMap[$type];
$sql = '';

if ($action == 'restore') {
    $sql = "UPDATE $tableName SET is_deleted = 0 WHERE id = ?";
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data berhasil dipulihkan.'];
} elseif ($action == 'delete_permanent') {
    $sql = "DELETE FROM $tableName WHERE id = ?";
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data berhasil dihapus permanen.'];
}

$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Aksi gagal dilakukan.'];
}

header("Location: trash_bin.php");
exit();
?>
