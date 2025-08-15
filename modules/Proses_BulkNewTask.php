<?php
session_start();
include '../php/database.php';
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xlxs'])) {
    $file = $_FILES['xlxs'];
    
    // Validasi file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $response['message'] = 'File terlalu besar, melebihi batas upload_max_filesize.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $response['message'] = 'File terlalu besar, melebihi batas MAX_FILE_SIZE.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $response['message'] = 'File hanya terunggah sebagian.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $response['message'] = 'Tidak ada file yang diunggah.';
                break;
            default:
                $response['message'] = 'Error unggah file: Kode ' . $file['error'];
                break;
        }
        echo json_encode($response);
        exit();
    }
    
    // Validasi ekstensi file
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'xlsx') {
        $response['message'] = 'Hanya file Excel (.xlsx) yang diperbolehkan.';
        echo json_encode($response);
        exit();
    }
    
    $originalFileName = $file['name'];
    $username = $_SESSION['username'];
    $whName = $_SESSION['wh_name'] ?? 'default';
    
    // PENGECEKAN DUPLIKASI GLOBAL - FILE NAME
    $checkQuery = "SELECT id, username, wh_name, created_at FROM queue_tasks WHERE file_name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('s', $originalFileName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $existingFile = $result->fetch_assoc();
        $response['message'] = "File name Already exist, please changes Filename & Try Again";
        $checkStmt->close();
        echo json_encode($response);
        exit();
    }
    $checkStmt->close();
    
    // Simpan file
    $uploadDir = '../Uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename untuk physical file (mencegah konflik di file system)
    $fileName = uniqid() . '-' . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Insert ke database
        $query = "INSERT INTO queue_tasks (file_path, file_name, username, wh_name, status, created_at) 
                  VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssss', $filePath, $originalFileName, $username, $whName);
        
        if ($stmt->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Upload Success. Check the status on the log page.'
            ];
        } else {
            $response['message'] = 'Gagal menyimpan metadata ke database.';
            // Hapus file yang sudah terupload jika gagal insert ke database
            unlink($filePath);
        }
        $stmt->close();
    } else {
        $response['message'] = 'Gagal menyimpan file ke folder uploads.';
    }
} else {
    $response['message'] = 'Metode request tidak valid atau file tidak diunggah.';
}

echo json_encode($response);
$conn->close();
?>