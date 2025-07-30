<?php
// Memuat header halaman pengunjung
require_once __DIR__ . '/templates/header.php';

// --- PENGATURAN & LOGIKA FILTER ---

// 1. Pengaturan Pagination
$limit = (int) ($settings['pagination_limit'] ?? 15);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 2. Pengaturan Filter & Pencarian (Disederhanakan)
$search_term = $_GET['search'] ?? '';

// 3. Membangun Query SQL Dinamis
$where_clauses = ["is_deleted = 0"];
$params = [];
$types = '';

// Filter Pencarian
if (!empty($search_term)) {
    $where_clauses[] = "nama_skpd LIKE ?";
    $params[] = "%" . $search_term . "%";
    $types .= 's';
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// --- PENGAMBILAN DATA ---

// 1. Data untuk Kartu Ringkasan
$summary_query_sql = "SELECT 
                        COUNT(DISTINCT nama_skpd) as total_skpd, 
                        COUNT(id) as total_barang, 
                        SUM(nilai_perolehan) as total_nilai 
                      FROM penghapusan $where_sql";
$stmt_summary = $db->prepare($summary_query_sql);
if (!empty($params)) {
    $stmt_summary->bind_param($types, ...$params);
}
$stmt_summary->execute();
$summary = $stmt_summary->get_result()->fetch_assoc();


// 2. Data untuk Tabel Utama
$main_data_sql = "SELECT 
                    nama_skpd, 
                    COUNT(id) as jumlah_barang, 
                    SUM(nilai_perolehan) as nilai_perolehan 
                  FROM penghapusan 
                  $where_sql 
                  GROUP BY nama_skpd 
                  ORDER BY nama_skpd ASC 
                  LIMIT ? OFFSET ?";

$stmt_main = $db->prepare($main_data_sql);
$main_params = $params;
$main_params[] = $limit;
$main_params[] = $offset;
$main_types = $types . 'ii';
if (!empty($main_params)) {
    $stmt_main->bind_param($main_types, ...$main_params);
}
$stmt_main->execute();
$main_data = $stmt_main->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Hitung Total Data untuk Pagination
$total_records_sql = "SELECT COUNT(DISTINCT nama_skpd) as total FROM penghapusan $where_sql";
$stmt_total = $db->prepare($total_records_sql);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_records = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

?>

<div class="container">
    <h1 class="page-title">Data Penghapusan Barang</h1>

    <!-- Summary Cards Horizontal (DIPERBARUI) -->
    <div class="summary-cards-horizontal">
        <div class="stat-card-horizontal">
            <div class="stat-icon-horizontal bg-primary-light">
                <i class="fas fa-building text-primary"></i>
            </div>
            <div class="stat-info-horizontal">
                <div class="stat-label">Total SKPD</div>
                <div class="stat-value"><?= number_format($summary['total_skpd'] ?? 0) ?></div>
            </div>
        </div>
        <div class="stat-card-horizontal">
            <div class="stat-icon-horizontal bg-accent-light">
                <i class="fas fa-box-open text-accent"></i>
            </div>
            <div class="stat-info-horizontal">
                <div class="stat-label">Total Barang</div>
                <div class="stat-value"><?= number_format($summary['total_barang'] ?? 0) ?></div>
            </div>
        </div>
        <div class="stat-card-horizontal">
            <div class="stat-icon-horizontal bg-success-light">
                <i class="fas fa-dollar-sign text-success"></i>
            </div>
            <div class="stat-info-horizontal">
                <div class="stat-label">Total Nilai Perolehan</div>
                <div class="stat-value">Rp <?= number_format($summary['total_nilai'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- Filter and Export Section -->
    <div class="card filter-export-card">
        <form action="penghapusan.php" method="GET" class="filter-form-simple">
            <div class="filter-group">
                <label for="search">Cari SKPD</label>
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" name="search" placeholder="Ketik nama SKPD..." value="<?= htmlspecialchars($search_term) ?>">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Cari</button>
                <a href="penghapusan.php" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
            </div>
        </form>
        <div class="export-actions">
            <button class="btn btn-export-excel"><i class="fas fa-file-excel"></i> Excel</button>
            <button class="btn btn-export-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
        </div>
    </div>
    
    <!-- Main Table -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama SKPD</th>
                        <th>Jumlah Barang</th>
                        <th>Total Nilai Perolehan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($main_data) > 0): ?>
                        <?php foreach ($main_data as $index => $row): ?>
                            <tr class="main-row">
                                <td><?= $offset + $index + 1 ?></td>
                                <td><?= htmlspecialchars($row['nama_skpd']) ?></td>
                                <td><?= number_format($row['jumlah_barang']) ?></td>
                                <td>Rp <?= number_format($row['nilai_perolehan'], 2, ',', '.') ?></td>
                                <td>
                                    <button class="btn-details" data-skpd="<?= htmlspecialchars($row['nama_skpd']) ?>">
                                        <i class="fas fa-chevron-down"></i> Lihat Detail
                                    </button>
                                </td>
                            </tr>
                            <tr class="detail-row" style="display: none;">
                                <td colspan="5" class="detail-container-cell">
                                    <div class="detail-container">
                                        <div class="loader">Memuat data...</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search_term) ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Menggunakan style yang sama -->
<style>
/* Summary Cards Horizontal */
.summary-cards-horizontal {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}
.stat-card-horizontal {
    display: flex;
    align-items: center;
    background-color: var(--surface-color);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.stat-card-horizontal:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}
.stat-icon-horizontal {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-right: 20px;
    flex-shrink: 0;
}
.stat-icon-horizontal.bg-primary-light { background-color: rgba(58, 90, 131, 0.1); }
.stat-icon-horizontal .text-primary { color: var(--primary-color); }
.stat-icon-horizontal.bg-accent-light { background-color: rgba(214, 158, 46, 0.1); }
.stat-icon-horizontal .text-accent { color: var(--accent-color); }
.stat-icon-horizontal.bg-success-light { background-color: rgba(39, 174, 96, 0.1); }
.stat-icon-horizontal .text-success { color: #27ae60; }

.stat-info-horizontal .stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-bottom: 5px;
}
.stat-info-horizontal .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

/* Filter and Export */
.filter-export-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.filter-form-simple { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; flex-grow: 1; }
.filter-form-simple .filter-group { flex-grow: 1; min-width: 250px; }
.filter-group label { font-weight: 600; margin-bottom: 8px; color: var(--text-secondary); }
.filter-group input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-color); color: var(--text-primary); }
.search-wrapper { position: relative; }
.search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }
.search-wrapper input { padding-left: 40px; }
.filter-actions, .export-actions { display: flex; gap: 10px; }
.btn-secondary { background: var(--surface-color); color: var(--text-primary); border: 1px solid var(--border-color); }
.btn-export-excel { background-color: #1D6F42; color: white; }
.btn-export-pdf { background-color: #B30B00; color: white; }

/* Table Details */
.btn-details { background: none; border: 1px solid var(--primary-color); color: var(--primary-color); padding: 5px 10px; border-radius: 5px; cursor: pointer; transition: all 0.3s ease; }
.btn-details:hover { background: var(--primary-color); color: white; }
.btn-details i { transition: transform 0.3s ease; }
.btn-details.active i { transform: rotate(180deg); }
.detail-row { background-color: var(--bg-color) !important; }
.detail-container-cell { padding: 0 !important; }
.detail-container { padding: 20px; }
.detail-container .table-container { max-height: 300px; }
.loader { text-align: center; padding: 20px; color: var(--text-secondary); }

/* Pagination */
.pagination { display: flex; justify-content: center; margin-top: 20px; gap: 5px; }
.pagination a { color: var(--primary-color); padding: 8px 15px; border: 1px solid var(--border-color); border-radius: 5px; }
.pagination a:hover { background-color: var(--bg-color); }
.pagination a.active { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }
</style>

<!-- JavaScript untuk halaman ini -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('.btn-details').on('click', function() {
        const button = $(this);
        const skpd = button.data('skpd');
        const detailRow = button.closest('tr.main-row').next('tr.detail-row');
        const detailContainer = detailRow.find('.detail-container');

        button.toggleClass('active');
        detailRow.slideToggle();

        if (!button.hasClass('loaded')) {
            $.ajax({
                url: '<?= BASE_URL ?>/admin/ajax/get_penghapusan_detail.php',
                type: 'POST',
                data: { skpd_name: skpd },
                success: function(response) {
                    detailContainer.html(response);
                    button.addClass('loaded');
                },
                error: function() {
                    detailContainer.html('<div class="loader" style="color: red;">Gagal memuat data.</div>');
                }
            });
        }
    });
});
</script>

<?php
// Memuat footer halaman pengunjung
require_once __DIR__ . '/templates/footer.php';
?>