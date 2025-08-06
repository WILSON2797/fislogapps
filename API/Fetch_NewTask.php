<?php
include '../php/database.php';

// Mulai session untuk mendapatkan informasi user
session_start();

$data = [];

try {
    // Query dasar
    $query = "SELECT id, order_number, site_id, site_name, customer, destination, created_at, created_by, status 
              FROM tasks 
              WHERE status = 'pending' AND deleted_at IS NULL";
    
    // Parameter untuk bind (jika diperlukan)
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
                header('Content-Type: application/json');
                echo json_encode([]);
                exit();
            }
        }
        // Superadmin tidak perlu filter tambahan
    } else {
        // Jika role tidak ada di session, kembalikan array kosong
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    
    // Selesaikan query
    $query .= ";";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameter jika ada
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        error_log("Fetch_NewTask - Order Number: {$row['order_number']}, Destination: " . ($row['destination'] !== '' ? $row['destination'] : 'EMPTY'));
        $data[] = $row;
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log("Error in Fetch_NewTask: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([]);
}

header('Content-Type: application/json');
echo json_encode($data);
exit();
?>