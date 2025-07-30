<?php
// Memuat header halaman pengunjung
require_once __DIR__ . '/templates/header.php';

// --- PENGAMBILAN DATA UNTUK STATISTIK ---

// 1. Ambil data slider dari database
$slider_images_query = $db->query("SELECT image_path FROM slider_images ORDER BY sort_order ASC");
$slider_images = [];
while($row = $slider_images_query->fetch_assoc()) {
    $slider_images[] = $row;
}

// 2. Ambil data untuk kartu statistik
$total_skpd = $db->query("SELECT COUNT(DISTINCT nama_skpd) as count FROM rekapitulasi_progres WHERE is_deleted = 0")->fetch_assoc()['count'];
$total_penghapusan = $db->query("SELECT COUNT(id) as count FROM penghapusan WHERE is_deleted = 0")->fetch_assoc()['count'];
$total_pemindahtanganan = $db->query("SELECT COUNT(id) as count FROM pemindahtanganan WHERE is_deleted = 0")->fetch_assoc()['count'];
$total_progres = $db->query("SELECT COUNT(id) as count FROM rekapitulasi_progres WHERE is_deleted = 0")->fetch_assoc()['count'];

// 3. Ambil data untuk chart
// a. Bar Chart (Jumlah Usulan per Jenis)
$bar_chart_data = [
    'labels' => ['Penghapusan', 'Pemindahtanganan'],
    'values' => [$total_penghapusan, $total_pemindahtanganan]
];

// b. Donut Chart (Persentase Progres)
$tahap_skpd = $db->query("SELECT COUNT(id) as count FROM rekapitulasi_progres WHERE tanggal_persetujuan IS NULL AND is_deleted = 0")->fetch_assoc()['count'];
$tahap_persetujuan = $db->query("SELECT COUNT(id) as count FROM rekapitulasi_progres WHERE tanggal_persetujuan IS NOT NULL AND tanggal_pemusnahan_penjualan IS NULL AND is_deleted = 0")->fetch_assoc()['count'];
$tahap_selesai = $db->query("SELECT COUNT(id) as count FROM rekapitulasi_progres WHERE tanggal_pemusnahan_penjualan IS NOT NULL AND is_deleted = 0")->fetch_assoc()['count'];
$donut_chart_data = [
    'labels' => ['Tahap Awal (SKPD)', 'Tahap Persetujuan', 'Selesai'],
    'values' => [$tahap_skpd, $tahap_persetujuan, $tahap_selesai]
];

