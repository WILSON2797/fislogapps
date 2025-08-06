<?php
// Set header untuk mengembalikan data dalam format JSON
header('Content-Type: application/json');
// Mulai sesi untuk mengakses wh_id pengguna yang login
session_start();
// Include file koneksi database
include '../php/database.php'; // Pastikan file koneksi.php ada dan berisi koneksi ke database

// Pastikan request adalah GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Ambil parameter dari URL
    $table = $_GET['table'] ?? ''; // Nama tabel
    $column = $_GET['column'] ?? ''; // Kolom yang akan digunakan sebagai value
    $display = $_GET['display'] ?? ''; // Kolom yang akan ditampilkan di dropdown
    
    // Validasi parameter
    if (empty($table) || empty($column) || empty($display)) {
        echo json_encode(['status' => 'error', 'message' => 'Parameter table, column, dan display wajib diisi']);
        exit;
    }
    
    // Daftar tabel yang diizinkan untuk mencegah SQL injection
    $allowed_tables = ['region', 'role'];
    if (!in_array($table, $allowed_tables)) {
        echo json_encode(['status' => 'error', 'message' => 'Tabel tidak diizinkan']);
        exit;
    }
    
    try {
        // Siapkan query SQL untuk mengambil data
        $query = "SELECT `$column` AS id, `$display` AS text FROM `$table`";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Gagal menyiapkan query: " . $conn->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Ambil semua data sebagai array asosiatif
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Kembalikan data dalam format JSON yang sesuai dengan Select2
        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (Exception $e) {
        // Jika ada error, kembalikan pesan error
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data: ' . $e->getMessage()]);
    }
    
    // Tutup statement dan koneksi
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} else {
    // Jika bukan GET request, kembalikan error
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>