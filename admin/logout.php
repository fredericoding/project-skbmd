<?php
require_once __DIR__ . '/../config.php';

// Hancurkan semua data session
$_SESSION = array();
session_destroy();

// Arahkan ke halaman login
header("Location: " . BASE_URL . "/admin/login.php");
exit();
?>
