<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Qadria International - <?php echo ucfirst($page); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#fcfdff 0%,#f8fafd 100%); min-height:100vh; }
        .dashboard-container { display:flex; min-height:100vh; }
        .sidebar { width:296px; background:#3a4984; position:fixed; height:100vh; overflow:hidden; z-index:100; box-shadow:2px 0 10px rgba(0,0,0,0.1); transition:transform 0.3s ease-in-out; transform:translateX(0); }
        .sidebar.collapsed { transform:translateX(-100%); }
        .sidebar-header { padding:30px 0px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.2); background:rgba(0,0,0,0.1); }
        .sidebar-logo-wrapper { background:white; padding:15px; border-radius:20px; display:inline-block; box-shadow:0 4px 15px rgba(0,0,0,0.15); }
        .sidebar-logo { height:75px; width:auto; display:block; margin:0 auto; }
        .sidebar-nav { padding:20px 0; }
        .nav-item { display:flex; align-items:center; padding:12px 20px; color:rgba(255,255,255,0.85); text-decoration:none; gap:12px; transition:all 0.3s; }
        .nav-item:hover, .nav-item.active { background:rgba(255,255,255,0.2); border-left:3px solid #8e181e; }
        .nav-item i { width:20px; }
        .main-content { flex:1; margin-left:296px; background:#fcfdff; min-height:100vh; transition:margin-left 0.3s ease-in-out; }
        body.sidebar-collapsed .main-content { margin-left:0; }
        .top-header { background:white; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.05); position:sticky; top:0; z-index:99; }
        .sidebar-toggle-header { background:#8e181e; width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.3s; margin-right:15px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        .sidebar-toggle-header:hover { background:#6b1217; transform:scale(1.02); }
        .sidebar-toggle-header i { color:white; font-size:18px; }
        .header-search { display:flex; align-items:center; background:#f5f9f5; padding:8px 15px; border-radius:12px; width:300px; }
        .header-search i { color:#479ace; margin-right:8px; }
        .header-search input { border:none; background:none; outline:none; width:100%; }
        .user-menu { display:flex; align-items:center; gap:15px; }
        .user-name { color:#3a4984; font-weight:600; }
        .user-avatar { width:40px; height:40px; background:#8e181e; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; cursor:pointer; position:relative; }
        .user-dropdown { position:absolute; top:50px; right:0; background:white; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.15); width:280px; display:none; z-index:1000; overflow:hidden; }
        .user-dropdown.show { display:block; }
        .dropdown-header { padding:15px; border-bottom:1px solid #e9edf2; background:#f8fafd; }
        .dropdown-header h4 { color:#3a4984; margin-bottom:5px; }
        .dropdown-header p { font-size:12px; color:#666; }
        .dropdown-item { padding:12px 15px; display:flex; align-items:center; gap:12px; text-decoration:none; color:#333; transition:all 0.3s; border-bottom:1px solid #e9edf2; cursor:pointer; }
        .dropdown-item:hover { background:#f8fafd; }
        .dropdown-item i { width:20px; color:#8e181e; }
        .dropdown-item.logout { color:#ef5350; }
        .dropdown-item.logout i { color:#ef5350; }
        .content-wrapper { padding:25px; }
        .dashboard-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:15px; }
        .page-title { font-size:1.8rem; color:#3a4984; }
        .page-subtitle { color:#479ace; margin-top:5px; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin-bottom:25px; }
        .stat-card { background:white; border-radius:16px; padding:20px; display:flex; align-items:center; justify-content:space-between; cursor:pointer; transition:all 0.3s; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
        .stat-card:hover { transform:translateY(-5px); box-shadow:0 5px 20px rgba(0,0,0,0.1); }
        .stat-icon { width:55px; height:55px; background:#f8fafd; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#8e181e; }
        .stat-info h3 { font-size:1.8rem; color:#3a4984; }
        .stat-info p { color:#479ace; }
        .card { background:white; border-radius:16px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.05); margin-bottom:20px; }
        .card-header { padding:15px 20px; border-bottom:1px solid #e9edf2; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
        .card-header h3 { color:#3a4984; font-size:1.2rem; }
        .card-header h3 i { color:#8e181e; margin-right:8px; }
        .card-body { padding:20px; }
        .table-container { overflow-x:auto; border-radius:12px; -webkit-overflow-scrolling:touch; }
        .data-table { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; min-width:600px; }
        .data-table thead { background:#3a4984; }
        .data-table th { color:white; padding:14px 12px; text-align:left; font-weight:600; font-size:0.85rem; white-space:nowrap; }
        .data-table td { padding:12px; border-bottom:1px solid #e9edf2; color:#4a5568; font-size:0.85rem; }
        .data-table tbody tr { transition:all 0.3s; cursor:pointer; }
        .data-table tbody tr:hover { background:#f8fafd; }
        .btn-primary { background:#8e181e; color:white; border:none; padding:8px 20px; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; font-size:0.85rem; transition:all 0.3s; }
        .btn-primary:hover { background:#6b1217; transform:translateY(-2px); box-shadow:0 4px 12px rgba(142,24,30,0.3); }
        .btn-danger { background:#ef5350; color:white; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:0.75rem; transition:all 0.3s; display:inline-flex; align-items:center; gap:5px; }
        .btn-danger:hover { background:#c62828; }
        .btn-warning { background:#ff9800; color:white; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:0.75rem; transition:all 0.3s; display:inline-flex; align-items:center; gap:5px; }
        .btn-warning:hover { background:#e65100; }
        .btn-info { background:#479ace; color:white; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:0.75rem; text-decoration:none; transition:all 0.3s; display:inline-flex; align-items:center; gap:5px; }
        .btn-info:hover { background:#2c7cb3; }
        .btn-success { background:#2196f3; color:white; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:0.75rem; text-decoration:none; transition:all 0.3s; display:inline-flex; align-items:center; gap:5px; }
        .btn-success:hover { background:#1565c0; }
        .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.7rem; font-weight:600; white-space:nowrap; }
        .status-badge.active { background:#e8f5e9; color:#4caf50; }
        .status-badge.completed { background:#e3f2fd; color:#2196f3; }
        .status-badge.cancelled { background:#ffebee; color:#f44336; }
        .form-row { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-bottom:20px; }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; margin-bottom:8px; color:#8e181e; font-weight:500; font-size:0.85rem; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:12px 15px; border:2px solid #e9edf2; border-radius:12px; font-size:0.9rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:#8e181e; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
        .modal-content { background:white; border-radius:16px; max-width:900px; width:90%; max-height:80vh; overflow-y:auto; }
        .modal-header { padding:20px; border-bottom:1px solid #e9edf2; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:white; z-index:10; }
        .modal-footer { padding:20px; border-top:1px solid #e9edf2; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white; }
        .close-modal { background:none; border:none; font-size:1.5rem; cursor:pointer; color:#999; }
        .close-modal:hover { color:#8e181e; }
        .error-message { background:#ffebee; color:#c62828; padding:12px; border-radius:12px; margin-bottom:20px; }
        .success-message { background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:12px; margin-bottom:20px; }
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); position:fixed; z-index:1000; }
            .sidebar.open { transform:translateX(0); }
            .main-content { margin-left:0; }
            .form-row { grid-template-columns:1fr; }
            .stats-grid { grid-template-columns:1fr; }
            .header-search { width:180px; }
        }
        @media print {
            .sidebar, .top-header, .btn-primary, .btn-danger, .btn-warning, .btn-success, .btn-info, .no-print { display:none !important; }
            .main-content { margin-left:0 !important; padding:0 !important; }
        }
    </style>
</head>
<body class="<?php echo (isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true') ? 'sidebar-collapsed' : ''; ?>">

<div class="dashboard-container">
    
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-header">
            <div style="display:flex; align-items:center;">
                <div class="sidebar-toggle-header" id="sidebarToggleBtn">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="globalSearch" placeholder="Search...">
                </div>
            </div>
            <div class="user-menu">
                <span class="user-name"><?php echo $_SESSION['full_name']; ?></span>
                <div class="user-avatar" onclick="toggleUserDropdown()">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <h4><?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>
                        <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                    <div class="dropdown-item" onclick="openProfileModal()">
                        <i class="fas fa-user-circle"></i> My Profile
                    </div>
                    <div class="dropdown-item logout" onclick="location.href='system.php?page=logout'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </div>
                </div>
            </div>
        </div>
        <div class="content-wrapper">