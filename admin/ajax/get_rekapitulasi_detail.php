<?php
// Memuat file konfigurasi
require_once __DIR__ . '/../../config.php';

// Pastikan request datang dari metode POST dan parameter id ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Siapkan query untuk mengambil data detail lengkap
    $query = "SELECT * FROM rekapitulasi_progres WHERE id = ? AND is_deleted = 0";
    
    $stmt = $db->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Fungsi helper untuk menampilkan baris detail
            function render_detail_row($label, $value, $is_currency = false, $is_date = false) {
                $formatted_value = htmlspecialchars($value ?: '-');
                if ($value) {
                    if ($is_currency) {
                        $formatted_value = 'Rp ' . number_format($value, 2, ',', '.');
                    }
                    if ($is_date) {
                        $formatted_value = date('d F Y', strtotime($value));
                    }
                }
                return '<div class="detail-item"><span class="detail-label">' . $label . '</span><span class="detail-value">' . $formatted_value . '</span></div>';
            }

            // Mulai membuat output HTML
            $output = '<div class="detail-grid">';
            $output .= '<h4 class="detail-title grid-span-2">Rincian Lengkap Usulan</h4>';
            
            $output .= render_detail_row('Nama SKPD', $row['nama_skpd']);
            $output .= render_detail_row('Perihal', $row['perihal']);
            $output .= render_detail_row('Tanggal Usulan', $row['tanggal_usulan'], false, true);
            $output .= render_detail_row('Nomor Usulan', $row['nomor_usulan']);
            $output .= render_detail_row('Tanggal Pembahasan', $row['tanggal_pembahasan'], false, true);
            $output .= render_detail_row('Nomor Pembahasan', $row['nomor_pembahasan']);
            $output .= render_detail_row('Tanggal Persetujuan Gubernur', $row['tanggal_persetujuan'], false, true);
            $output .= render_detail_row('Nomor Persetujuan Gubernur', $row['nomor_persetujuan']);
            $output .= render_detail_row('Tanggal SK Pengelola Barang', $row['tanggal_sk_pengelola'], false, true);
            $output .= render_detail_row('Nomor SK Pengelola Barang', $row['nomor_sk_pengelola']);
            $output .= render_detail_row('Tanggal Eksekusi', $row['tanggal_pemusnahan_penjualan'], false, true);
            $output .= render_detail_row('Nomor Eksekusi', $row['nomor_pemusnahan_penjualan']);
            $output .= render_detail_row('Tanggal STS', $row['tanggal_sts'], false, true);
            $output .= render_detail_row('Nomor STS', $row['nomor_sts']);
            $output .= render_detail_row('KIB', $row['kib']);
            $output .= render_detail_row('Eksekusi SIMAS', $row['eksekusi_simas']);
            $output .= render_detail_row('Nilai Aset', $row['nilai_aset'], true);
            $output .= render_detail_row('Nilai Jual', $row['nilai_jual'], true);

            $output .= '</div>';

        } else {
            $output = '<div class="loader" style="color: red;">Data tidak ditemukan.</div>';
        }

        $stmt->close();
        echo $output;

    } else {
        echo '<div class="loader" style="color: red;">Query gagal disiapkan.</div>';
    }
} else {
    http_response_code(400);
    echo 'Akses tidak valid.';
}

// Menambahkan style khusus untuk tampilan detail
echo '<style>
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .detail-title.grid-span-2 { grid-column: 1 / -1; margin-bottom: 10px; font-weight: 600; color: var(--primary-color); border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
    .detail-item { display: flex; flex-direction: column; background-color: var(--surface-color); padding: 10px; border-radius: 6px; }
    .detail-label { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; }
    .detail-value { font-size: 1rem; font-weight: 500; color: var(--text-primary); }
    @media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
</style>';

?>
