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

//Periksa apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header('Location: ../login.php');
    exit;
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
                <h4 class="mb-0"><i class="fas fa-users me-2"></i>Register Users</h4>
                <div>
                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-plus-circle me-1"></i> Tambah Users
                    </button>
                </div>
            </div>
            <!-- Tabel untuk menampilkan data -->
            <div class="table-responsive">
                <div class="table-wrapper">
                    <table id="tabelusers" class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th style="background-color: #5F9EA0; color: #fff;"class="users-no">No</th>
                                <th style="background-color: #5F9EA0; color: #fff;">Nama</th>
                                <th style="background-color: #5F9EA0; color: #fff;">Username</th>
                                <th style="background-color: #5F9EA0; color: #fff;">Region</th>
                                <th style="background-color: #5F9EA0; color: #fff;">Role</th>
                                <th style="background-color: #5F9EA0; color: #fff;"class="action-column">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan diisi oleh DataTables melalui API -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Reset Password -->
        <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="resetPasswordForm">
                            <input type="hidden" name="user_id" id="user_id">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'Register_user.php'; ?>
<?php include 'layouts/footer.php'; ?>