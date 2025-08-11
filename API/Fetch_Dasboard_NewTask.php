<?php
include '../php/database.php';
session_start();

$data = [];

try {
    // Query dasar untuk mengambil semua data
    $query = "SELECT id, order_number, site_id, site_name, customer, destination, created_at, status, wh_name 
              FROM tasks 
              WHERE deleted_at IS NULL";
    
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
                error_log("Fetch_NewTask - Filtering by wh_name: " . $_SESSION['wh_name']);
            } else {
                error_log("Fetch_NewTask - No wh_name in session for role: " . $_SESSION['role']);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No warehouse name defined for this user.']);
                exit();
            }
        }
        // Superadmin tidak perlu filter tambahan
    } else {
        error_log("Fetch_NewTask - No role in session");
        header('Content-Type: application/json');
        echo json_encode(['error' => 'User role not defined.']);
        exit();
    }

    error_log("Fetch_NewTask - Session role: " . ($_SESSION['role'] ?? 'undefined') . ", wh_name: " . ($_SESSION['wh_name'] ?? 'undefined') . ", session_id: " . session_id());
    error_log("Fetch_NewTask - Query executed: " . $query);

    // Siapkan dan jalankan query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("Fetch_NewTask - Number of rows returned: " . $result->num_rows);

    while ($row = $result->fetch_assoc()) {
        // Debugging: Log nilai
        error_log("Fetch_NewTask - Row: order_number=" . ($row['order_number'] ?? 'NULL') . ", Destination=" . ($row['destination'] ?? 'EMPTY') . ", status=" . ($row['status'] ?? 'NULL') . ", wh_name=" . ($row['wh_name'] ?? 'NULL'));
        $data[] = [
            'id' => $row['id'],
            'order_number' => $row['order_number'] ?? '-',
            'site_id' => $row['site_id'] ?? '-',
            'site_name' => $row['site_name'] ?? '-',
            'customer' => $row['customer'] ?? '-',
            'destination' => $row['destination'] ?? '-',
            'created_at' => $row['created_at'] ?? '-',
            'status' => $row['status'] ?? '-',
            'wh_name' => $row['wh_name'] ?? '-'
        ];
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log("Fetch_NewTask - Error: " . $e->getMessage() . " | Query: " . $query);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Terjadi kesaluwarsa saat mengambil data: ' . $e->getMessage()]);
    exit();
}

header('Content-Type: application/json');
echo json_encode($data);
$conn->close();
exit();
?>