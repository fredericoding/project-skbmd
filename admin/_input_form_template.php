<?php
// Template ini dipanggil oleh file input_*
// Variabel $dataType, $inputMethod, dan $columns sudah didefinisikan sebelumnya.

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
            <?php foreach ($columns as $col): 
                $field_name = strtolower(str_replace(' ', '_', $col));
            ?>
            <div class="form-group">
                <label for="<?= $field_name ?>"><?= $col ?></label>
                <textarea id="<?= $field_name ?>" name="<?= $field_name ?>" class="form-control multi-input" rows="5"></textarea>
            </div>
            <?php endforeach; ?>
        </div>
        
        <button type="submit" class="btn btn-primary mt-3">Simpan Data</button>
    </form>
<?php else: ?>
    <form action="process_input.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="data_type" value="<?= $dataType ?>">
        <input type="hidden" name="input_method" value="otomatis">

        <div class="template-info">
            <p><strong>Penting:</strong> Pastikan file Excel Anda memiliki header kolom yang sesuai dengan template.</p>
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

<!-- CSS dan JS hanya perlu ada di satu tempat, bisa ditambahkan ke admin_style.css dan admin_script.js jika belum ada -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const skpdInput = document.getElementById('nama_skpd');
    const suggestionsContainer = document.getElementById('skpd-suggestions');
    
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