// c. Line Chart (Tren Nilai Aset per Bulan - Contoh untuk 12 bulan terakhir)
$line_chart_query = $db->query("
    SELECT 
        DATE_FORMAT(tanggal_usulan, '%Y-%m') as bulan, 
        SUM(nilai_aset) as total_nilai 
    FROM rekapitulasi_progres 
    WHERE tanggal_usulan >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND is_deleted = 0
    GROUP BY bulan 
    ORDER BY bulan ASC
");
$line_chart_labels = [];
$line_chart_values = [];
while($row = $line_chart_query->fetch_assoc()){
    $line_chart_labels[] = date("M Y", strtotime($row['bulan']."-01"));
    $line_chart_values[] = $row['total_nilai'];
}
$line_chart_data = [
    'labels' => $line_chart_labels,
    'values' => $line_chart_values
];

?>

<!-- Bagian Slider Gambar -->
<div class="container mt-1">
    <div class="swiper-container card">
        <div class="swiper-wrapper">
            <?php if (!empty($slider_images)): ?>
                <?php foreach ($slider_images as $image): ?>
                    <div class="swiper-slide">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($image['image_path']) ?>" alt="Gambar Slider">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Placeholder jika tidak ada gambar di database -->
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="swiper-slide">
                    <img src="https://placehold.co/1200x500/3a5a83/FFFFFF?text=Gambar+Slider+<?= $i ?>" alt="Placeholder Slider">
                </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Add Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

<div class="container">
    <!-- Bagian Sambutan -->
    <div class="card text-center">
        <h1 class="page-title">Selamat Datang di <?= htmlspecialchars($settings['site_title'] ?? 'E-RASER') ?></h1>
        <p class="welcome-text" style="font-size: 1.1rem; max-width: 800px; margin: 0 auto;">
            <?= htmlspecialchars($settings['welcome_text'] ?? 'Teks sambutan default.') ?>
        </p>
    </div>

    <!-- Bagian Kartu Statistik -->
    <!-- <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-building fa-3x text-primary"></i>
            <div class="stat-value"><?= number_format($total_skpd) ?></div>
            <div class="stat-label">Total SKPD Terlibat</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-file-alt fa-3x text-primary"></i>
            <div class="stat-value"><?= number_format($total_progres) ?></div>
            <div class="stat-label">Total Usulan Diproses</div>
        </div> -->
        <div class="stat-card">
            <i class="fas fa-trash-alt fa-3x text-primary"></i>
            <div class="stat-value"><?= number_format($total_penghapusan) ?></div>
            <div class="stat-label">Barang Usulan Penghapusan</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-exchange-alt fa-3x text-primary"></i>
            <div class="stat-value"><?= number_format($total_pemindahtanganan) ?></div>
            <div class="stat-label">Barang Usulan Pemindahtanganan</div>
        </div>
    </div>

    <!-- Bagian Visualisasi Data -->
    <div class="chart-grid">
        <div class="card chart-card">
            <h3 class="card-title">Jumlah Usulan per Jenis</h3>
            <canvas id="barChart"></canvas>
        </div>
        <div class="card chart-card">
            <h3 class="card-title">Progres Usulan</h3>
            <canvas id="donutChart"></canvas>
        </div>
    </div>
    <!-- <div class="card chart-card-full">
        <h3 class="card-title">Tren Nilai Aset yang Diusulkan (12 Bulan Terakhir)</h3>
        <canvas id="lineChart"></canvas>
    </div> -->

</div>

<!-- Tambahan CSS untuk halaman ini -->
<style>
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}
.stat-card {
    background-color: var(--surface-color);
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    box-shadow: var(--shadow-md);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid var(--border-color);
}
.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.stat-card i {
    margin-bottom: 15px;
    opacity: 0.8;
}
.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary-color);
}
.stat-label {
    font-size: 1rem;
    color: var(--text-secondary);
    font-weight: 500;
}
.chart-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}
.chart-card {
    height: 400px;
}
.chart-card-full {
    height: 450px;
}
@media (max-width: 768px) {
    .chart-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- JavaScript untuk Inisialisasi Chart (DIPERBARUI) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Fungsi untuk mendapatkan warna chart sesuai tema
    const getChartColors = () => {
        const isDarkMode = document.body.classList.contains('dark-mode');
        return {
            // Palet warna berbeda dan kontras
            backgroundColor: [
                isDarkMode ? 'rgba(121, 178, 244, 0.4)' : 'rgba(58, 90, 131, 0.7)', // Biru
                isDarkMode ? 'rgba(246, 224, 94, 0.4)' : 'rgba(214, 158, 46, 0.7)', // Emas
                isDarkMode ? 'rgba(79, 209, 197, 0.4)' : 'rgba(49, 151, 149, 0.7)', // Teal
                isDarkMode ? 'rgba(159, 122, 234, 0.4)' : 'rgba(128, 90, 213, 0.7)', // Ungu
                isDarkMode ? 'rgba(246, 173, 85, 0.4)' : 'rgba(221, 107, 32, 0.7)', // Oranye
            ],
            borderColor: [
                isDarkMode ? '#79b2f4' : '#3a5a83',
                isDarkMode ? '#f6e05e' : '#d69e2e',
                isDarkMode ? '#4fd1c5' : '#319795',
                isDarkMode ? '#9f7aea' : '#805ad5',
                isDarkMode ? '#f6ad55' : '#dd6b20',
            ],
            gridColor: isDarkMode ? 'rgba(74, 85, 104, 0.5)' : 'rgba(226, 232, 240, 1)',
            textColor: isDarkMode ? '#f7fafc' : '#4a5568'
        };
    };

    const chartOptions = (isDarkMode) => {
        const colors = getChartColors();
        Chart.defaults.color = colors.textColor;
        return {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { color: colors.textColor }, 
                    grid: { color: colors.gridColor } 
                },
                x: { 
                    ticks: { color: colors.textColor }, 
                    grid: { color: colors.gridColor } 
                }
            },
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { color: colors.textColor } 
                } 
            }
        };
    };

    let barChart, donutChart, lineChart;

    const createCharts = () => {
        const options = chartOptions();
        
        // Bar Chart
        const barCtx = document.getElementById('barChart')?.getContext('2d');
        if (barCtx) {
            if(barChart) barChart.destroy();
            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($bar_chart_data['labels']) ?>,
                    datasets: [{
                        label: 'Jumlah Barang',
                        data: <?= json_encode($bar_chart_data['values']) ?>,
                        backgroundColor: getChartColors().backgroundColor,
                        borderColor: getChartColors().borderColor,
                        borderWidth: 1
                    }]
                },
                options: { ...options, plugins: { legend: { display: false } } }
            });
        }

        // Donut Chart
        const donutCtx = document.getElementById('donutChart')?.getContext('2d');
        if (donutCtx) {
            if(donutChart) donutChart.destroy();
            donutChart = new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($donut_chart_data['labels']) ?>,
                    datasets: [{
                        data: <?= json_encode($donut_chart_data['values']) ?>,
                        backgroundColor: getChartColors().backgroundColor,
                        borderColor: getChartColors().borderColor,
                        borderWidth: 1
                    }]
                },
                options: { ...options }
            });
        }

        // Line Chart
        const lineCtx = document.getElementById('lineChart')?.getContext('2d');
        if (lineCtx) {
            if(lineChart) lineChart.destroy();
            lineChart = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($line_chart_data['labels']) ?>,
                    datasets: [{
                        label: 'Total Nilai Aset (Rp)',
                        data: <?= json_encode($line_chart_data['values']) ?>,
                        backgroundColor: getChartColors().backgroundColor[0],
                        borderColor: getChartColors().borderColor[0],
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { ...options, plugins: { legend: { display: false } } }
            });
        }
    };
    
    // Buat chart saat pertama kali load
    createCharts();
    
    // Buat ulang chart saat tema berubah
    const themeToggleButton = document.getElementById('theme-toggle');
    themeToggleButton?.addEventListener('click', () => {
        // Beri sedikit jeda agar transisi body selesai
        setTimeout(createCharts, 400);
    });
});
</script>

<?php
// Memuat footer halaman pengunjung
require_once __DIR__ . '/templates/footer.php';
?>
