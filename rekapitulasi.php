<?php
// Memuat header halaman pengunjung
require_once __DIR__ . '/templates/header.php';

// --- PENGATURAN & LOGIKA FILTER ---

// 1. Pengaturan Tab Aktif
$tab = $_GET['tab'] ?? 'skpd';
$allowed_tabs = ['skpd', 'gubernur', 'pemusnahan', 'sekda', 'nilai_jual'];
if (!in_array($tab, $allowed_tabs)) {
    $tab = 'skpd';
}

// 2. Pengaturan Pagination
$limit = (int) ($settings['pagination_limit'] ?? 15);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 3. Pengaturan Filter & Pencarian
$search_term = $_GET['search'] ?? '';
$months = $_GET['months'] ?? [];
$quarter = $_GET['quarter'] ?? '';

// 4. Membangun Query SQL Dinamis
$where_clauses = ["is_deleted = 0"];
$params = [];
$types = '';

// Filter Pencarian
if (!empty($search_term)) {
    $where_clauses[] = "nama_skpd LIKE ?";
    $params[] = "%" . $search_term . "%";
    $types .= 's';
}

// Filter Bulan (berdasarkan tanggal usulan)
if (!empty($months) && is_array($months)) {
    $month_placeholders = implode(',', array_fill(0, count($months), '?'));
    $where_clauses[] = "MONTH(tanggal_usulan) IN ($month_placeholders)";
    foreach ($months as $month) {
        $params[] = $month;
        $types .= 'i';
    }
}

// Filter Triwulan
if (!empty($quarter)) {
    switch ($quarter) {
        case '1': $where_clauses[] = "MONTH(tanggal_usulan) BETWEEN 1 AND 3"; break;
        case '2': $where_clauses[] = "MONTH(tanggal_usulan) BETWEEN 4 AND 6"; break;
        case '3': $where_clauses[] = "MONTH(tanggal_usulan) BETWEEN 7 AND 9"; break;
        case '4': $where_clauses[] = "MONTH(tanggal_usulan) BETWEEN 10 AND 12"; break;
    }
}

