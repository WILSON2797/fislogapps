<?php
session_start(); // Mulai sesi
// Atur waktu timeout sesi (30 menit = 1800 detik)
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

include '../php/database.php';

// Cek apakah session ada
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
$page_title = "User Profile"; // Untuk judul halaman
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
        <div class="content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2" style="margin-left:15px">User Profile</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Profile Information</h4>
                    <!-- Tombol untuk membuka modal Change Password -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        Ubah Password
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Nama</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Change Password -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Ubah Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm" action="../php/proses_change_password.php" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah submit default

            const form = this;

            fetch('../php/proses_change_password.php', { // Sesuaikan path jika perlu
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#changePasswordModal').modal('hide'); // Tutup modal
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Sistem',
                    text: 'Terjadi kesalahan saat mengirim data.',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
</div>

<?php include 'layouts/footer.php'; ?>