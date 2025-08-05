<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/home/u697482187/domains/fis-maintenance.site/public_html/fis-application-cwh/error.log');

echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "Script dimulai pada: " . date('Y-m-d H:i:s') . "\n";

$logFile = '/home/u697482187/domains/fis-maintenance.site/public_html/fis-application-cwh/cron.log';
$debugLog = "=== DEBUG START ===\n";
$debugLog .= "PHP SAPI: " . php_sapi_name() . "\n";
$debugLog .= "Script dimulai pada: " . date('Y-m-d H:i:s') . "\n";
$debugLog .= "Current working directory: " . getcwd() . "\n";
file_put_contents($logFile, $debugLog, FILE_APPEND | LOCK_EX);

echo "hello world\n";
file_put_contents($logFile, "Script Process_QueueTasks.php mulai berjalan pada " . date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);
error_log("Script Process_QueueTasks.php mulai berjalan pada " . date('Y-m-d H:i:s') . "\n");

$allowedSapis = ['cli', 'cgi-fcgi', 'fpm-fcgi'];
if (!in_array(php_sapi_name(), $allowedSapis)) {
    $error = "Access denied: This script can only be run from command line or CGI. Current SAPI: " . php_sapi_name() . "\n";
    echo $error;
    file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
    exit(1);
}
echo "Pengecekan SAPI berhasil: " . php_sapi_name() . "\n";

$baseDir = '/home/u697482187/domains/fis-maintenance.site/public_html/fis-application-cwh';
$autoloadPath = $baseDir . '/vendor/autoload.php';
$databasePath = $baseDir . '/php/database.php';

if (!file_exists($autoloadPath)) {
    $error = "Gagal: File vendor/autoload.php tidak ditemukan di $autoloadPath\n";
    error_log($error);
    echo $error;
    file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
    exit(1);
}
if (!file_exists($databasePath)) {
    $error = "Gagal: File database.php tidak ditemukan di $databasePath\n";
    error_log($error);
    echo $error;
    file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
    exit(1);
}

try {
    require_once $autoloadPath;
    echo "vendor/autoload.php berhasil di-load.\n";
} catch (Throwable $e) {
    $error = "Gagal load vendor/autoload.php: " . $e->getMessage() . "\n";
    error_log($error);
    echo $error;
    file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
    exit(1);
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

try {
    include_once $databasePath;
    if (!isset($conn) || !$conn) {
        throw new Exception("Koneksi database tidak tersedia");
    }
    $testQuery = $conn->query("SELECT 1");
    if (!$testQuery) {
        throw new Exception("Test query gagal: " . $conn->error);
    }
    echo "Test koneksi database berhasil.\n";
} catch (Throwable $e) {
    $error = "Gagal load database.php atau koneksi: " . $e->getMessage() . "\n";
    if (isset($conn) && $conn->error) {
        $error .= "MySQL Error: " . $conn->error . "\n";
    }
    error_log($error);
    echo $error;
    file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
    exit(1);
}

try {
    $query = "SELECT id, file_path, file_name FROM queue_tasks WHERE status = 'pending' LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt || !$stmt->execute()) {
        throw new Exception("Query gagal: " . ($conn->error ?? $stmt->error));
    }
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
    $stmt->close();
    echo "Jobs found: " . ($job ? "Yes (ID: " . $job['id'] . ")" : "No") . "\n";
} catch (Throwable $e) {
    $error = "Gagal query database: " . $e->getMessage() . "\n";
    error_log($error);
    echo $error;
    file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
    exit(1);
}

