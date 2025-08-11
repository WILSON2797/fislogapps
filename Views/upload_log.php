<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
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
        <div class="table-responsive">
            <div class="table-wrapper">
                <button id="refreshBtn" class="btn btn-primary mb-3">Refresh Status</button>
                <table id="fileStatusTable" class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>File Name</th>
                            <th>Status</th>
                            <th>Row Success</th>
                            <th>Error Message</th>
                            <th>Upload Date</th>
                            <th>Upload By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include 'layouts/footer.php'; ?>