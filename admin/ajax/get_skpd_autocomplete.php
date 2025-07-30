<?php
require_once __DIR__ . '/../../config.php';

$suggestions = [];
if (isset($_POST['query'])) {
    $query = $_POST['query'] . '%';
    
    // Gabungkan pencarian dari 3 tabel untuk mendapatkan daftar SKPD yang komprehensif
    $sql = "
        (SELECT DISTINCT nama_skpd FROM pemindahtanganan WHERE nama_skpd LIKE ?)
        UNION
        (SELECT DISTINCT nama_skpd FROM penghapusan WHERE nama_skpd LIKE ?)
        UNION
        (SELECT DISTINCT nama_skpd FROM rekapitulasi_progres WHERE nama_skpd LIKE ?)
        LIMIT 10
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param('sss', $query, $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['nama_skpd'];
    }
}

header('Content-Type: application/json');
echo json_encode($suggestions);
?>
