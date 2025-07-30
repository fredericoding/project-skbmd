<?php
require_once __DIR__ . '/../config.php';

// Keamanan
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'utama') {
    die("Akses ditolak.");
}

// Hapus gambar slider
if (isset($_GET['action']) && $_GET['action'] == 'delete_slider' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Hapus file fisik & data DB
    $img_path_q = $db->query("SELECT image_path FROM slider_images WHERE id = $id");
    if($img_path_q->num_rows > 0){
        $img_path = $img_path_q->fetch_assoc()['image_path'];
        if(file_exists(__DIR__ . '/../' . $img_path)) unlink(__DIR__ . '/../' . $img_path);
    }
    $db->query("DELETE FROM slider_images WHERE id = $id");
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Gambar slider dihapus.'];
    header("Location: control_panel.php");
    exit();
}

// Proses form utama
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update settings teks
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->bind_param('ss', $value, $key);
            $stmt->execute();
        }
    }

    // Proses upload logo
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        $target_dir = __DIR__ . "/../assets/images/";
        $filename = "logo_" . time() . "." . pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_dir . $filename)) {
            $db->query("UPDATE settings SET setting_value = 'assets/images/$filename' WHERE setting_key = 'site_logo'");
        }
    }

    // Proses upload gambar slider
    if (isset($_FILES['slider_images'])) {
        $target_dir = __DIR__ . "/../assets/images/slider/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        
        foreach ($_FILES['slider_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['slider_images']['error'][$key] == 0) {
                $filename = "slider_" . time() . "_" . $key . "." . pathinfo($_FILES['slider_images']['name'][$key], PATHINFO_EXTENSION);
                if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                    $db->query("INSERT INTO slider_images (image_path) VALUES ('assets/images/slider/$filename')");
                }
            }
        }
    }

    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Pengaturan berhasil disimpan.'];
    header("Location: control_panel.php");
    exit();
}
