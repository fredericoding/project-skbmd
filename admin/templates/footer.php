<?php
// File ini dipanggil di akhir setiap halaman admin.
?>
        </main> <!-- Penutup tag <main class="admin-page-content"> -->
    </div> <!-- Penutup tag <div class="main-content-admin"> -->
</div> <!-- Penutup tag <div class="admin-wrapper"> -->

<!-- Library JavaScript dari CDN (jika ada yang spesifik admin) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Untuk dashboard admin -->

<!-- File JavaScript Kustom untuk Admin -->
<script src="<?= BASE_URL ?>/admin/assets/js/admin_script.js?v=<?= time() ?>"></script>

</body>
</html>
