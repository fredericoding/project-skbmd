<?php
/**
 * =================================================================
 * File Konfigurasi Utama (config.php)
 * =================================================================
 * - Mengatur koneksi ke database.
 * - Membuat BASE_URL dinamis untuk path aset dan navigasi.
 * - Mengatur zona waktu default.
 * - Versi 1.2: Logika BASE_URL diperbaiki agar lebih robust.
 */

// --- 1. PENGATURAN KONEKSI DATABASE ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_skbmd');

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    die("Gagal terhubung ke database: " . $db->connect_error);
}

// --- 2. PENGATURAN BASE URL DINAMIS (LOGIKA BARU YANG DIPERBAIKI) ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$domainName = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];

// Logika ini mengasumsikan 'config.php' ada di root proyek.
// Ini akan selalu menghasilkan path yang benar ke root proyek.
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$projectName = basename(__DIR__);
$baseURLPath = preg_replace('/' . preg_quote($projectName, '/') . '.*/', '', $scriptDir) . '/' . $projectName;
// Menghapus duplikasi slash dan slash di akhir
$baseURLPath = rtrim(preg_replace('#/+#', '/', $baseURLPath), '/');


$base_url = $protocol . $domainName . $baseURLPath;

define('BASE_URL', $base_url);
define('ASSETS_URL', BASE_URL . '/assets');


// --- 3. PENGATURAN LAINNYA ---
date_default_timezone_set('Asia/Jakarta');
if (!session_id()) {
    session_start();
}
?>
