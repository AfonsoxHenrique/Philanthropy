<!-- Admin Top Bar -->
<header class="admin-topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <!-- Sidebar toggle (mobile only) -->
        <button class="btn sidebar-toggle d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"
            aria-controls="adminSidebar" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>

        <a href="dashboard.php" class="admin-brand d-flex align-items-center gap-2">
            <img src="../assets/images/footerLogo2.png" style="height: 60px; width: auto;">
            <span>Phill Anthropy Console</span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="d-none d-sm-flex align-items-center gap-2">
            <img src="<?php echo htmlspecialchars($adminPhoto); ?>" class="admin-avatar" alt="">
            <span class="admin-name"><?php echo htmlspecialchars($adminName); ?></span>
            <span class="badge role-badge text-uppercase"><?php echo htmlspecialchars($adminRole); ?></span>
        </div>
        <a href="include/logout.php" class="btn btn-logout">Log Out</a>
    </div>
</header>