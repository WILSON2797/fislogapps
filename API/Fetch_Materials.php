<?php
session_start(); // Tambahkan ini di awal untuk mengakses sesi
include '../php/database.php';

// Pastikan header JSON
header('Content-Type: application/json; charset=UTF-8');

// Ambil parameter pencarian (jika ada)
$searchTerm = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

// Ambil wh_name dari sesi
$wh_name = isset($_SESSION['wh_name']) ? mysqli_real_escape_string($conn, $_SESSION['wh_name']) : '';

// Query untuk mengambil data materials dengan filter pencarian dan wh_name
$query = "SELECT id, item_name FROM data_materials WHERE item_name LIKE ? AND wh_name = ? ORDER BY item_name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $searchTerm, $wh_name);
$stmt->execute();
$result = $stmt->get_result();

$materials = [];
while ($row = $result->fetch_assoc()) {
    $materials[] = [
        'id' => $row['item_name'], // Select2 menggunakan 'id' sebagai value
        'text' => $row['item_name'] // Select2 menggunakan 'text' sebagai label
    ];
}

echo json_encode(['results' => $materials]);

$result->free();
$stmt->close();
$conn->close();
?>