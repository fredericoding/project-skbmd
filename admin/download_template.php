<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Memastikan PhpSpreadsheet ter-load

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Keamanan: Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$type = $_GET['type'] ?? '';
$headers = [];
$filename = 'template.xlsx';

switch ($type) {
    case 'pemindahtanganan':
        $filename = 'template_pemindahtanganan.xlsx';
        $headers = ['No', 'SKPD', 'Kode Barang', 'Nama Barang', 'Spesifikasi Nama Barang', 'NIBAR', 'Jumlah Barang', 'Satuan', 'Lokasi', 'Nilai Perolehan', 'Bentuk Pemindahtanganan', 'Alasan Rencana Pemindahtanganan', 'Keterangan'];
        break;
    case 'penghapusan':
        $filename = 'template_penghapusan.xlsx';
        $headers = ['No', 'SKPD', 'Kode Barang', 'Nama Barang', 'Spesifikasi Nama Barang', 'NIBAR', 'Nilai Perolehan', 'Alasan Rencana Penghapusan', 'Keterangan', 'Jumlah Barang'];
        break;
    case 'rekapitulasi':
        $filename = 'template_rekapitulasi.xlsx';
        $headers = ['No','Nama SKPD','Tanggal Usulan','Nomor Usulan','Perihal','Tanggal Pembahasan','Nomor Pembahasan','Tanggal Persetujuan','Nomor Persetujuan','Tanggal Pemusnahan/Penjualan','Nomor Pemusnahan/Penjualan','Tanggal STS','Nomor STS','Nilai Jual','Tanggal SK Pengelola Barang','Nomor SK Pengelola Barang','KIB','Nilai Aset','Eksekusi SIMAS'];
        break;
    default:
        die("Jenis template tidak valid.");
}

// Membuat objek spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Menulis header ke baris pertama
$column = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($column . '1', $header);
    $column++;
}

// Mengatur header HTTP untuk memaksa download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Membuat writer dan mengirimkan file ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();