// Filter spesifik berdasarkan Tab yang aktif
$tab_columns = [];
switch ($tab) {
    case 'skpd':
        $tab_columns = ['id', 'nama_skpd', 'tanggal_usulan', 'nomor_usulan', 'perihal', 'nilai_aset'];
        break;
    case 'gubernur':
        $tab_columns = ['id', 'nama_skpd', 'perihal', 'tanggal_persetujuan', 'nomor_persetujuan', 'nilai_aset'];
        $where_clauses[] = "tanggal_persetujuan IS NOT NULL";
        break;
    case 'pemusnahan':
        $tab_columns = ['id', 'nama_skpd', 'perihal', 'tanggal_pemusnahan_penjualan', 'nomor_pemusnahan_penjualan', 'nilai_jual'];
        $where_clauses[] = "tanggal_pemusnahan_penjualan IS NOT NULL";
        break;
    case 'sekda':
        $tab_columns = ['id', 'nama_skpd', 'perihal', 'tanggal_sk_pengelola', 'nomor_sk_pengelola', 'nilai_aset'];
        $where_clauses[] = "tanggal_sk_pengelola IS NOT NULL";
        break;
    case 'nilai_jual':
        $tab_columns = ['id', 'nama_skpd', 'perihal', 'tanggal_pemusnahan_penjualan', 'nilai_jual'];
        $where_clauses[] = "tanggal_pemusnahan_penjualan IS NOT NULL AND nilai_jual > 0";
        break;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// --- PENGAMBILAN DATA ---
// 1. Data untuk Tabel (dengan pagination)
$main_data_sql = "SELECT * FROM rekapitulasi_progres $where_sql ORDER BY tanggal_usulan DESC LIMIT ? OFFSET ?";
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

// 2. Hitung Total Data untuk Pagination
$total_records_sql = "SELECT COUNT(id) as total FROM rekapitulasi_progres $where_sql";
$stmt_total = $db->prepare($total_records_sql);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_records = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
?>

<div class="container">
    <h1 class="page-title">Rekapitulasi Progres Usulan</h1>

    <!-- Filter Section -->
    <div class="card filter-card">
        <form action="rekapitulasi.php" method="GET" class="filter-form">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
            <div class="filter-group">
                <label for="search">Cari SKPD</label>
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" name="search" placeholder="Ketik nama SKPD..." value="<?= htmlspecialchars($search_term) ?>">
                </div>
            </div>
            <div class="filter-group">
                <label for="quarter">Filter Triwulan (Usulan)</label>
                <select name="quarter" id="quarter">
                    <option value="">Semua Triwulan</option>
                    <option value="1" <?= $quarter == '1' ? 'selected' : '' ?>>Triwulan 1</option>
                    <option value="2" <?= $quarter == '2' ? 'selected' : '' ?>>Triwulan 2</option>
                    <option value="3" <?= $quarter == '3' ? 'selected' : '' ?>>Triwulan 3</option>
                    <option value="4" <?= $quarter == '4' ? 'selected' : '' ?>>Triwulan 4</option>
                </select>
            </div>
            <div class="filter-group month-filter">
                <label>Filter Bulan (Usulan)</label>
                <div class="month-checkboxes">
                    <?php for ($i=1; $i<=12; $i++): $month_name = date("M", mktime(0,0,0,$i,10)); ?>
                    <label><input type="checkbox" name="months[]" value="<?= $i ?>" <?= in_array($i, $months) ? 'checked' : '' ?>> <?= $month_name ?></label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                <a href="rekapitulasi.php?tab=<?= $tab ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Tab Navigation & Export -->
    <div class="tabs-and-export-container">
        <div class="tabs-container">
            <a href="?tab=skpd" class="tab-link <?= $tab == 'skpd' ? 'active' : '' ?>">Usulan SKPD</a>
            <a href="?tab=gubernur" class="tab-link <?= $tab == 'gubernur' ? 'active' : '' ?>">Persetujuan Gubernur</a>
            <a href="?tab=pemusnahan" class="tab-link <?= $tab == 'pemusnahan' ? 'active' : '' ?>">Pemusnahan/Penjualan</a>
            <a href="?tab=sekda" class="tab-link <?= $tab == 'sekda' ? 'active' : '' ?>">SK SEKDA</a>
            <a href="?tab=nilai_jual" class="tab-link <?= $tab == 'nilai_jual' ? 'active' : '' ?>">Nilai Penjualan</a>
        </div>
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
                        <?php if (in_array('nama_skpd', $tab_columns)) echo '<th>Nama SKPD</th>'; ?>
                        <?php if (in_array('tanggal_usulan', $tab_columns)) echo '<th>Tgl Usulan</th>'; ?>
                        <?php if (in_array('nomor_usulan', $tab_columns)) echo '<th>No Usulan</th>'; ?>
                        <?php if (in_array('perihal', $tab_columns)) echo '<th>Perihal</th>'; ?>
                        <?php if (in_array('tanggal_persetujuan', $tab_columns)) echo '<th>Tgl Persetujuan</th>'; ?>
                        <?php if (in_array('nomor_persetujuan', $tab_columns)) echo '<th>No Persetujuan</th>'; ?>
                        <?php if (in_array('tanggal_pemusnahan_penjualan', $tab_columns)) echo '<th>Tgl Eksekusi</th>'; ?>
                        <?php if (in_array('nomor_pemusnahan_penjualan', $tab_columns)) echo '<th>No Eksekusi</th>'; ?>
                        <?php if (in_array('tanggal_sk_pengelola', $tab_columns)) echo '<th>Tgl SK SEKDA</th>'; ?>
                        <?php if (in_array('nomor_sk_pengelola', $tab_columns)) echo '<th>No SK SEKDA</th>'; ?>
                        <?php if (in_array('nilai_aset', $tab_columns)) echo '<th>Nilai Aset</th>'; ?>
                        <?php if (in_array('nilai_jual', $tab_columns)) echo '<th>Nilai Jual</th>'; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($main_data) > 0): ?>
                        <?php foreach ($main_data as $index => $row): ?>
                            <tr class="main-row">
                                <td><?= $offset + $index + 1 ?></td>
                                <?php if (in_array('nama_skpd', $tab_columns)) echo '<td>' . htmlspecialchars($row['nama_skpd']) . '</td>'; ?>
                                <?php if (in_array('tanggal_usulan', $tab_columns)) echo '<td>' . ($row['tanggal_usulan'] ? date('d-m-Y', strtotime($row['tanggal_usulan'])) : '-') . '</td>'; ?>
                                <?php if (in_array('nomor_usulan', $tab_columns)) echo '<td>' . htmlspecialchars($row['nomor_usulan'] ?: '-') . '</td>'; ?>
                                <?php if (in_array('perihal', $tab_columns)) echo '<td>' . htmlspecialchars($row['perihal'] ?: '-') . '</td>'; ?>
                                <?php if (in_array('tanggal_persetujuan', $tab_columns)) echo '<td>' . ($row['tanggal_persetujuan'] ? date('d-m-Y', strtotime($row['tanggal_persetujuan'])) : '-') . '</td>'; ?>
                                <?php if (in_array('nomor_persetujuan', $tab_columns)) echo '<td>' . htmlspecialchars($row['nomor_persetujuan'] ?: '-') . '</td>'; ?>
                                <?php if (in_array('tanggal_pemusnahan_penjualan', $tab_columns)) echo '<td>' . ($row['tanggal_pemusnahan_penjualan'] ? date('d-m-Y', strtotime($row['tanggal_pemusnahan_penjualan'])) : '-') . '</td>'; ?>
                                <?php if (in_array('nomor_pemusnahan_penjualan', $tab_columns)) echo '<td>' . htmlspecialchars($row['nomor_pemusnahan_penjualan'] ?: '-') . '</td>'; ?>
                                <?php if (in_array('tanggal_sk_pengelola', $tab_columns)) echo '<td>' . ($row['tanggal_sk_pengelola'] ? date('d-m-Y', strtotime($row['tanggal_sk_pengelola'])) : '-') . '</td>'; ?>
                                <?php if (in_array('nomor_sk_pengelola', $tab_columns)) echo '<td>' . htmlspecialchars($row['nomor_sk_pengelola'] ?: '-') . '</td>'; ?>
                                <?php if (in_array('nilai_aset', $tab_columns)) echo '<td>Rp ' . number_format($row['nilai_aset'], 0, ',', '.') . '</td>'; ?>
                                <?php if (in_array('nilai_jual', $tab_columns)) echo '<td>Rp ' . number_format($row['nilai_jual'], 0, ',', '.') . '</td>'; ?>
                                <td>
                                    <button class="btn-details" data-id="<?= $row['id'] ?>">
                                        <i class="fas fa-chevron-down"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <tr class="detail-row" style="display: none;">
                                <td colspan="<?= count($tab_columns) + 2 ?>" class="detail-container-cell">
                                    <div class="detail-container"><div class="loader">Memuat data...</div></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($tab_columns) + 2 ?>" class="text-center">Tidak ada data yang ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php 
            $query_params = http_build_query(array_merge($_GET, ['page' => '']));
            for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tambahan CSS untuk halaman ini -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/page_data_tables.css?v=<?= time() ?>">
