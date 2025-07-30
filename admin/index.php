<?php
// Memuat header dan sidebar admin
require_once __DIR__ . '/templates/header.php';

// Ambil data untuk statistik dashboard
$total_pemindahtanganan = $db->query("SELECT COUNT(id) as count, SUM(nilai_perolehan) as total_nilai FROM pemindahtanganan WHERE is_deleted = 0")->fetch_assoc();
$total_penghapusan = $db->query("SELECT COUNT(id) as count, SUM(nilai_perolehan) as total_nilai FROM penghapusan WHERE is_deleted = 0")->fetch_assoc();
$total_rekap = $db->query("SELECT COUNT(id) as count FROM rekapitulasi_progres WHERE is_deleted = 0")->fetch_assoc()['count'];
$total_admins = $db->query("SELECT COUNT(id) as count FROM admins WHERE is_deleted = 0 AND role = 'input'")->fetch_assoc()['count'];

// Data untuk Chart
$usulan_per_bulan_query = $db->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan, 
        COUNT(id) as jumlah 
    FROM penghapusan 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND is_deleted = 0
    GROUP BY bulan 
    ORDER BY bulan ASC
");
$chart_labels = [];
$chart_values = [];
while($row = $usulan_per_bulan_query->fetch_assoc()){
    $chart_labels[] = date("M Y", strtotime($row['bulan']."-01"));
    $chart_values[] = $row['jumlah'];
}
?>

<div class="container-fluid">
    <!-- Page Title -->
    <h1 class="page-title-admin-content">Dashboard Admin</h1>
    <p class="page-subtitle-admin">Selamat datang kembali, <strong><?= htmlspecialchars($admin_username) ?></strong>! Berikut adalah ringkasan aktivitas sistem.</p>

    <!-- Kartu Statistik (Layout Diperbarui) -->
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-admin bg-primary">
                <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= number_format($total_pemindahtanganan['count']) ?></div>
                    <div class="stat-label">Data Pemindahtanganan</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-admin bg-danger">
                <div class="stat-icon"><i class="fas fa-trash-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= number_format($total_penghapusan['count']) ?></div>
                    <div class="stat-label">Data Penghapusan</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-admin bg-warning">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= number_format($total_rekap) ?></div>
                    <div class="stat-label">Data Progres Rekap</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-admin bg-success">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-info">
                    <div class="stat-number">Rp <?= number_format($total_pemindahtanganan['total_nilai'] ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Nilai Pemindahtanganan</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-admin bg-secondary">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <div class="stat-number">Rp <?= number_format($total_penghapusan['total_nilai'] ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Nilai Penghapusan</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card-admin bg-info">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= number_format($total_admins) ?></div>
                    <div class="stat-label">Admin Input Data</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart dan Pintasan -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card-admin">
                <div class="card-header-admin">
                    <h3 class="card-title-admin">Tren Usulan Penghapusan (6 Bulan Terakhir)</h3>
                </div>
                <div class="card-body-admin">
                    <canvas id="trendsChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-admin">
                <div class="card-header-admin">
                    <h3 class="card-title-admin">Pintasan Cepat</h3>
                </div>
                <div class="card-body-admin">
                    <a href="input_data.php" class="quick-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Input Data Baru</span>
                    </a>
                    <?php if ($admin_role === 'utama'): ?>
                    <a href="control_panel.php" class="quick-link">
                        <i class="fas fa-cogs"></i>
                        <span>Pusat Pengaturan</span>
                    </a>
                    <a href="manage_admins.php" class="quick-link">
                        <i class="fas fa-users-cog"></i>
                        <span>Manajemen Akun Admin</span>
                    </a>
                    <?php endif; ?>
                    <a href="profile.php" class="quick-link">
                        <i class="fas fa-user-edit"></i>
                        <span>Ubah Profil & Password</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Khusus Dashboard Admin */
.page-title-admin-content { font-size: 1.8rem; font-weight: 700; margin-bottom: 5px; }
.page-subtitle-admin { margin-bottom: 25px; color: #6c757d; }
body.dark-mode .page-subtitle-admin { color: #95a5a6; }
.row { display: flex; flex-wrap: wrap; margin: 0 -12.5px; }
.col-lg-4, .col-lg-8, .col-md-6 { padding: 0 12.5px; margin-bottom: 25px; }
.col-lg-4 { width: 33.33%; } .col-lg-8 { width: 66.67%; } .col-md-6 { width: 50%; }
@media(max-width: 992px) { .col-lg-4, .col-lg-8 { width: 50%; } }
@media(max-width: 768px) { .col-lg-4, .col-lg-8, .col-md-6 { width: 100%; } }

.stat-card-admin { display: flex; align-items: center; padding: 25px; border-radius: 8px; color: white; }
.stat-card-admin.bg-primary { background: linear-gradient(45deg, #3498db, #2980b9); }
.stat-card-admin.bg-danger { background: linear-gradient(45deg, #e74c3c, #c0392b); }
.stat-card-admin.bg-warning { background: linear-gradient(45deg, #f1c40f, #f39c12); }
.stat-card-admin.bg-info { background: linear-gradient(45deg, #1abc9c, #16a085); }
.stat-card-admin.bg-success { background: linear-gradient(45deg, #2ecc71, #27ae60); }
.stat-card-admin.bg-secondary { background: linear-gradient(45deg, #9b59b6, #8e44ad); }

.stat-icon { font-size: 2.5rem; margin-right: 20px; opacity: 0.5; }
.stat-info { overflow: hidden; }
.stat-number { font-size: 1.8rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stat-label { font-size: 0.9rem; opacity: 0.9; }

.quick-link { display: flex; align-items: center; padding: 15px; border-radius: 6px; margin-bottom: 10px; background-color: var(--admin-bg-light); color: var(--admin-text-dark); transition: all 0.3s ease; }
body.dark-mode .quick-link { background-color: var(--admin-bg-dark); color: var(--admin-text-light); }
.quick-link:hover { background-color: var(--admin-accent); color: white; transform: translateX(5px); }
.quick-link i { font-size: 1.2rem; width: 30px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const trendsCtx = document.getElementById('trendsChart')?.getContext('2d');
    if (trendsCtx) {
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Jumlah Usulan',
                    data: <?= json_encode($chart_values) ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>

<?php
// Memuat footer admin
require_once __DIR__ . '/templates/footer.php';
?>