if ($job) {
    $jobId = $job['id'];
    $filePath = $job['file_path'];
    $fileName = $job['file_name'];

    try {
        $updateQuery = "UPDATE queue_tasks SET status = 'processing' WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('i', $jobId);
        if (!$updateStmt->execute()) {
            throw new Exception("Update status gagal: " . $updateStmt->error);
        }
        $updateStmt->close();
        echo "Status job diupdate ke 'processing'.\n";
    } catch (Throwable $e) {
        $error = "Gagal update status: " . $e->getMessage() . "\n";
        error_log($error);
        echo $error;
        file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
        exit(1);
    }

    try {
        function resolveFilePath($originalPath, $baseDir) {
            $possiblePaths = [
                $originalPath,
                $baseDir . '/' . ltrim($originalPath, '/'),
                $baseDir . '/Uploads/' . basename($originalPath),
                '/home/u697482187/domains/fis-maintenance.site/public_html/Uploads/' . basename($originalPath)
            ];
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    error_log("File ditemukan di: $path");
                    return $path;
                }
            }
            error_log("File tidak ditemukan di: " . implode(', ', $possiblePaths));
            return null;
        }

        $resolvedPath = resolveFilePath($filePath, $baseDir);
        if (!$resolvedPath) {
            throw new Exception("File tidak ditemukan: $filePath");
        }
        $filePath = $resolvedPath;
        echo "File ditemukan di: $filePath\n";

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("File tidak dapat dibaca: $filePath");
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        echo "File Excel dibaca. Total baris: " . count($rows) . "\n";

        // Inisialisasi laporan sebelum validasi
        $reportData = [['Order Number', 'Site ID', 'Site Name', 'Customer', 'Destination', 'Error Log']];
        $successCount = 0;
        $errorMessages = [];

        // Validasi header
        $expectedHeaders = ['Order Number', 'Site ID', 'Site Name', 'Customer / Supplier', 'Destination'];
        if (empty($rows)) {
            $errorMessages[] = 'File Excel kosong';
        } else {
            $headers = array_map('trim', $rows[0]);
            if (count($headers) !== count($expectedHeaders) || array_diff($expectedHeaders, $headers) !== [] || array_diff($headers, $expectedHeaders) !== []) {
                $errorMessages[] = 'Format header Excel tidak sesuai. Expected: ' . implode(', ', $expectedHeaders) . ', Found: ' . implode(', ', $headers);
            }
        }
        if (!empty($errorMessages)) {
            throw new Exception(implode('; ', $errorMessages));
        }
        echo "Validasi header berhasil.\n";

        $rowsToProcess = array_slice($rows, 1);
        echo "Processing " . count($rowsToProcess) . " data rows...\n";

        // MULAI TRANSACTION
        $conn->autocommit(FALSE);
        $conn->begin_transaction();

        $insertQuery = "INSERT INTO tasks (order_number, site_id, site_name, customer, destination, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        if (!$insertStmt) {
            throw new Exception("Prepare insert gagal: " . $conn->error);
        }

        $hasError = false;
        $duplicateFound = false;

        for ($i = 0; $i < count($rowsToProcess); $i++) {
            $row = array_map('trim', $rowsToProcess[$i]);
            $rowNumber = $i + 2;

            if (count($row) < 5 || empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4])) {
                $errorMessages[] = "Baris $rowNumber: Data tidak lengkap";
                $reportData[] = [$row[0] ?? '', $row[1] ?? '', $row[2] ?? '', $row[3] ?? '', $row[4] ?? '', 'Data tidak lengkap'];
                $hasError = true;
                continue;
            }

            $order_number = $row[0];
            $site_id = $row[1];
            $site_name = $row[2];
            $customer = $row[3];
            $destination = $row[4];

            $checkQuery = "SELECT COUNT(*) as count FROM tasks WHERE order_number = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('s', $order_number);
            if (!$checkStmt->execute()) {
                throw new Exception("Check duplicate gagal: " . $checkStmt->error);
            }
            $checkResult = $checkStmt->get_result();
            $rowCount = $checkResult->fetch_assoc()['count'];
            $checkStmt->close();

            if ($rowCount > 0) {
                $errorMessages[] = "Baris $rowNumber: Duplicate Order Number '$order_number'";
                $reportData[] = [$order_number, $site_id, $site_name, $customer, $destination, "Duplicate Order Number '$order_number'"];
                $hasError = true;
                $duplicateFound = true;
            } else {
                $insertStmt->bind_param('sssss', $order_number, $site_id, $site_name, $customer, $destination);
                if ($insertStmt->execute()) {
                    $reportData[] = [$order_number, $site_id, $site_name, $customer, $destination, 'Pending'];
                    $successCount++;
                } else {
                    $errorMessages[] = "Baris $rowNumber: Gagal insert: " . $insertStmt->error;
                    $reportData[] = [$order_number, $site_id, $site_name, $customer, $destination, "Gagal insert"];
                    $hasError = true;
                }
            }
        }

        $insertStmt->close();

        // ROLLBACK JIKA ADA ERROR ATAU DUPLICATE
        if ($hasError || $duplicateFound) {
            $conn->rollback();
            $status = 'error';
            $errorMessage = implode('; ', $errorMessages);
            $successCount = 0;
            echo "ROLLBACK dilakukan karena ada error atau duplicate.\n";
            
            // Update status report menjadi "Pending" untuk yang berhasil sebelum rollback
            for ($i = 1; $i < count($reportData); $i++) {
                if ($reportData[$i][5] === 'Pending') {
                    $reportData[$i][5] = 'Pending';
                }
            }
        } else {
            $conn->commit();
            $status = $successCount > 0 ? 'success' : 'warning';
            $errorMessage = null;
            echo "COMMIT berhasil.\n";
        }
        
        // Kembalikan ke autocommit
        $conn->autocommit(TRUE);

    } catch (Throwable $e) {
        // ROLLBACK pada error
        $conn->rollback();
        $conn->autocommit(TRUE);
        $status = 'error';
        $errorMessage = $e->getMessage();
        $successCount = 0;
        echo "ROLLBACK dilakukan karena exception: " . $e->getMessage() . "\n";
        
        // Pastikan semua entry dalam report menunjukkan status error
        for ($i = 1; $i < count($reportData); $i++) {
            if ($reportData[$i][5] === 'Pending') {
                $reportData[$i][5] = 'Pending';
            }
        }
        if (empty($reportData) || count($reportData) === 1) {
            $reportData[] = ['', '', '', '', '', $errorMessage];
        }
    }

    // Pembuatan laporan selalu dilakukan
    $reportSpreadsheet = new Spreadsheet();
    $reportSheet = $reportSpreadsheet->getActiveSheet();
    $reportSheet->fromArray($reportData, null, 'A1');

    $reportDir = $baseDir . '/Uploads/reports/';
    if (!is_dir($reportDir) && !mkdir($reportDir, 0755, true)) {
        throw new Exception("Gagal buat direktori laporan: $reportDir");
    }
    $reportFileName = 'Error_Report_' . uniqid() . '_' . $fileName;
    $reportPath = $reportDir . $reportFileName;
    $writer = IOFactory::createWriter($reportSpreadsheet, 'Xlsx');
    $writer->save($reportPath);

    $updateQuery = "UPDATE queue_tasks SET status = ?, error_message = ?, success_count = ?, report_path = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ssisi', $status, $errorMessage, $successCount, $reportPath, $jobId);
    if (!$updateStmt->execute()) {
        throw new Exception("Update status gagal: " . $updateStmt->error);
    }
    $updateStmt->close();

    echo "Job selesai: $successCount data valid, status: $status\n";
    file_put_contents($logFile, "Selesai memproses $fileName - $successCount data valid, status: $status\n", FILE_APPEND | LOCK_EX);
} else {
    echo "Tidak ada job pending.\n";
    file_put_contents($logFile, "Tidak ada job pending pada " . date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);
}

if (isset($conn)) $conn->close();
echo "Script selesai pada: " . date('Y-m-d H:i:s') . "\n";
file_put_contents($logFile, "=== DEBUG END ===\n", FILE_APPEND | LOCK_EX);
exit(0);
?>