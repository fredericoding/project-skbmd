<?php
// Mengambil data pengaturan lagi untuk jaga-jaga jika footer dipanggil terpisah
// Dalam praktik normal, variabel $settings dari header.php masih tersedia.
if (!isset($settings)) {
    $settings = [];
    $result = $db->query("SELECT * FROM settings");
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>

    </main> <!-- Penutup tag <main> dari header.php -->

    <!-- Footer Utama -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section about">
                    <h2 class="footer-title"><?= htmlspecialchars($settings['site_title'] ?? 'E-RASER') ?></h2>
                    <p id="footer-welcome-text">
                        Sistem digital untuk pengelolaan aset daerah yang transparan dan efisien oleh BPKAD Provinsi Jawa Timur.
                    </p>
                </div>
                <div class="footer-section links">
                    <h2 class="footer-title">Tautan Terkait</h2>
                    <ul>
                        <li><a href="https://jatimprov.go.id/" target="_blank" rel="noopener noreferrer">Website Pemprov Jatim</a></li>
                        <li><a href="https://bpkad.jatimprov.go.id/" target="_blank" rel="noopener noreferrer">Website BPKAD Jatim</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php">Dashboard</a></li>
                        <li><a href="<?= BASE_URL ?>/rekapitulasi.php">Rekapitulasi</a></li>
                    </ul>
                </div>
                <div class="footer-section contact">
                    <h2 class="footer-title">Hubungi Kami</h2>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span id="footer-address"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span id="footer-phone"><?= htmlspecialchars($settings['contact_phone'] ?? '') ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span id="footer-email"><?= htmlspecialchars($settings['contact_email'] ?? '') ?></span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom" id="footer-copyright-text">
                <?= htmlspecialchars($settings['footer_text'] ?? '© 2025 BPKAD PEMPROV JATIM') ?>
            </div>
        </div>
    </footer>

    <!-- Library JavaScript dari CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- File JavaScript Kustom -->
    <script src="<?= ASSETS_URL ?>/js/script.js?v=<?= time() ?>"></script>

</body>
</html>
