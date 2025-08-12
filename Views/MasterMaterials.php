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
                <h4 class="mb-0"><i class="fas fa-arrow-circle-down me-2"></i>Add Materials</h4>
                <div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#MaterialsModal" id="addMaterialsBtn">
                        <i class="fas fa-plus me-1"></i> Add Materials
                    </button>
                    <button class="btn btn-success btn-sm" id="exportExcelMaterials">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <table id="tabelMaterials" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th style="background-color: #008080;">No</th>
                                    <th style="background-color: #008080;">Material Name</th>
                                    <th style="background-color: #008080;">Dimensi</th>
                                    <th style="background-color: #008080;">UOM</th>
                                    <th style="background-color: #008080; "class="action-column">Action</th>
                                </tr>
                                <tr class="table-search">
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
<!-- Modal Tambah/Edit MATERIALS -->
<div class="modal fade" id="MaterialsModal" tabindex="-1" aria-labelledby="materialsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="materialsModalLabel">Tambah Data Materials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="MaterialsForm" method="POST">
                    <input type="hidden" name="id" id="material_id">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Materials Name</label>
                            <input type="text" name="item_name" id="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Dimension</label>
                            <input type="text" name="dimensi" id="dimensi" class="form-control" placeholder="Misalkan 300x50x50"required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Uom</label>
                            <input type="text" name="uom" id="uom" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="saveMaterialsBtn">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>