<?php
require_once __DIR__ . '/templates/header.php';

// Keamanan
if ($admin_role !== 'utama') {
    die("Akses ditolak.");
}

// Ambil semua settings
$settings_query = $db->query("SELECT * FROM settings");
$settings = [];
while($row = $settings_query->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Ambil gambar slider
$slider_images = $db->query("SELECT * FROM slider_images ORDER BY sort_order ASC");
?>
<div class="container-fluid">
    <h1 class="page-title-admin-content">Pusat Pengaturan</h1>
    <form action="process_settings.php" method="POST" enctype="multipart/form-data">
        <div class="card-admin">
            <div class="card-header-admin"><h3 class="card-title-admin">Pengaturan Umum</h3></div>
            <div class="card-body-admin">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Judul Website</label>
                        <input type="text" name="settings[site_title]" value="<?= htmlspecialchars($settings['site_title']) ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Data per Halaman (Pagination)</label>
                        <input type="number" name="settings[pagination_limit]" value="<?= htmlspecialchars($settings['pagination_limit']) ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teks Sambutan Dashboard</label>
                        <textarea name="settings[welcome_text]" class="form-control" rows="4"><?= htmlspecialchars($settings['welcome_text']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Logo Website (kosongkan jika tidak diubah)</label>
                        <input type="file" name="site_logo" class="form-control">
                        <img src="<?= BASE_URL . '/' . $settings['site_logo'] ?>" alt="Logo saat ini" style="max-height: 50px; margin-top: 10px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-admin">
            <div class="card-header-admin"><h3 class="card-title-admin">Pengaturan Kontak (Footer)</h3></div>
            <div class="card-body-admin">
                <div class="form-grid">
                    <div class="form-group"><label>Alamat</label><input type="text" name="settings[contact_address]" value="<?= htmlspecialchars($settings['contact_address']) ?>" class="form-control"></div>
                    <div class="form-group"><label>Telepon</label><input type="text" name="settings[contact_phone]" value="<?= htmlspecialchars($settings['contact_phone']) ?>" class="form-control"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="settings[contact_email]" value="<?= htmlspecialchars($settings['contact_email']) ?>" class="form-control"></div>
                </div>
            </div>
        </div>

        <div class="card-admin">
            <div class="card-header-admin"><h3 class="card-title-admin">Manajemen Slider Dashboard</h3></div>
            <div class="card-body-admin">
                <div class="form-group">
                    <label>Tambah Gambar Baru (bisa pilih banyak)</label>
                    <input type="file" name="slider_images[]" class="form-control" multiple>
                </div>
                <div class="slider-preview-grid">
                    <?php while($img = $slider_images->fetch_assoc()): ?>
                    <div class="slider-preview-item">
                        <img src="<?= BASE_URL . '/' . $img['image_path'] ?>">
                        <a href="process_settings.php?action=delete_slider&id=<?= $img['id'] ?>" class="delete-slider-btn" onclick="return confirm('Yakin?')">&times;</a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan Semua Pengaturan</button>
    </form>
</div>
<style>
.slider-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
.slider-preview-item { position: relative; }
.slider-preview-item img { width: 100%; height: 100px; object-fit: cover; border-radius: 6px; }
.delete-slider-btn { position: absolute; top: 5px; right: 5px; background: rgba(192, 57, 43, 0.8); color: white; border: none; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; line-height: 1; text-decoration: none; }
</style>
<link rel="stylesheet" href="assets/css/admin_style.css">
<?php require_once __DIR__ . '/templates/footer.php'; ?>
