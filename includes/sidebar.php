<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo-wrapper">
            <a href="system.php?page=dashboard">
                <img class="sidebar-logo" src="img/logo.png" alt="QADIRA INTERNATIONAL" onerror="this.style.display='none'">
            </a>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="system.php?page=dashboard" class="nav-item <?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="system.php?page=jobs" class="nav-item <?php echo ($page == 'jobs') ? 'active' : ''; ?>">
            <i class="fas fa-briefcase"></i> Jobs
        </a>
        <a href="system.php?page=create-job" class="nav-item <?php echo ($page == 'create-job') ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i> New Job
        </a>
        <a href="system.php?page=bills" class="nav-item <?php echo ($page == 'bills') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i> Bills
        </a>
        <?php if (isAdmin()): ?>
        <a href="system.php?page=masters" class="nav-item <?php echo ($page == 'masters') ? 'active' : ''; ?>">
            <i class="fas fa-database"></i> Masters
        </a>
        <?php endif; ?>
        <a href="system.php?page=invoices" class="nav-item <?php echo ($page == 'invoices') ? 'active' : ''; ?>">
            <i class="fas fa-folder-open"></i> Invoices
        </a>
        <a href="system.php?page=karachi-clearance" class="nav-item <?php echo ($page == 'karachi-clearance') ? 'active' : ''; ?>">
            <i class="fas fa-ship"></i> Karachi Clearance
        </a>
        <a href="system.php?page=loading-files" class="nav-item <?php echo ($page == 'loading-files') ? 'active' : ''; ?>">
            <i class="fas fa-truck-loading"></i> Loading File
        </a>
        <?php if (isAdmin()): ?>
        <a href="system.php?page=users" class="nav-item <?php echo ($page == 'users') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="system.php?page=manage-permissions" class="nav-item <?php echo ($page == 'manage-permissions') ? 'active' : ''; ?>">
            <i class="fas fa-key"></i> Permissions
        </a>
  
        
        <?php endif; ?>
    </nav>
</div>