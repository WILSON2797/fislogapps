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
                <h4 class="mb-0"><i class="fas fa-checklist"></i> Completed Tasks</h4>
                <div>
                    <button class="btn btn-success btn-sm" id="exportTask">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <table id="tabelCompletedTask" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th style="background-color: #008080;">No</th>
                                    <th style="background-color: #008080;">SDR Number</th>
                                    <th style="background-color: #008080;">Destination</th>
                                    <th style="background-color: #008080;">Driver Name</th>
                                    <th style="background-color: #008080;">Total Collie</th>
                                    <th style="background-color: #008080;">Total CBM</th>
                                    <th style="background-color: #008080;">Date Pickup</th>
                                    <th style="background-color: #008080;">Submit By</th>
                                    <th style="background-color: #008080;">Submitted by (Region)</th>
                                    <th style="background-color: #008080;">Action</th>
                                </tr>
                                <tr class="table-search">
                                    <th><input type="text" class="form-control form-control-sm"></th>
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

<!-- Modal Tambah/Edit Item -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Edit Item untuk Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm" action="../modules/Proses_EditItem.php" method="POST">
                    <input type="hidden" name="order_number" id="order_number">
                    <!-- <input type="hidden" name="destination" id="destination"> -->
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
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name (Nama Barang)</th>
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

<!-- Modal untuk melihat item -->
<div class="modal fade" id="viewItemsModal" tabindex="-1" aria-labelledby="viewItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewItemsModalLabel">Daftar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabelItems" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item Name</th>
                                <th>Panjang</th>
                                <th>Lebar</th>
                                <th>Tinggi</th>
                                <th>Qty</th>
                                <th>UOM</th>
                                <th style="width: 20px; align-items: center;">Action</th>
                            </tr>
                            <tr class="table-search">
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
<script>
var userRole =
"<?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8') : ''; ?>";
</script>
<?php include 'layouts/footer.php'; ?>