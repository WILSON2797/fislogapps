<?php
session_start();
include '../php/database.php';

$order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if ($item_id > 0) {
    $query = "SELECT * FROM task_items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $item_id);
} else {
    $query = "SELECT * FROM task_items WHERE order_number = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $order_number); // Menggunakan 's' karena order_number adalah string
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
exit();
?>