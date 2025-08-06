<?php
session_start();

// Periksa apakah pengguna adalah admin atau superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Include file konfigurasi database
require_once '../php/database.php';

try {
    // Query dasar
    $query = "SELECT id, nama, username, wh_name, role FROM users";
    $params = [];
    
    // Tambahkan kondisi WHERE berdasarkan role
    if ($_SESSION['role'] === 'admin' && isset($_SESSION['wh_name'])) {
        $query .= " WHERE wh_name = :wh_name";
        $params[':wh_name'] = $_SESSION['wh_name'];
    }
    
    // Siapkan dan jalankan query menggunakan PDO
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sanitasi data untuk mencegah XSS
    $sanitized_users = [];
    foreach ($users as $user) {
        $sanitized_users[] = [
            'id' => $user['id'],
            'nama' => htmlspecialchars($user['nama']),
            'username' => htmlspecialchars($user['username']),
            'wh_name' => htmlspecialchars($user['wh_name']),
            'role' => htmlspecialchars($user['role'])
        ];
    }

    // Kembalikan data dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($sanitized_users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data: ' . $e->getMessage()]);
    exit;
}
?>