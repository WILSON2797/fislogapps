<?php
header('Content-Type: application/json');

include '../php/database.php';
session_start();

try {
    // Query dasar
    $query = "SELECT * FROM tasks WHERE deleted_at IS NULL";
    $params = [];
    $types = "";

    // Tambahkan kondisi WHERE berdasarkan role user
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] !== 'superadmin') {
            // Untuk admin, user, dan customer, filter berdasarkan wh_name
            if (isset($_SESSION['wh_name'])) {
                $query .= " AND wh_name = ?";
                $params[] = $_SESSION['wh_name'];
                $types .= "s";
            } else {
                // Jika wh_name tidak ada di session, kembalikan array kosong
                echo json_encode([]);
                exit();
            }
        }
        // Superadmin tidak perlu filter tambahan
    } else {
        // Jika role tidak ada di session, kembalikan array kosong
        echo json_encode([]);
        exit();
    }

    // Urutkan berdasarkan created_at
    $query .= " ORDER BY created_at ASC";

    // Siapkan dan jalankan query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'Gagal mengambil data: ' . $e->getMessage()]);
}

$conn->close();
?>