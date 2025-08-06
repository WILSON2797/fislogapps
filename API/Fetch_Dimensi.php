<?php
include '../php/database.php';

header('Content-Type: application/json; charset=UTF-8');

$item_name = isset($_GET['item_name']) ? $_GET['item_name'] : '';

$query = "SELECT dimensi, uom FROM data_materials WHERE item_name = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $item_name);
$stmt->execute();
$result = $stmt->get_result();

$response = [];
if ($row = $result->fetch_assoc()) {
    $response['dimensi'] = $row['dimensi'];
    $response['uom'] = $row['uom']; // Tambahkan ini
} else {
    $response['dimensi'] = null;
    $response['uom'] = null;
}

echo json_encode($response);

$result->free();
$stmt->close();
$conn->close();
?>