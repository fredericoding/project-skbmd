<?php
require_once __DIR__ . '/templates/header.php';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search_term = $_GET['search'] ?? '';
$where_clause = "WHERE is_deleted = 0";
$params = [];
$types = '';
if (!empty($search_term)) {
    $where_clause .= " AND nama_skpd LIKE ?";
    $search_like = "%" . $search_term . "%";
    $params = [$search_like];
    $types = 's';
}

// Fetch data (daftar SKPD unik)
$data_sql = "SELECT nama_skpd, COUNT(id) as jumlah_barang, SUM(nilai_perolehan) as total_nilai 
             FROM pemindahtanganan 
             $where_clause 
             GROUP BY nama_skpd 
             ORDER BY nama_skpd ASC 
             LIMIT ? OFFSET ?";
$stmt = $db->prepare($data_sql);
$final_params = array_merge($params, [$limit, $offset]);
$final_types = $types . 'ii';
$stmt->bind_param($final_types, ...$final_params);
$stmt->execute();
$result = $stmt->get_result();

// Count total unique SKPDs for pagination
$total_sql = "SELECT COUNT(DISTINCT nama_skpd) as total FROM pemindahtanganan $where_clause";
$stmt_total = $db->prepare($total_sql);
if(!empty($params)){
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_records = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
?>

<div class="container-fluid">
    <h1 class="page-title-admin-content">Manajemen Data Pemindahtanganan</h1>
    <p class="page-subtitle-admin">Pilih SKPD untuk melihat detail atau hapus semua data terkait.</p>

    <div class="card-admin">
        <div class="card-header-admin">
            <h3 class="card-title-admin">Daftar SKPD</h3>
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari SKPD..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="card-body-admin">
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>"><?= $_SESSION['flash_message']['message'] ?></div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama SKPD</th>
                            <th>Jumlah Barang</th>
                            <th>Total Nilai</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php $no = $offset + 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_skpd']) ?></strong></td>
                                <td><?= number_format($row['jumlah_barang']) ?></td>
                                <td>Rp <?= number_format($row['total_nilai'], 0, ',', '.') ?></td>
                                <td class="action-cell">
                                    <a href="detail_skpd.php?type=pemindahtanganan&skpd=<?= urlencode($row['nama_skpd']) ?>" class="btn-action btn-view">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                    <a href="delete_data.php?type=pemindahtanganan&skpd=<?= urlencode($row['nama_skpd']) ?>" class="btn-action btn-delete" onclick="return confirm('ANDA YAKIN ingin menghapus SEMUA data dari SKPD ini? Aksi ini tidak dapat dibatalkan.')">
                                        <i class="fas fa-trash"></i> Hapus Semua
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">Tidak ada data ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav class="pagination-nav">
                <ul>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li><a href="?page=<?= $i ?>&search=<?= urlencode($search_term) ?>" class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<link rel="stylesheet" href="assets/css/admin_style.css">
<?php require_once __DIR__ . '/templates/footer.php'; ?>
