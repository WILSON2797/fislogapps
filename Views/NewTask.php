<?php
session_start(); // Mulai sesi
// Atur waktu timeout sesi (20 menit = 1200 detik)
$timeout_duration = 1200;
// Periksa apakah sesi terakhir aktif ada
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Sesi telah kedaluwarsa, hapus semua data sesi
    session_unset();
    session_destroy();
    header("Location: ../login?message=session_expired");
    exit();
}
// Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    // Jika belum login, arahkan ke halaman login
    header('Location: ../login.php');
    exit();
}
?>
<?php include 'layouts/header.php'; ?>
<div class="main-container d-flex">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="content">
        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <div class="container-fluid">
                <button class="btn px-1 py-0 open-btn me-2"><i class="fas fa-bars"></i></button>
                <div class="dropdown">
                    <a href="#" class="navbar-brand dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <div class="user-icon-circle">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fa-solid fa-user-circle"></i>
                                Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fa-solid fa-sign-out-alt"></i>
                                Log Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="card m-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <?php 
                    // Cek apakah role bukan customer atau user
                    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'customer' && $_SESSION['role'] !== 'user')) : 
                    ?>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#NewTaskModal">
                        <i class="fas fa-plus me-1"></i> Tambah Data
                    </button>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bulkNewTaskModal">
                        <i class="fas fa-upload me-1"></i> Bulk Upload
                    </button>
                    <a href="upload_log.php" class="btn btn-dark btn-sm">
                        <i class="fas fa-file"></i>Upload Status Log
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-success btn-sm" id="exportTask">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <table id="tabelNewTask" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th class="hide-mobile" style="background-color: #008080;">No</th>
                                    <th style="background-color: #008080;">SDR Number</th>
                                    <th style="background-color: #008080;">Site ID</th>
                                    <th style="background-color: #008080;">Site Name</th>
                                    <th style="background-color: #008080;">Customer / Supplier</th>
                                    <th style="background-color: #008080;">Destination</th>
                                    <th style="background-color: #008080;">Create Date</th>
                                    <th style="background-color: #008080;">Create By</th>
                                    <th style="background-color: #008080;">Status</th>
                                    <th style="background-color: #008080;">Action</th>
                                </tr>
                                <tr class="table-search">
                                    <th class="hide-mobile"><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th><input type="text" class="form-control form-control-sm"></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Task -->
<div class="modal fade" id="NewTaskModal" tabindex="-1" aria-labelledby="NewTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inboundModalLabel">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="NewTaskForm" action="../modules/Proses_NewTask.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Order Number</label>
                            <input type="text" name="order_number" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Site ID</label>
                            <input type="text" name="site_id" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Customer / Supplier</label>
                            <input type="text" name="customer" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bulk Upload Task -->
<div class="modal fade" id="bulkNewTaskModal" tabindex="-1" aria-labelledby="bulkNewTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkNewTaskModalLabel">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Bulk Upload Form
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Download Template Section -->
                <div class="download-template">
                    <strong>Upload File xlsx</strong>
                    <p class="mb-2 mt-2"></p>
                    <button onclick="window.open('../modules/export_template.php', '_blank')"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Download Template
                    </button>
                </div>

                <form id="bulkNewTaskForm" action="../modules/Proses_BulkNewTask.php" method="POST"
                    enctype="multipart/form-data">
                    <!-- Upload Section -->
                    <div class="upload-section" id="uploadSection">
                        <div class="text-center">
                            <i class="fas fa-file-upload upload-icon"></i>
                            <div class="upload-text">
                                <strong>Drop your file here, or Browse</strong>
                            </div>
                            <div class="upload-subtext">
                                Maksimum file size 50mb
                            </div>
                            <button type="button" class="btn btn-browse" id="browseButton">
                                <i class="fas fa-folder-open me-2"></i>Browse File Here
                            </button>
                            <input type="file" name="xlxs" class="file-input" id="fileInput" accept=".xlsx,.xls"
                                required>
                            <div class="format-info">
                                <span>Supported formats:</span>
                                <span class="format-badge">xlsx</span>

                            </div>
                        </div>
                    </div>

                    <!-- File Info Section -->
                    <div class="file-info" id="fileInfo">
                        <div class="file-details">
                            <div>
                                <div class="file-name" id="fileName"></div>
                                <div class="file-size" id="fileSize"></div>
                            </div>
                            <button type="button" class="remove-file" id="removeFile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="progress-container" id="progressContainer">
                            <div class="progress">
                                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Item -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Tambah Item untuk Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm" action="../modules/Proses_AddItems.php" method="POST">
                    <input type="hidden" name="order_number" id="order_number">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date Pickup</label>
                            <input type="date" name="date_pickup" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Driver Name</label>
                            <input type="text" name="driver_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" class="form-control" readonly id="destination"
                                required>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered item-table">
                            <thead>
                                <tr>
                                    <th style="width: 500px;">Item Name (Nama Barang)</th>
                                    <th>Total Quantity</th>
                                    <th>Unit of Measure</th>
                                    <th>Dimensi Material</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                <!-- Baris item akan ditambahkan secara dinamis oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success mb-3" id="addItemRow">Tambah Item</button>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>