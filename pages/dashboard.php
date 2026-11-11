<?php
$userId = getUserId();

if (isAdmin()) {
    $totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $activeJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
    $totalBills = $pdo->query("SELECT COUNT(*) FROM bills")->fetchColumn();
    $totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM bills")->fetchColumn();
    $recentJobs = $pdo->query("SELECT * FROM jobs ORDER BY id DESC LIMIT 5")->fetchAll();
    $recentBills = $pdo->query("SELECT b.*, j.consignee, j.job_number FROM bills b LEFT JOIN jobs j ON b.job_id = j.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
} else {
    $jobs = getUserVisibleJobs($pdo, $userId);
    $totalJobs = count($jobs);
    $activeJobs = 0;
    foreach ($jobs as $job) if ($job['status'] == 'active') $activeJobs++;
    $bills = getUserVisibleBills($pdo, $userId);
    $totalBills = count($bills);
    $totalRevenue = 0;
    foreach ($bills as $bill) $totalRevenue += floatval($bill['total_amount'] ?? 0);
    $recentJobs = array_slice($jobs, 0, 5);
    $recentBills = array_slice($bills, 0, 5);
}

$stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%M') as month, SUM(total_amount) as total FROM bills WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)");
$stmt->execute();
$monthlyRevenue = $stmt->fetchAll();
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>
    <a href="system.php?page=create-job" class="btn-primary"><i class="fas fa-plus"></i> New Job</a>
</div>

<div class="stats-grid">
    <div class="stat-card" onclick="location.href='system.php?page=jobs'">
        <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
        <div class="stat-info"><h3><?php echo number_format($totalJobs); ?></h3><p>Total Jobs</p></div>
    </div>
    <div class="stat-card" onclick="location.href='system.php?page=bills'">
        <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info"><h3><?php echo number_format($totalBills); ?></h3><p>Total Bills</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info"><h3><?php echo formatCurrency($totalRevenue); ?></h3><p>Total Revenue</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
        <div class="stat-info"><h3><?php echo number_format($activeJobs); ?></h3><p>Active Jobs</p></div>
    </div>
</div>

<div class="quick-actions" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:15px; margin-bottom:25px;">
    <div class="quick-action-card" style="background:white; border-radius:12px; padding:15px; text-align:center; cursor:pointer; border:1px solid #e9edf2;" onclick="location.href='system.php?page=create-job'">
        <div class="quick-action-icon" style="font-size:1.8rem; color:#8e181e; margin-bottom:10px;"><i class="fas fa-plus-circle"></i></div>
        <div><strong>New Job</strong></div>
    </div>
    <div class="quick-action-card" style="background:white; border-radius:12px; padding:15px; text-align:center; cursor:pointer; border:1px solid #e9edf2;" onclick="location.href='system.php?page=bills'">
        <div class="quick-action-icon" style="font-size:1.8rem; color:#8e181e; margin-bottom:10px;"><i class="fas fa-file-invoice"></i></div>
        <div><strong>View Bills</strong></div>
    </div>
    <div class="quick-action-card" style="background:white; border-radius:12px; padding:15px; text-align:center; cursor:pointer; border:1px solid #e9edf2;" onclick="location.href='system.php?page=invoices'">
        <div class="quick-action-icon" style="font-size:1.8rem; color:#8e181e; margin-bottom:10px;"><i class="fas fa-folder-open"></i></div>
        <div><strong>Invoices</strong></div>
    </div>
    <div class="quick-action-card" style="background:white; border-radius:12px; padding:15px; text-align:center; cursor:pointer; border:1px solid #e9edf2;" onclick="location.href='system.php?page=karachi-clearance'">
        <div class="quick-action-icon" style="font-size:1.8rem; color:#8e181e; margin-bottom:10px;"><i class="fas fa-ship"></i></div>
        <div><strong>Karachi Clearance</strong></div>
    </div>
    <div class="quick-action-card" style="background:white; border-radius:12px; padding:15px; text-align:center; cursor:pointer; border:1px solid #e9edf2;" onclick="location.href='system.php?page=loading-files'">
        <div class="quick-action-icon" style="font-size:1.8rem; color:#8e181e; margin-bottom:10px;"><i class="fas fa-truck-loading"></i></div>
        <div><strong>Loading File</strong></div>
    </div>
</div>

<?php if(!empty($monthlyRevenue)): ?>
<div class="chart-container" style="background:white; border-radius:16px; padding:20px; margin-bottom:25px;">
    <h3 style="margin-bottom:15px; color:#3a4984;"><i class="fas fa-chart-bar"></i> Monthly Revenue (<?php echo date('Y'); ?>)</h3>
    <?php $maxRevenue = 0; foreach($monthlyRevenue as $mr) if($mr['total'] > $maxRevenue) $maxRevenue = $mr['total']; $maxRevenue = $maxRevenue > 0 ? $maxRevenue : 1; ?>
    <?php foreach($monthlyRevenue as $mr): $percentage = ($mr['total'] / $maxRevenue) * 100; ?>
    <div class="chart-bar" style="display:flex; align-items:center; margin-bottom:10px;">
        <div class="chart-label" style="width:100px; font-size:12px; color:#666;"><?php echo substr($mr['month'], 0, 3); ?></div>
        <div class="chart-bar-fill" style="height:30px; background:#8e181e; border-radius:5px; width: <?php echo max(5, $percentage); ?>%; display:flex; align-items:center; justify-content:flex-end; padding-right:10px; color:white; font-size:11px;"><?php echo formatCurrency($mr['total']); ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3><i class="fas fa-clock"></i> Recent Jobs</h3><a href="system.php?page=jobs" style="color:#8e181e; text-decoration:none;">View All →</a></div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Job #</th><th>Consignee</th><th>Customer Name</th><th>BL #</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php if(empty($recentJobs)): ?>
                    <tr><td colspan="7" style="text-align:center;">No jobs found. <a href="system.php?page=create-job" style="color:#8e181e;">Create your first job</a></td></tr>
                <?php else: foreach($recentJobs as $job): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($job['job_number'] ?? ''); ?></strong></td>
                        <td><?php echo htmlspecialchars($job['consignee'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($job['customer_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($job['bl_number'] ?? '-'); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($job['created_at'] ?? 'now')); ?></td>
                        <td><span class="status-badge <?php echo $job['status'] ?? 'active'; ?>"><?php echo $job['status'] ?? 'active'; ?></span></td>
                        <td class="action-buttons"><button class="btn-primary" style="padding:4px 10px;" onclick="location.href='system.php?page=create-bill&job_id=<?php echo $job['id']; ?>'"><i class="fas fa-file-invoice"></i> Create Bill</button></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3><i class="fas fa-receipt"></i> Recent Bills</h3><a href="system.php?page=bills" style="color:#8e181e; text-decoration:none;">View All →</a></div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Bill #</th><th>Job #</th><th>Consignee</th><th>Type</th><th>Total Amount</th><th>Balance</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php if(empty($recentBills)): ?>
                    <tr><td colspan="8" style="text-align:center;">No bills found.</td></tr>
                <?php else: foreach($recentBills as $bill): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($bill['bill_number'] ?? ''); ?></strong></td>
                        <td><?php echo htmlspecialchars($bill['job_number'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($bill['consignee'] ?? '-'); ?></td>
                        <td><?php echo ucfirst($bill['bill_type'] ?? ''); ?></td>
                        <td><?php echo formatCurrency($bill['total_amount'] ?? 0); ?></td>
                        <td><?php echo formatCurrency($bill['balance_amount'] ?? 0); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($bill['created_at'] ?? 'now')); ?></td>
                        <td class="action-buttons"><button class="btn-info" onclick="location.href='system.php?page=view-bill&id=<?php echo $bill['id']; ?>'"><i class="fas fa-eye"></i> View</button></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="profileModal" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header"><h3><i class="fas fa-user-circle"></i> My Profile</h3><button class="close-modal" onclick="closeProfileModal()">&times;</button></div>
        <div class="modal-body">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:80px; height:80px; background:#8e181e; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-user" style="font-size:40px; color:white;"></i></div>
                <h4><?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>
                <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><span class="status-badge"><?php echo ucfirst($_SESSION['role']); ?></span></p>
            </div>
            <form method="POST" action="system.php?page=update-profile">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>"></div>
                <button type="submit" class="btn-primary" style="width:100%;">Update Profile</button>
            </form>
            <hr>
            <form method="POST" action="system.php?page=change-password">
                <h4>Change Password</h4>
                <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                <div class="form-group"><label>New Password</label><input type="password" name="new_password" class="form-control" required></div>
                <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                <button type="submit" class="btn-primary" style="width:100%; background:#479ace;">Change Password</button>
            </form>
        </div>
        <div class="modal-footer"><button class="btn-danger" onclick="closeProfileModal()">Close</button></div>
    </div>
</div>

<script>
function toggleUserDropdown() { document.getElementById('userDropdown').classList.toggle('show'); }
function openProfileModal() { document.getElementById('profileModal').style.display = 'flex'; document.getElementById('userDropdown').classList.remove('show'); }
function closeProfileModal() { document.getElementById('profileModal').style.display = 'none'; }
window.onclick = function(e) { if(e.target.classList && e.target.classList.contains('modal')) e.target.style.display = 'none'; if(!e.target.closest('.user-avatar') && !e.target.closest('.user-dropdown')) document.getElementById('userDropdown').classList.remove('show'); }
(function() { const sidebar = document.querySelector('.sidebar'); const body = document.body; const toggleBtn = document.getElementById('sidebarToggleBtn'); if (!sidebar || !toggleBtn) return; function toggleSidebar() { sidebar.classList.toggle('collapsed'); if (sidebar.classList.contains('collapsed')) { body.classList.add('sidebar-collapsed'); document.cookie = "sidebar_collapsed=true; path=/"; } else { body.classList.remove('sidebar-collapsed'); document.cookie = "sidebar_collapsed=false; path=/"; } } toggleBtn.addEventListener('click', toggleSidebar); if (document.cookie.indexOf('sidebar_collapsed=true') !== -1) { sidebar.classList.add('collapsed'); body.classList.add('sidebar-collapsed'); } })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>