<style>
.filter-card { margin-bottom: 20px; } .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; align-items: end; } .filter-group { display: flex; flex-direction: column; } .filter-group label { font-weight: 600; margin-bottom: 8px; color: var(--text-secondary); } .filter-group input, .filter-group select { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-color); color: var(--text-primary); } .search-wrapper { position: relative; } .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); } .search-wrapper input { padding-left: 40px; } .month-checkboxes { display: flex; flex-wrap: wrap; gap: 10px; } .month-checkboxes label { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; padding: 5px 10px; border-radius: 5px; background-color: var(--bg-color); cursor: pointer; } .filter-actions { display: flex; gap: 10px; } .btn-secondary { background: var(--surface-color); color: var(--text-primary); border: 1px solid var(--border-color); }

.tabs-and-export-container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
.tabs-container { display: flex; background-color: var(--surface-color); border-radius: 8px; padding: 5px; box-shadow: var(--shadow-sm); overflow-x: auto; flex-grow: 1; }
.tab-link { flex-grow: 1; text-align: center; padding: 12px 10px; color: var(--text-secondary); font-weight: 600; border-radius: 6px; transition: all 0.3s ease; white-space: nowrap; }
.tab-link:hover { background-color: var(--bg-color); color: var(--text-primary); }
.tab-link.active { background-color: var(--primary-color); color: white; box-shadow: var(--shadow-md); }
.export-actions { display: flex; gap: 10px; }
.btn-export-excel { background-color: #1D6F42; color: white; }
.btn-export-pdf { background-color: #B30B00; color: white; }

.btn-details { background: none; border: 1px solid var(--primary-color); color: var(--primary-color); padding: 5px 10px; border-radius: 5px; cursor: pointer; transition: all 0.3s ease; } .btn-details:hover { background: var(--primary-color); color: white; } .btn-details i { transition: transform 0.3s ease; } .btn-details.active i { transform: rotate(180deg); } .detail-row { background-color: var(--bg-color) !important; } .detail-container-cell { padding: 0 !important; } .detail-container { padding: 20px; } .loader { text-align: center; padding: 20px; color: var(--text-secondary); } .pagination { display: flex; justify-content: center; margin-top: 20px; gap: 5px; } .pagination a { color: var(--primary-color); padding: 8px 15px; border: 1px solid var(--border-color); border-radius: 5px; } .pagination a:hover { background-color: var(--bg-color); } .pagination a.active { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }
</style>

<!-- JavaScript untuk halaman ini -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('.btn-details').on('click', function() {
        const button = $(this);
        const usulanId = button.data('id');
        const detailRow = button.closest('tr.main-row').next('tr.detail-row');
        const detailContainer = detailRow.find('.detail-container');

        button.toggleClass('active');
        detailRow.slideToggle();

        if (!button.hasClass('loaded')) {
            $.ajax({
                url: '<?= BASE_URL ?>/admin/ajax/get_rekapitulasi_detail.php',
                type: 'POST',
                data: { id: usulanId },
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
