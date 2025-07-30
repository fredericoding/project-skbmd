<?php
require_once __DIR__ . '/templates/header.php';

// Mendapatkan pilihan dari URL query, default ke pemindahtanganan & manual
$dataType = $_GET['type'] ?? 'pemindahtanganan';
$inputMethod = $_GET['method'] ?? 'manual';
?>

<div class="container-fluid">
    <h1 class="page-title-admin-content">Input Data Baru</h1>
    <p class="page-subtitle-admin">Pilih jenis data dan metode input yang akan digunakan.</p>

    <!-- Navigation for Data Type and Method -->
    <div class="card-admin">
        <div class="input-selection-grid">
            <!-- Input Method Selection -->
            <div class="selection-group">
                <label>Pilih Metode Input:</label>
                <div class="btn-group">
                    <a href="?type=<?= $dataType ?>&method=manual" class="btn <?= $inputMethod == 'manual' ? 'btn-primary' : 'btn-secondary' ?>">Manual</a>
                    <a href="?type=<?= $dataType ?>&method=otomatis" class="btn <?= $inputMethod == 'otomatis' ? 'btn-primary' : 'btn-secondary' ?>">Otomatis (Excel)</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Form Area -->
    <div class="card-admin">
        <div class="card-header-admin">
            <h3 class="card-title-admin">
                Form Input <?= ucfirst($dataType) ?> - Metode <?= ucfirst($inputMethod) ?>
            </h3>
        </div>
        <div class="card-body-admin">
            <?php
            // Menampilkan notifikasi dari proses sebelumnya (jika ada)
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            if (isset($_SESSION['validation_errors'])) {
                echo '<div class="alert alert-danger"><strong>Ditemukan kesalahan validasi:</strong><br><ul>';
                foreach ($_SESSION['validation_errors'] as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul></div>';
                unset($_SESSION['validation_errors']);
            }
            ?>

            <?php if ($inputMethod == 'manual'): ?>
                <!-- ==================== FORM MANUAL ==================== -->
                <form action="process_input.php" method="POST" id="manual-form">
                    <input type="hidden" name="data_type" value="<?= $dataType ?>">
                    <input type="hidden" name="input_method" value="manual">
                    
                    <div class="form-group">
                        <label for="nama_skpd">Nama SKPD (wajib diisi)</label>
                        <input type="text" id="nama_skpd" name="nama_skpd" class="form-control" required autocomplete="off">
                        <div id="skpd-suggestions"></div>
                    </div>

                    <p class="form-instruction">Untuk kolom di bawah, pisahkan setiap entri barang dengan menekan <strong>Enter</strong>. Pastikan jumlah baris di setiap kolom sama.</p>
                    
                    <div class="multi-input-grid">
                        <?php
                        // Mendefinisikan kolom untuk setiap jenis data
                        $columns = [];
                        if ($dataType == 'pemindahtanganan') {
                            $columns = ['Kode Barang', 'Nama Barang', 'Spesifikasi Nama Barang', 'NIBAR', 'Jumlah Barang', 'Lokasi', 'Nilai Perolehan', 'Bentuk Pemindahtanganan', 'Alasan Rencana Pemindahtanganan', 'Keterangan'];
                        } elseif ($dataType == 'penghapusan') {
                            $columns = ['Kode Barang', 'Nama Barang', 'Spesifikasi Nama Barang', 'NIBAR', 'Nilai Perolehan', 'Alasan Rencana Penghapusan', 'Keterangan', 'Jumlah Barang'];
                        } else { // rekapitulasi
                            $columns = ['Nomor Usulan', 'Perihal', 'Tanggal Usulan', 'Tanggal Pembahasan', 'Nomor Pembahasan', 'Tanggal Persetujuan', 'Nomor Persetujuan', 'Tanggal Pemusnahan/Penjualan', 'Nomor Pemusnahan/Penjualan', 'Tanggal STS', 'Nomor STS', 'Nilai Jual', 'Tanggal SK Pengelola Barang', 'Nomor SK Pengelola Barang', 'KIB', 'Nilai Aset', 'Eksekusi SIMAS'];
                        }

                        foreach ($columns as $col) {
                            $field_name = strtolower(str_replace(' ', '_', $col));
                            echo '<div class="form-group">';
                            echo '<label for="' . $field_name . '">' . $col . '</label>';
                            echo '<textarea id="' . $field_name . '" name="' . $field_name . '" class="form-control multi-input" rows="5"></textarea>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3">Simpan Data</button>
                </form>

            <?php else: ?>
                <!-- ==================== FORM OTOMATIS (EXCEL) ==================== -->
                <form action="process_input.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="data_type" value="<?= $dataType ?>">
                    <input type="hidden" name="input_method" value="otomatis">

                    <div class="template-info">
                        <p><strong>Penting:</strong> Pastikan file Excel Anda memiliki header kolom yang sesuai dengan template.</p>
                        <!-- Tombol Unduh Template DITAMBAHKAN DI SINI -->
                        <a href="download_template.php?type=<?= $dataType ?>" class="btn btn-secondary btn-download-template">
                            <i class="fas fa-download"></i> Unduh Template untuk <?= ucfirst($dataType) ?>
                        </a>
                    </div>

                    <div class="form-group">
                        <label for="excel_file">Unggah File Excel (.xlsx)</label>
                        <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xlsx" required>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Unggah dan Proses</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* CSS Khusus Halaman Input (DIPERBARUI) */
.input-selection-grid {
    display: grid;
    /* Menggunakan auto-fit agar dinamis dan responsif */
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}
.selection-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
}
.btn-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap; /* Memastikan tombol tidak tumpang tindih di layar kecil */
}
.btn {
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-align: center;
}
.btn-primary {
    background-color: var(--admin-accent);
    color: white;
}
.btn-secondary {
    background-color: var(--admin-bg-light);
    color: var(--admin-text-dark);
    border: 1px solid var(--admin-border-light);
}
body.dark-mode .btn-secondary {
    background-color: var(--admin-surface-dark);
    color: var(--admin-text-light);
    border-color: var(--admin-border-dark);
}
.form-instruction {
    margin-bottom: 20px;
    padding: 15px;
    background-color: rgba(52, 152, 219, 0.1);
    border-left: 4px solid var(--admin-accent);
    border-radius: 4px;
}
.multi-input-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}
.form-group {
    margin-bottom: 15px;
    position: relative;
}
.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--admin-border-light);
    border-radius: 6px;
    background-color: var(--admin-surface-light);
    color: var(--admin-text-dark);
}
body.dark-mode .form-control {
    background-color: var(--admin-bg-dark);
    color: var(--admin-text-light);
    border-color: var(--admin-border-dark);
}
textarea.form-control {
    min-height: 120px;
    resize: vertical;
}
.template-info {
    margin-bottom: 20px;
    padding: 15px;
    background-color: var(--admin-bg-light);
    border-radius: 6px;
}
body.dark-mode .template-info {
    background-color: var(--admin-bg-dark);
}
.btn-download-template {
    margin-top: 10px;
    display: inline-block; /* Agar margin bekerja dengan benar */
}
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
#skpd-suggestions {
    position: absolute;
    background: var(--admin-surface-light);
    border: 1px solid var(--admin-border-light);
    z-index: 100;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
}
body.dark-mode #skpd-suggestions {
    background: var(--admin-surface-dark);
    border-color: var(--admin-border-dark);
}
#skpd-suggestions div {
    padding: 10px;
    cursor: pointer;
}
#skpd-suggestions div:hover {
    background-color: var(--admin-accent);
    color: white;
}
</style>

<script>
// Script untuk Autocomplete
document.addEventListener('DOMContentLoaded', () => {
    const skpdInput = document.getElementById('nama_skpd');
    const suggestionsContainer = document.getElementById('skpd-suggestions');

    // Autocomplete
    skpdInput?.addEventListener('keyup', function() {
        const query = this.value;
        if (query.length < 2) {
            suggestionsContainer.innerHTML = '';
            return;
        }
        fetch('<?= BASE_URL ?>/admin/ajax/get_skpd_autocomplete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'query=' + encodeURIComponent(query)
        })
        .then(response => response.json())
        .then(data => {
            suggestionsContainer.innerHTML = '';
            data.forEach(skpd => {
                const div = document.createElement('div');
                div.textContent = skpd;
                div.addEventListener('click', function() {
                    skpdInput.value = this.textContent;
                    suggestionsContainer.innerHTML = '';
                });
                suggestionsContainer.appendChild(div);
            });
        });
    });
});
</script>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
