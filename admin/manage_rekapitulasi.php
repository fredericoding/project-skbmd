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
    $where_clause .= " AND (nama_skpd LIKE ? OR perihal LIKE ? OR nomor_usulan LIKE ?)";
    $search_like = "%" . $search_term . "%";
    $params = [$search_like, $search_like, $search_like];
    $types = 'sss';
}

// Fetch data
$data_sql = "SELECT * FROM rekapitulasi_progres $where_clause ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($data_sql);
$final_params = array_merge($params, [$limit, $offset]);
$final_types = $types . 'ii';
$stmt->bind_param($final_types, ...$final_params);
$stmt->execute();
$result = $stmt->get_result();

// Count total records
$total_sql = "SELECT COUNT(id) as total FROM rekapitulasi_progres $where_clause";
$stmt_total = $db->prepare($total_sql);
if(!empty($params)){
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_records = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
?>

<div class="container-fluid">
    <h1 class="page-title-admin-content">Manajemen Data Rekapitulasi Progres</h1>
    <div class="card-admin">
        <div class="card-header-admin">
            <h3 class="card-title-admin">Daftar Progres Usulan</h3>
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari SKPD, perihal, no usulan..." value="<?= htmlspecialchars($search_term) ?>">
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
                            <th>ID</th>
                            <th>Nama SKPD</th>
                            <th>Perihal</th>
                            <th>Tgl Usulan</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): 
                                $status = '<span class="status-badge status-proses">Proses Awal</span>';
                                if(!empty($row['tanggal_pemusnahan_penjualan'])) $status = '<span class="status-badge status-selesai">Selesai</span>';
                                elseif(!empty($row['tanggal_persetujuan'])) $status = '<span class="status-badge status-disetujui">Disetujui</span>';
                            ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_skpd']) ?></strong></td>
                                <td><?= htmlspecialchars($row['perihal']) ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_usulan'])) ?></td>
                                <td><?= $status ?></td>
                                <td class="action-cell">
                                    <a href="edit_data.php?type=rekapitulasi&id=<?= $row['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_data.php?type=rekapitulasi&id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Anda yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Tidak ada data ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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