<div class="desktop-footer">
    <p>&copy | Fan Indonesia Sejahtera</p>
</div>

<!-- Mobile Bottom Navigation dengan animasi CSS -->
<div class="mobile-footer-nav">
    <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="NewTask.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'NewTask.php' ? 'active' : ''; ?>">
        <i class="far fa-clock"></i>
        <span>Pending</span>
        <!-- Contoh penggunaan notification dot (hapus jika tidak perlu) -->
        <!-- <span class="notification-dot"></span> -->
    </a>
    <a href="Completed.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'Completed.php' ? 'active' : ''; ?>">
        <i class="fas fa-check"></i>
        <span>Completed</span>
    </a>
    <a href="logout.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'active' : ''; ?>">
        <i class="fas fa-sign-out-alt"></i>
        <span>Log-out</span>
    </a>
</div>
<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/app_new.js"></script>
<!-- <script src="assets/js/custom.js"></script> -->
<!-- Loading Spinner -->
<div id="loadingOverlay" class="loading-overlay">
  <div class="container">
    <div class="spinner">
      <div class="grok-spinner">
        <div class="grok-dot"></div>
        <div class="grok-dot"></div>
        <div class="grok-dot"></div>
      </div>
    </div>
    <div class="text" id="loading-text">Loading...</div>
    <p class="sub-text" id="sub-loading-text">The data is being processed.</p>
  </div>
</div>
</body>

</html>