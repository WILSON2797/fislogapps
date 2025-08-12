<div class="sidebar" id="side_nav">
    <div class="header-box position-relative">
        <button class="btn close-btn position-absolute">
            <i class="fas fa-bars text-white"></i>
        </button>
        <div class="d-flex align-items-center justify-content-center mb-4">
            <div style="
                width: 120px; 
                height: 120px; 
                border-radius: 50%; 
                background-color: transparent;
                background: linear-gradient(135deg, teal, #d3cdcdff); /* gradasi teal -> hitam */
                padding: 60px; 
                box-shadow: 0 4px 12px rgba(0,0,0,0.3); /* bayangan lebih tegas */
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <img src="../assets/img/LogoFis.png" 
                     alt="Company Logo" 
                     style="
                        width: 70px; 
                        height: 80px; 
                        object-fit: contain;
                     ">
            </div>
        </div>
    </div>
    <ul class="list-unstyled px-2">
        <li><a href="dashboard.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-home"></i>
                Dashboard</a></li>
    </ul>

    <ul class="list-unstyled px-2">
        <?php if ($_SESSION['role'] !== 'customer'): ?>
        <li><a href="NewTask.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-list-check"></i>List
                Ordered</a></li>
        <?php endif; ?>
    </ul>
    <hr class="h-color mx-2">
    <ul class="list-unstyled px-2">
        <?php if ($_SESSION['role'] === 'admin'|| $_SESSION['role'] === 'superadmin'): ?>
        <li><a href="OrderReq_Detail.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-file"></i>
                Order Req Tracking</a></li>
        <?php endif; ?>
    </ul>
    <ul class="list-unstyled px-2">
        <li><a href="Completed.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-check"></i> Task
                Order Done</a></li>
    </ul>
    <hr class="h-color mx-2">
    <ul class="list-unstyled px-2">
        <li><a href="ReportTask.php" class="text-decoration-none px-3 py-2 d-block"><i
                    class="fas fa-database"></i>Reporting</a></li>
    </ul>

    <hr class="h-color mx-2">
    <ul class="list-unstyled px-2">
        <?php if ($_SESSION['role'] === 'admin'|| $_SESSION['role'] === 'superadmin'): ?>
        <!-- Menu hanya untuk admin -->
        <li><a href="settings.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-gear"></i> User
                Settings</a></li>
        <!-- Menu hanya untuk admin -->
        <li><a href="MasterMaterials.php" class="text-decoration-none px-3 py-2 d-block"><i
                    class="fas fa-clipboard-list"></i> Master Material</a></li>
        <?php endif; ?>
        <li><a href="profile.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-user"></i> Profile</a>
        </li>
        <li><a href="logout.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-sign-out-alt"></i>
                Logout</a></li>
    </ul>
</div>