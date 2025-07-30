<?php
// Memuat file konfigurasi. Path disesuaikan karena file ini ada di dalam sub-folder.
require_once __DIR__ . '/../../config.php';

// Pastikan request datang dari metode POST dan parameter skpd_name ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skpd_name'])) {
    $skpd_name = $_POST['skpd_name'];

    // Siapkan query untuk mengambil data detail
    $query = "SELECT kode_barang, nama_barang, bentuk_pemindahtanganan, alasan, jumlah_barang, nilai_perolehan 
              FROM pemindahtanganan 
              WHERE nama_skpd = ? AND is_deleted = 0 
              ORDER BY id ASC";
    
    $stmt = $db->prepare($query);
    if ($stmt) {
        $stmt->bind_param('s', $skpd_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Mulai membuat output HTML
        $output = '<h4 class="detail-title">Rincian Barang untuk: ' . htmlspecialchars($skpd_name) . '</h4>';
        $output .= '<div class="table-container">';
        $output .= '<table>';
        $output .= '<thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Bentuk</th>
                            <th>Alasan</th>
                            <th>Jumlah</th>
                            <th>Nilai Perolehan</th>
                        </tr>
                    </thead>';
        $output .= '<tbody>';

        if ($result->num_rows > 0) {
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                $output .= '<tr>';
                $output .= '<td>' . $no++ . '</td>';
                $output .= '<td>' . htmlspecialchars($row['kode_barang'] ?: '-') . '</td>';
                $output .= '<td>' . htmlspecialchars($row['nama_barang'] ?: '-') . '</td>';
                $output .= '<td>' . htmlspecialchars($row['bentuk_pemindahtanganan'] ?: '-') . '</td>';
                $output .= '<td>' . htmlspecialchars($row['alasan'] ?: '-') . '</td>';
                $output .= '<td>' . htmlspecialchars($row['jumlah_barang'] ?: '-') . '</td>';
                $output .= '<td>Rp ' . number_format($row['nilai_perolehan'], 2, ',', '.') . '</td>';
                $output .= '</tr>';
            }
        } else {
            $output .= '<tr><td colspan="7" class="text-center">Tidak ada data rincian yang ditemukan.</td></tr>';
        }

        $output .= '</tbody></table></div>';
        $stmt->close();

        // Kirimkan output HTML
        echo $output;

    } else {
        echo '<div class="loader" style="color: red;">Query gagal disiapkan.</div>';
    }
} else {
    // Jika diakses langsung atau tanpa parameter
    http_response_code(400);
    echo 'Akses tidak valid.';
}

// Menambahkan style khusus untuk tabel detail
echo '<style>
    .detail-title { margin-bottom: 15px; font-weight: 600; color: var(--primary-color); }
    .detail-container table { font-size: 0.9rem; }
    .detail-container th, .detail-container td { padding: 10px; }
</style>';

?>
