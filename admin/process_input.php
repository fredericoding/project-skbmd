<?php
require_once __DIR__ . '/../config.php';
// Pastikan Anda sudah menjalankan 'composer require phpoffice/phpspreadsheet'
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// --- SECURITY CHECK ---
if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak.");
}

// --- MAIN LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataType = $_POST['data_type'] ?? '';
    $inputMethod = $_POST['input_method'] ?? '';

    if ($inputMethod == 'manual') {
        process_manual_input($db, $dataType, $_POST);
    } elseif ($inputMethod == 'otomatis' && isset($_FILES['excel_file'])) {
        process_excel_input($db, $dataType, $_FILES['excel_file']);
    } else {
        redirect_with_error("Metode input tidak valid.");
    }
}

// --- FUNCTIONS ---

function process_manual_input($db, $dataType, $postData) {
    $tableName = get_table_name($dataType);
    if (!$tableName) redirect_with_error("Jenis data tidak valid.");

    $skpdName = trim($postData['nama_skpd']);
    if (empty($skpdName)) {
        redirect_with_error("Nama SKPD wajib diisi.");
    }

    unset($postData['data_type'], $postData['input_method'], $postData['nama_skpd']);

    $data_arrays = [];
    foreach ($postData as $key => $value) {
        $data_arrays[$key] = preg_split('/\r\n|\r|\n/', $value);
    }

    $line_counts = array_map(function($arr) { return count(array_filter($arr, 'strlen')); }, $data_arrays);
    $first_count = 0;
    foreach($line_counts as $c) {
        if ($c > 0) {
            $first_count = $c;
            break;
        }
    }

    foreach ($line_counts as $count) {
        if ($count > 0 && $count !== $first_count) {
             redirect_with_error("Jumlah baris pada setiap kolom input tidak sama. Pastikan semua kolom memiliki jumlah entri yang sama.");
        }
    }
    
    $total_rows = $first_count;
    if ($total_rows == 0) {
        redirect_with_error("Tidak ada data barang yang diinput.");
    }

    $db->begin_transaction();
    try {
        for ($i = 0; $i < $total_rows; $i++) {
            $row_data = ['nama_skpd' => $skpdName];
            foreach ($data_arrays as $key => $values) {
                $row_data[$key] = trim($values[$i] ?? '');
            }
            
            if (isset($row_data['nilai_perolehan'])) {
                $row_data['nilai_perolehan'] = filter_var(str_replace(['.', ','], ['', '.'], $row_data['nilai_perolehan']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }
             if (isset($row_data['nilai_jual'])) {
                $row_data['nilai_jual'] = filter_var(str_replace(['.', ','], ['', '.'], $row_data['nilai_jual']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }

            $columns = implode(', ', array_keys($row_data));
            $placeholders = implode(', ', array_fill(0, count($row_data), '?'));
            $types = str_repeat('s', count($row_data));

            $stmt = $db->prepare("INSERT INTO $tableName ($columns) VALUES ($placeholders)");
            $stmt->bind_param($types, ...array_values($row_data));
            $stmt->execute();
        }
        $db->commit();
        redirect_with_success("$total_rows data berhasil disimpan.");
    } catch (Exception $e) {
        $db->rollback();
        redirect_with_error("Terjadi kesalahan: " . $e->getMessage());
    }
}

/**
 * Fungsi utama untuk memproses unggahan file Excel.
 * Fungsi ini bertindak sebagai router yang memanggil fungsi spesifik berdasarkan tipe data.
 */
function process_excel_input($db, $dataType, $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        redirect_with_error("Terjadi kesalahan saat mengunggah file.");
    }
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($file_ext != 'xlsx') {
        redirect_with_error("Hanya file .xlsx yang diizinkan.");
    }

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        if (count($data) < 2) {
            redirect_with_error("File Excel kosong atau hanya berisi header.");
        }

        // --- PERBAIKAN LOGIKA HEADER ---
        $header_row_raw = array_values($data[1]);
        // Hapus kolom kosong di akhir header
        while (count($header_row_raw) > 0 && empty(trim(end($header_row_raw)))) {
            array_pop($header_row_raw);
        }
        $header_row = array_map('strtolower', array_map('trim', $header_row_raw));
        $expected_headers = array_map('strtolower', get_expected_headers($dataType));

        if ($header_row !== $expected_headers) {
            $error_detail = "Header file Excel tidak sesuai. <br><b>Header Diharapkan:</b> " . implode(', ', get_expected_headers($dataType)) . "<br><b>Header Ditemukan:</b> " . implode(', ', array_values($data[1]));
            $_SESSION['validation_errors'] = [$error_detail];
            header("Location: " . BASE_URL . "/admin/input_data.php?type=$dataType&method=otomatis");
            exit();
        }
        // --- AKHIR PERBAIKAN LOGIKA HEADER ---

        // Panggil fungsi prosesor spesifik
        switch ($dataType) {
            case 'pemindahtanganan':
                process_pemindahtanganan_excel($db, $data);
                break;
            case 'penghapusan':
                process_penghapusan_excel($db, $data);
                break;
            case 'rekapitulasi':
                process_rekapitulasi_excel($db, $data);
                break;
            default:
                redirect_with_error("Tipe data tidak valid untuk proses Excel.");
        }

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        redirect_with_error("Gagal membaca file Excel: " . $e->getMessage());
    } catch (Exception $e) {
        redirect_with_error("Terjadi kesalahan umum: " . $e->getMessage());
    }
}

/**
 * Fungsi spesifik untuk memproses data Pemindahtanganan dari Excel.
 */
function process_pemindahtanganan_excel($db, $data) {
    $tableName = 'pemindahtanganan';
    $expected_headers = get_expected_headers('pemindahtanganan');
    $validation_errors = [];
    $data_to_insert = [];

    for ($i = 2; $i <= count($data); $i++) {
        $row_array_raw = array_values($data[$i]);
        if (count(array_filter($row_array_raw)) == 0) continue;
        
        $row_array = array_slice($row_array_raw, 0, count($expected_headers));
        $row_data = array_combine($expected_headers, $row_array);
        
        $insert_data = map_pemindahtanganan_row($row_data, $i, $validation_errors);
        if ($insert_data) $data_to_insert[] = $insert_data;
    }

    if (!empty($validation_errors)) {
        $_SESSION['validation_errors'] = $validation_errors;
        header("Location: " . BASE_URL . "/admin/input_data.php?type=pemindahtanganan&method=otomatis");
        exit();
    }

    $db->begin_transaction();
    try {
        foreach ($data_to_insert as $insert_data) {
            $columns = implode(', ', array_keys($insert_data));
            $placeholders = implode(', ', array_fill(0, count($insert_data), '?'));
            $types = str_repeat('s', count($insert_data));
            $stmt = $db->prepare("INSERT INTO $tableName ($columns) VALUES ($placeholders)");
            $stmt->bind_param($types, ...array_values($insert_data));
            $stmt->execute();
        }
        $db->commit();
        redirect_with_success(count($data_to_insert) . " data pemindahtanganan berhasil diimpor.");
    } catch (Exception $e) {
        $db->rollback();
        redirect_with_error("Gagal menyimpan data pemindahtanganan: " . $e->getMessage());
    }
}

/**
 * Fungsi spesifik untuk memproses data Penghapusan dari Excel.
 */
function process_penghapusan_excel($db, $data) {
    $tableName = 'penghapusan';
    $expected_headers = get_expected_headers('penghapusan');
    $validation_errors = [];
    $data_to_insert = [];

    for ($i = 2; $i <= count($data); $i++) {
        $row_array_raw = array_values($data[$i]);
        if (count(array_filter($row_array_raw)) == 0) continue;

        $row_array = array_slice($row_array_raw, 0, count($expected_headers));
        $row_data = array_combine($expected_headers, $row_array);

        $insert_data = map_penghapusan_row($row_data, $i, $validation_errors);
        if ($insert_data) $data_to_insert[] = $insert_data;
    }

    if (!empty($validation_errors)) {
        $_SESSION['validation_errors'] = $validation_errors;
        header("Location: " . BASE_URL . "/admin/input_data.php?type=penghapusan&method=otomatis");
        exit();
    }

    $db->begin_transaction();
    try {
        foreach ($data_to_insert as $insert_data) {
            $columns = implode(', ', array_keys($insert_data));
            $placeholders = implode(', ', array_fill(0, count($insert_data), '?'));
            $types = str_repeat('s', count($insert_data));
            $stmt = $db->prepare("INSERT INTO $tableName ($columns) VALUES ($placeholders)");
            $stmt->bind_param($types, ...array_values($insert_data));
            $stmt->execute();
        }
        $db->commit();
        redirect_with_success(count($data_to_insert) . " data penghapusan berhasil diimpor.");
    } catch (Exception $e) {
        $db->rollback();
        redirect_with_error("Gagal menyimpan data penghapusan: " . $e->getMessage());
    }
}

/**
 * Fungsi spesifik untuk memproses data Rekapitulasi dari Excel.
 */
function process_rekapitulasi_excel($db, $data) {
    $tableName = 'rekapitulasi_progres';
    $expected_headers = get_expected_headers('rekapitulasi');
    $validation_errors = [];
    $data_to_insert = [];

    for ($i = 2; $i <= count($data); $i++) {
        $row_array_raw = array_values($data[$i]);
        if (count(array_filter($row_array_raw)) == 0) continue;

        $row_array = array_slice($row_array_raw, 0, count($expected_headers));
        $row_data = array_combine($expected_headers, $row_array);

        $insert_data = map_rekapitulasi_row($row_data, $i, $validation_errors);
        if ($insert_data) $data_to_insert[] = $insert_data;
    }

    if (!empty($validation_errors)) {
        $_SESSION['validation_errors'] = $validation_errors;
        header("Location: " . BASE_URL . "/admin/input_data.php?type=rekapitulasi&method=otomatis");
        exit();
    }

    $db->begin_transaction();
    try {
        foreach ($data_to_insert as $insert_data) {
            $columns = implode(', ', array_keys($insert_data));
            $placeholders = implode(', ', array_fill(0, count($insert_data), '?'));
            $types = str_repeat('s', count($insert_data));
            $stmt = $db->prepare("INSERT INTO $tableName ($columns) VALUES ($placeholders)");
            $stmt->bind_param($types, ...array_values($insert_data));
            $stmt->execute();
        }
        $db->commit();
        redirect_with_success(count($data_to_insert) . " data rekapitulasi berhasil diimpor.");
    } catch (Exception $e) {
        $db->rollback();
        redirect_with_error("Gagal menyimpan data rekapitulasi: " . $e->getMessage());
    }
}


// --- FUNGSI PEMBANTU ---

function get_table_name($dataType) {
    $map = [
        'pemindahtanganan' => 'pemindahtanganan',
        'penghapusan' => 'penghapusan',
        'rekapitulasi' => 'rekapitulasi_progres'
    ];
    return $map[$dataType] ?? null;
}

function get_expected_headers($dataType) {
    if ($dataType == 'pemindahtanganan') {
        return ['No', 'SKPD', 'Kode Barang', 'Nama Barang', 'Spesifikasi Nama Barang', 'NIBAR', 'Jumlah Barang', 'Satuan', 'Lokasi', 'Nilai Perolehan', 'Bentuk Pemindahtanganan', 'Alasan Rencana Pemindahtanganan', 'Keterangan'];
    } elseif ($dataType == 'penghapusan') {
        return ['No', 'SKPD', 'Kode Barang', 'Nama Barang', 'Spesifikasi Nama Barang', 'NIBAR', 'Nilai Perolehan', 'Alasan Rencana Penghapusan', 'Keterangan', 'Jumlah Barang'];
    } else { // rekapitulasi
        return ['No', 'Nama SKPD', 'Tanggal Usulan', 'Nomor Usulan', 'Perihal', 'Tanggal Pembahasan', 'Nomor Pembahasan', 'Tanggal Persetujuan', 'Nomor Persetujuan', 'Tanggal Pemusnahan/Penjualan', 'Nomor Pemusnahan/Penjualan', 'Tanggal STS', 'Nomor STS', 'Nilai Jual', 'Tanggal SK Pengelola Barang', 'Nomor SK Pengelola Barang', 'KIB', 'Nilai Aset', 'Eksekusi SIMAS'];
    }
}

// --- FUNGSI MAPPING SPESIFIK ---

function map_pemindahtanganan_row($row_data, $row_num, &$errors) {
    $db_data = [];
    $db_data['nama_skpd'] = $row_data['SKPD'];
    $db_data['kode_barang'] = $row_data['Kode Barang'];
    $db_data['nama_barang'] = $row_data['Nama Barang'];
    $db_data['spesifikasi'] = $row_data['Spesifikasi Nama Barang'];
    $db_data['nibar'] = $row_data['NIBAR'];
    $db_data['jumlah_barang'] = $row_data['Jumlah Barang'] . ' ' . $row_data['Satuan'];
    $db_data['lokasi'] = $row_data['Lokasi'];
    $db_data['nilai_perolehan'] = filter_var($row_data['Nilai Perolehan'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $db_data['bentuk_pemindahtanganan'] = $row_data['Bentuk Pemindahtanganan'];
    $db_data['alasan'] = $row_data['Alasan Rencana Pemindahtanganan'];
    $db_data['keterangan'] = $row_data['Keterangan'];
    return $db_data;
}

function map_penghapusan_row($row_data, $row_num, &$errors) {
    $db_data = [];
    $db_data['nama_skpd'] = $row_data['SKPD'];
    $db_data['kode_barang'] = $row_data['Kode Barang'];
    $db_data['nama_barang'] = $row_data['Nama Barang'];
    $db_data['spesifikasi'] = $row_data['Spesifikasi Nama Barang'];
    $db_data['nibar'] = $row_data['NIBAR'];
    $db_data['nilai_perolehan'] = filter_var($row_data['Nilai Perolehan'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $db_data['alasan'] = $row_data['Alasan Rencana Penghapusan'];
    $db_data['keterangan'] = $row_data['Keterangan'];
    $db_data['jumlah_barang'] = $row_data['Jumlah Barang'];
    return $db_data;
}

function map_rekapitulasi_row($row_data, $row_num, &$errors) {
    $db_data = [];
    $date_columns = ['Tanggal Usulan', 'Tanggal Pembahasan', 'Tanggal Persetujuan', 'Tanggal Pemusnahan/Penjualan', 'Tanggal STS', 'Tanggal SK Pengelola Barang'];
    $numeric_columns = ['Nilai Jual', 'Nilai Aset'];

    foreach($row_data as $key => $value) {
        $db_key = strtolower(str_replace(' ', '_', $key));
        if ($db_key === 'no') continue;

        $trimmed_value = trim($value);
        if ($trimmed_value === '' || $trimmed_value === null) {
            $db_data[$db_key] = null;
            continue;
        }

        if(in_array($key, $date_columns)) {
            if(is_numeric($trimmed_value)) {
                try {
                    $db_data[$db_key] = Date::excelToDateTimeObject($trimmed_value)->format('Y-m-d');
                } catch (Exception $e) {
                    $errors[] = "Baris $row_num, Kolom '$key': Format tanggal Excel tidak valid."; return false;
                }
            } else {
                $date = date_create_from_format('d/m/Y', $trimmed_value) ?: date_create($trimmed_value);
                if ($date) {
                    $db_data[$db_key] = date_format($date, 'Y-m-d');
                } else {
                     $errors[] = "Baris $row_num, Kolom '$key': Format tanggal '$trimmed_value' tidak bisa dibaca. Gunakan format YYYY-MM-DD atau DD/MM/YYYY."; return false;
                }
            }
        } elseif(in_array($key, $numeric_columns)) {
            $cleaned_value = str_replace(['.', ','], ['', '.'], $trimmed_value);
            if (is_numeric($cleaned_value)) {
                $db_data[$db_key] = $cleaned_value;
            } else {
                $errors[] = "Baris $row_num, Kolom '$key': Nilai '$trimmed_value' harus berupa angka."; return false;
            }
        } else {
            $db_data[$db_key] = $trimmed_value;
        }
    }
    return $db_data;
}

function redirect_with_error($message) {
    $_SESSION['error_message'] = $message;
    $dataType = $_POST['data_type'] ?? 'pemindahtanganan';
    $method = $_POST['input_method'] ?? 'manual';
    header("Location: " . BASE_URL . "/admin/input_$dataType.php?method=$method");
    exit();
}

function redirect_with_success($message) {
    $_SESSION['success_message'] = $message;
    $dataType = $_POST['data_type'] ?? 'pemindahtanganan';
    $method = $_POST['input_method'] ?? 'manual';
    header("Location: " . BASE_URL . "/admin/input_$dataType.php?method=$method");
    exit();
}
?>
