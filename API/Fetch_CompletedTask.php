<?php
ob_start();
include '../php/database.php';
session_start();

error_log("Session role: " . ($_SESSION['role'] ?? 'undefined') . ", wh_name: " . ($_SESSION['wh_name'] ?? 'undefined') . ", session_id: " . session_id());

$data = [];

try {
    // Query dasar dengan logika CASE untuk qty
    $query = "SELECT 
                ti.order_number, 
                ti.destination, 
                ti.driver_name,
                ti.wh_name, 
                SUM(CASE 
                    WHEN LOWER(ti.uom) = 'mtr' THEN 1 
                    ELSE ti.qty 
                END) as qty,
                SUM(ti.total_cbm) as total_cbm, 
                ti.date_pickup,
                ti.submit_by
              FROM task_items ti
              WHERE ti.deleted_at IS NULL";
    
    $params = [];
    $types = "";

    // Tambahkan kondisi WHERE berdasarkan role user
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] !== 'superadmin') {
            // Untuk admin, user, dan customer, filter berdasarkan wh_name
            if (isset($_SESSION['wh_name'])) {
                $query .= " AND ti.wh_name = ?";
                $params[] = $_SESSION['wh_name'];
                $types .= "s";
                error_log("Filtering by wh_name: " . $_SESSION['wh_name']);
            } else {
                error_log("No wh_name in session for role: " . $_SESSION['role']);
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['data' => []]);
                exit();
            }
        }
        // Superadmin tidak perlu filter tambahan
    } else {
        error_log("No role in session");
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['data' => []]);
        exit();
    }

    // Selesaikan query dengan pengelompokan dan pengurutan
    $query .= " GROUP BY ti.order_number, ti.destination, ti.driver_name, ti.date_pickup, ti.submit_by, ti.wh_name
                ORDER BY ti.date_pickup ASC";

    error_log("Query executed: " . $query);

    // Siapkan dan jalankan query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("Fetch_ReportTask: Number of rows returned: " . $result->num_rows);

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'order_number' => $row['order_number'],
            'destination' => $row['destination'],
            'driver_name' => $row['driver_name'],
            'wh_name' => $row['wh_name'],
            'qty' => $row['qty'],
            'total_cbm' => $row['total_cbm'],
            'date_pickup' => $row['date_pickup'],
            'submit_by' => $row['submit_by']
        ];
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log("Error in Fetch_ReportTask: " . $e->getMessage() . " | Query: " . $query);
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}

ob_clean();
header('Content-Type: application/json; charset=utf-8');
try {
    echo json_encode(['data' => $data], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (JsonException $e) {
    error_log("JSON encoding error: " . $e->getMessage());
    echo json_encode(['error' => 'Invalid JSON data']);
}
$conn->close();
exit();
?>