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
                        <a href="#" class="navbar-brand dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-icon-circle">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                        </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php">Log out</a></li>
                </ul>
            </div>
            </div>
        </nav>

        <div class="container-fluid">
            <!-- Header Dashboard -->
            <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h2>

            <!-- Statistik Cards -->
            <div class="row mb-4">
                <!-- Total Pending Tasks -->
                <div class="col-md-4">
                    <div class="card shadow-sm text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tasks me-2"></i> Total Pending Tasks</h5>
                            <h3 class="card-text" id="totalPendingTasks">0</h3>
                        </div>
                    </div>
                </div>
                <!-- Total Completed Tasks -->
                <div class="col-md-4">
                    <div class="card shadow-sm text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-check-circle me-2"></i> Total Completed Tasks</h5>
                            <h3 class="card-text" id="totalCompletedTasks">0</h3>
                        </div>
                    </div>
                </div>
                <!-- Total Tasks -->
                <div class="col-md-4">
                    <div class="card shadow-sm text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tasks me-2"></i> Total Tasks</h5>
                            <h3 class="card-text" id="totalAllTasks">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <!-- Tasks per Region -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar me-2"></i> Tasks per Region</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="tasksPerRegionChart" class="chart-canvas" style="height: 600px;"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Tasks per Destination (Pie Chart) -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie me-2"></i> Tasks per Destination</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="tasksPerDestinationChart" class="chart-canvas" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Over Time (Line Chart) -->
            <!-- <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line me-2"></i> Tasks Over Time</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="tasksOverTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>
<?php include 'layouts/footer.php'; ?>