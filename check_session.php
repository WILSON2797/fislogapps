<?php
session_start();
$timeout_duration = 1200; // 20 menit
$response = ['status' => 'active'];

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    $response['status'] = 'expired';
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>