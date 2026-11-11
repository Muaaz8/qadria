<?php
$userId = getUserId();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if (isAdmin()) {
    if ($start_date && $end_date) {
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE DATE(job_date) BETWEEN ? AND ? ORDER BY id DESC");
        $stmt->execute([$start_date, $end_date]);
    } elseif ($search) {
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE job_number LIKE ? OR consignee LIKE ? OR bl_number LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM jobs ORDER BY id DESC");
    }
    $jobs = $stmt->fetchAll();
} else {
    $jobs = getUserVisibleJobs($pdo, $userId);
    if ($start_date && $end_date) {
        $filtered_jobs = [];
        foreach ($jobs as $job) if ($job['job_date'] >= $start_date && $job['job_date'] <= $end_date) $filtered_jobs[] = $job;
        $jobs = $filtered_jobs;
    }
    if ($search) {
        $filtered_jobs = [];
        foreach ($jobs as $job) if (stripos($job['job_number'], $search) !== false || stripos($job['consignee'], $search) !== false || stripos($job['bl_number'], $search) !== false) $filtered_jobs[] = $job;
        $jobs = $filtered_jobs;
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php if (isset($_SESSION['success'])): ?><div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])): ?><div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

<div class="dashboard-header">
    <div><h1 class="page-title">Jobs</h1><p class="page-subtitle">Manage all job orders</p></div>
    <div style="display:flex; gap:10px;"><a href="system.php?page=create-job" class="btn-primary"><i class="fas fa-plus"></i> New Job</a><span style="background:#e8f5e9; padding:5px 12px; border-radius:20px; font-size:12px;"><i class="fas fa-database"></i> Total: <?php echo count($jobs); ?> jobs</span></div>
</div>

<div class="date-filter" style="background:white; border-radius:12px; padding:15px 20px; margin-bottom:20px; display:flex; flex-wrap:wrap; align-items:flex-end; gap:15px; border:1px solid #e0e0e0;">
    <div class="date-filter-group" style="display:flex; flex-direction:column; gap:5px;"><label style="font-size:12px; color:#8e181e; font-weight:600;"><i class="fas fa-calendar-alt"></i> Start Date</label><input type="date" id="job_start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px;"></div>
    <div class="date-filter-group" style="display:flex; flex-direction:column; gap:5px;"><label style="font-size:12px; color:#8e181e; font-weight:600;"><i class="fas fa-calendar-alt"></i> End Date</label><input type="date" id="job_end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px;"></div>
    <div class="filter-actions" style="display:flex; gap:10px; margin-left:auto;"><button class="btn-primary" onclick="applyJobDateFilter()"><i class="fas fa-filter"></i> Apply Filter</button><button class="btn-danger" onclick="clearJobDateFilter()" style="background:#81c784;"><i class="fas fa-times"></i> Clear</button></div>
    <div class="filter-actions" style="display:flex; gap:10px;"><button class="btn-success" onclick="exportJobs('excel')" style="background:#2196f3;"><i class="fas fa-file-excel"></i> Excel</button><button class="btn-info" onclick="exportJobs('pdf')"><i class="fas fa-file-pdf"></i> PDF</button><button class="btn-primary" onclick="exportJobsFolder()"><i class="fas fa-folder-open"></i> Folder</button></div>
</div>

<div class="card"><div class="card-body" style="padding:0;">
    <div class="jobs-google-sheets-container" style="overflow-x:auto; border-radius:8px; border:1px solid #e0e0e0; background:white; max-height:calc(100vh - 300px); overflow-y:auto;">
        <table class="jobs-google-sheets-table" id="jobsTable" style="width:100%; border-collapse:collapse; font-family:'Segoe UI','Roboto','Arial',sans-serif; font-size:13px; background:white;">
            <thead>
                <tr class="titles-row" style="background-color:#f8f9fa; border-bottom:1px solid #e0e0e0;">
                    <th class="sortable" data-column="0" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">JOB #</th>
                    <th class="sortable" data-column="1" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">YEAR</th>
                    <th class="sortable" data-column="2" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">JOB CREATION DT</th>
                    <th class="sortable" data-column="3" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">ORIG DOCS RECEIVED</th>
                    <th class="sortable" data-column="4" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">BONDED CARRIER</th>
                    <th class="sortable" data-column="5" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">D/O COLLECTED</th>
                    <th class="sortable" data-column="6" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">CUSTOMER NAME</th>
                    <th class="sortable" data-column="7" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">CONSIGNEE</th>
                    <th class="sortable" data-column="8" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">BL #</th>
                    <th class="sortable" data-column="9" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">CONTAINER #</th>
                    <th class="sortable" data-column="10" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">SIZE</th>
                    <th class="sortable" data-column="11" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">VESSEL NAME</th>
                    <th class="sortable" data-column="12" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">VESSEL ARRIVAL</th>
                    <th class="sortable" data-column="13" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">POL</th>
                    <th class="sortable" data-column="14" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">POD</th>
                    <th class="sortable" data-column="15" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">KHI PORT</th>
                    <th class="sortable" data-column="16" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">DRY PORT</th>
                    <th class="sortable" data-column="17" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">SHIPPING LINE</th>
                    <th class="sortable" data-column="18" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">FREIGHT FORWARDER</th>
                    <th class="sortable" data-column="19" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">DEPOSITOR</th>
                    <th class="sortable" data-column="20" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">FREE TIME</th>
                    <th class="sortable" data-column="21" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">DETENTION TARIFF</th>
                    <th class="sortable" data-column="22" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">SOC</th>
                    <th class="sortable" data-column="23" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">MOBILE NO</th>
                    <th class="sortable" data-column="24" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">DESCRIPTION</th>
                    <th class="sortable" data-column="25" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">STATUS</th>
                    <th class="sortable" data-column="25" style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">created_by</th>
                    <th style="background-color:#3a4984; color:white; padding:12px 8px; white-space:nowrap;">ACTIONS</th>
                </tr>
                <tr class="filter-row" style="background-color:#f8f9fa;">
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="0" placeholder="Filter Job #" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="1" placeholder="Filter Year" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="2" placeholder="Filter Date" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="3" placeholder="Filter Date" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="4" placeholder="Filter Carrier" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="5" placeholder="Filter Date" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="6" placeholder="Filter Customer" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="7" placeholder="Filter Consignee" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="8" placeholder="Filter BL" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="9" placeholder="Filter Container" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="10" placeholder="Filter Size" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="11" placeholder="Filter Vessel" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="12" placeholder="Filter Date" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="13" placeholder="Filter POL" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="14" placeholder="Filter POD" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="15" placeholder="Filter Port" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="16" placeholder="Filter Dry Port" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="17" placeholder="Filter Line" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="18" placeholder="Filter Forwarder" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="19" placeholder="Filter Depositor" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="20" placeholder="Filter Time" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="21" placeholder="Filter Tariff" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="22" placeholder="Filter SOC" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="23" placeholder="Filter Mobile" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="24" placeholder="Filter Description" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="25" placeholder="Filter Status" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"><input type="text" class="col-filter" data-col="26" placeholder="Filter created_by" style="width:100%; padding:6px 8px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px 8px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($jobs as $job): $job_year = !empty($job['job_date']) ? date('Y', strtotime($job['job_date'])) : '-'; ?>
                <tr ondblclick="showJobWithBills(<?php echo $job['id']; ?>)" data-job-id="<?php echo $job['id']; ?>">
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><strong><?php echo htmlspecialchars($job['job_number'] ?? ''); ?></strong></td>
                    <td style="background-color:#f0f5ff; font-weight:bold; color:#8e181e; text-align:center; padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo $job_year; ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo !empty($job['job_date']) ? date('d-m-Y', strtotime($job['job_date'])) : '-'; ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo !empty($job['original_documents_date']) ? date('d-m-Y', strtotime($job['original_documents_date'])) : '-'; ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['bonded_carrier'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo !empty($job['do_collected_date']) ? date('d-m-Y', strtotime($job['do_collected_date'])) : '-'; ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['customer_name'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['consignee'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['bl_number'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['container_number'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['container_size'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['vessel_name'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo !empty($job['vessel_arrival_date']) ? date('d-m-Y', strtotime($job['vessel_arrival_date'])) : '-'; ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['port_of_loading'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['port_of_discharge'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['khi_port'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['dry_port'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['shipping_line'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['freight_forwarder_name'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['depositor'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['free_time'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['detention_tariff'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><span class="status-badge <?php echo ($job['soc'] ?? 'No') == 'Yes' ? 'active' : 'cancelled'; ?>"><?php echo $job['soc'] ?? 'No'; ?></span></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['mobile_no'] ?? '-'); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;" title="<?php echo htmlspecialchars($job['description'] ?? '-'); ?>"><?php echo htmlspecialchars(substr($job['description'] ?? '-', 0, 30)) . (strlen($job['description'] ?? '') > 30 ? '...' : ''); ?></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><span class="status-badge <?php echo $job['status'] ?? 'active'; ?>"><?php echo $job['status'] ?? 'active'; ?></span></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e8eaed; border-right:1px solid #e8eaed;"><?php echo htmlspecialchars($job['created_by'] ?? '-'); ?></td>
                    <td class="action-cell" style="padding:10px 8px; border-bottom:1px solid #e8eaed; white-space:nowrap; text-align:center;">
                        <button class="btn-info btn-sm" onclick="event.stopPropagation(); showJobWithBills(<?php echo $job['id']; ?>)" title="View Details" style="padding:4px 8px; background:#479ace; border:none; border-radius:4px; cursor:pointer; color:white;"><i class="fas fa-eye"></i></button>
                        <button class="btn-warning btn-sm" onclick="event.stopPropagation(); editJob(<?php echo $job['id']; ?>)" title="Edit Job" style="padding:4px 8px; background:#ff9800; border:none; border-radius:4px; cursor:pointer; color:white;"><i class="fas fa-edit"></i></button>
                        <button class="btn-danger btn-sm" onclick="event.stopPropagation(); deleteJob(<?php echo $job['id']; ?>)" title="Delete Job" style="padding:4px 8px; background:#dc3545; border:none; border-radius:4px; cursor:pointer; color:white;"><i class="fas fa-trash"></i></button>
                        <button class="btn-primary btn-sm" onclick="event.stopPropagation(); checkAndCreateBill(<?php echo $job['id']; ?>)" title="Create Bill" style="padding:4px 8px; background:#8e181e; border:none; border-radius:4px; cursor:pointer; color:white;"><i class="fas fa-file-invoice"></i></button>
                    </div>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($jobs)): ?>
                <tr><td colspan="27" style="text-align:center; padding:60px;"><i class="fas fa-folder-open" style="font-size:48px; color:#c8e6c9;"></i><p style="margin-top:10px; color:#666;">No jobs found. Click "New Job" to get started.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>

<div id="jobModal" class="modal"><div class="modal-content" style="max-width:800px;"><div class="modal-header"><h3><i class="fas fa-briefcase"></i> Job Details</h3><button class="close-modal" onclick="closeJobModal()">&times;</button></div><div class="modal-body" id="jobModalBody"></div><div class="modal-footer"><button class="btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button><button class="btn-primary" onclick="closeJobModal()">Close</button></div></div></div>

<div id="editJobModal" class="modal"><div class="modal-content" style="max-width:900px;"><div class="modal-header"><h3><i class="fas fa-edit"></i> Edit Job</h3><button class="close-modal" onclick="closeEditJobModal()">&times;</button></div><form method="POST" action="system.php?page=update-job" id="editJobForm"><div class="modal-body" id="editJobBody"></div><div class="modal-footer"><button type="button" class="btn-danger" onclick="closeEditJobModal()">Cancel</button><button type="submit" class="btn-primary">Update Job</button></div></form></div></div>

<div id="existingBillsModal" class="modal"><div class="modal-content" style="max-width:600px;"><div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Existing Bills Found</h3><button class="close-modal" onclick="closeExistingBillsModal()">&times;</button></div><div class="modal-body" id="existingBillsBody"></div><div class="modal-footer"><button class="btn-primary" onclick="proceedToCreateBill()">Create Another Bill</button><button class="btn-danger" onclick="closeExistingBillsModal()">Cancel</button></div></div></div>

<style>
.jobs-google-sheets-container::-webkit-scrollbar { width:8px; height:8px; }
.jobs-google-sheets-container::-webkit-scrollbar-track { background:#f1f1f1; border-radius:4px; }
.jobs-google-sheets-container::-webkit-scrollbar-thumb { background:#c1c1c1; border-radius:4px; }
.jobs-google-sheets-table th.sortable { cursor:pointer; position:relative; }
.jobs-google-sheets-table th.sortable::after { content:'⇅'; position:absolute; right:6px; top:50%; transform:translateY(-50%); opacity:0.6; font-size:11px; color:white; }
.jobs-google-sheets-table th.sortable.asc::after { content:'↑'; }
.jobs-google-sheets-table th.sortable.desc::after { content:'↓'; }
</style>

<script>
function applyJobDateFilter(){let s=document.getElementById('job_start_date').value;let e=document.getElementById('job_end_date').value;if(s&&e)window.location.href='system.php?page=jobs&start_date='+s+'&end_date='+e;else alert('Select both dates');}
function clearJobDateFilter(){window.location.href='system.php?page=jobs';}
function exportJobs(type){let s=document.getElementById('job_start_date').value;let e=document.getElementById('job_end_date').value;if(!s||!e){alert('Select date range first');return;}let f=document.createElement('form');f.method='POST';f.action='system.php?page=export-jobs-batch';let si=document.createElement('input');si.type='hidden';si.name='start_date';si.value=s;let ei=document.createElement('input');ei.type='hidden';ei.name='end_date';ei.value=e;let ti=document.createElement('input');ti.type='hidden';ti.name='export_type';ti.value=type;f.appendChild(si);f.appendChild(ei);f.appendChild(ti);document.body.appendChild(f);f.submit();document.body.removeChild(f);}
function exportJobsFolder(){let s=document.getElementById('job_start_date').value;let e=document.getElementById('job_end_date').value;if(!s||!e){alert('Select date range first');return;}let f=document.createElement('form');f.method='POST';f.action='system.php?page=export-jobs-folder';let si=document.createElement('input');si.type='hidden';si.name='start_date';si.value=s;let ei=document.createElement('input');ei.type='hidden';ei.name='end_date';ei.value=e;f.appendChild(si);f.appendChild(ei);document.body.appendChild(f);f.submit();document.body.removeChild(f);}
function initColumnFilters(){document.querySelectorAll('.col-filter').forEach(f=>{f.addEventListener('keyup',function(){let ci=parseInt(this.dataset.col),fv=this.value.toLowerCase(),rows=document.querySelectorAll('#jobsTable tbody tr');rows.forEach(row=>{if(row.querySelector('td[colspan]'))return;let cell=row.children[ci];if(cell)row.style.display=cell.textContent.toLowerCase().includes(fv)?'':(fv===''?'':'none');});});});}
function initTableSorting(){let headers=document.querySelectorAll('.jobs-google-sheets-table th.sortable'),currentSortColumn=null,currentSortDirection='asc';headers.forEach(header=>{header.addEventListener('click',function(e){if(e.target.classList&&e.target.classList.contains('col-filter'))return;let column=parseInt(this.dataset.column),table=this.closest('.jobs-google-sheets-table'),tbody=table.querySelector('tbody'),rows=Array.from(tbody.querySelectorAll('tr'));if(rows.length===1&&rows[0].querySelector('td[colspan]'))return;headers.forEach(h=>h.classList.remove('asc','desc'));if(currentSortColumn===column)currentSortDirection=currentSortDirection==='asc'?'desc':'asc';else{currentSortColumn=column;currentSortDirection='asc';}this.classList.add(currentSortDirection);rows.sort((a,b)=>{let av=a.children[column]?a.children[column].innerText.trim():'',bv=b.children[column]?b.children[column].innerText.trim():'';let an=parseFloat(av.replace(/[^0-9.-]/g,'')),bn=parseFloat(bv.replace(/[^0-9.-]/g,''));if(!isNaN(an)&&!isNaN(bn))return currentSortDirection==='asc'?an-bn:bn-an;av=av.toLowerCase();bv=bv.toLowerCase();return currentSortDirection==='asc'?av.localeCompare(bv):bv.localeCompare(av);});rows.forEach(row=>tbody.appendChild(row));});});}
function showJobWithBills(jobId){fetch('system.php?page=get-job&id='+jobId).then(r=>r.json()).then(job=>{fetch('system.php?page=get-job-bills&job_id='+jobId).then(r=>r.json()).then(data=>{let html='<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:15px;">';html+='<div><strong>Job Number:</strong><br>'+(job.job_number||'-')+'</div>';html+='<div><strong>Year:</strong><br>'+(job.job_date?new Date(job.job_date).getFullYear():'-')+'</div>';html+='<div><strong>Original Documents Received:</strong><br>'+(job.original_documents_date||'-')+'</div>';html+='<div><strong>Job Creation Dt:</strong><br>'+(job.job_date||'-')+'</div>';html+='<div><strong>Bonded Carrier Name:</strong><br>'+(job.bonded_carrier||'-')+'</div>';html+='<div><strong>D/O Collected on:</strong><br>'+(job.do_collected_date||'-')+'</div>';html+='<div><strong>Customer Name:</strong><br>'+(job.customer_name||'-')+'</div>';html+='<div><strong>Consignee:</strong><br>'+(job.consignee||'-')+'</div>';html+='<div><strong>BL Number:</strong><br>'+(job.bl_number||'-')+'</div>';html+='<div><strong>Container Number:</strong><br>'+(job.container_number||'-')+'</div>';html+='<div><strong>Container Size:</strong><br>'+(job.container_size||'-')+'</div>';html+='<div><strong>KHI Port:</strong><br>'+(job.khi_port||'-')+'</div>';html+='<div><strong>Dry Port:</strong><br>'+(job.dry_port||'-')+'</div>';html+='<div><strong>Vessel Name:</strong><br>'+(job.vessel_name||'-')+'</div>';html+='<div><strong>Vessel Arrival Date:</strong><br>'+(job.vessel_arrival_date||'-')+'</div>';html+='<div><strong>Shipping Line:</strong><br>'+(job.shipping_line||'-')+'</div>';html+='<div><strong>Free Time:</strong><br>'+(job.free_time||'-')+'</div>';html+='<div><strong>SOC:</strong><br>'+(job.soc||'No')+'</div>';html+='<div><strong>Description:</strong><br>'+(job.description||'-')+'</div>';html+='<div><strong>Remarks:</strong><br>'+(job.remarks||'-')+'</div></div>';html+='<div class="bill-summary-section" style="margin-top:20px; padding-top:15px; border-top:2px solid #e9edf2;"><div class="bill-summary-title"><i class="fas fa-file-invoice-dollar"></i> Bills Summary</div>';if(data.bills&&data.bills.length>0){html+='<table style="width:100%; border-collapse:collapse;"><thead><tr style="background:#f8fafd;"><th>Bill Number</th><th>Type</th><th>Total Amount</th><th>Balance</th><th>Date</th><th>Action</th></tr></thead><tbody>';for(var i=0;i<data.bills.length;i++){var b=data.bills[i];html+='tr><td><strong>'+b.bill_number+'</strong></td><td>'+b.bill_type+(b.is_revised?' (Revised)':'')+'</td><td>PKR '+parseFloat(b.total_amount).toFixed(2)+'</td><td>PKR '+parseFloat(b.balance_amount).toFixed(2)+'</td><td>'+new Date(b.created_at).toLocaleDateString()+'</td><td><a href="system.php?page=view-bill&id='+b.id+'">View</a></td></tr>';}html+='</tbody></table>';}else{html+='<div style="text-align:center; padding:20px;">No bills created yet.</div><div style="margin-top:10px;"><a href="system.php?page=create-bill&job_id='+jobId+'" class="btn-primary" style="padding:6px 12px;">Create First Bill</a></div>';}html+='</div>';document.getElementById('jobModalBody').innerHTML=html;document.getElementById('jobModal').style.display='flex';});});}
function editJob(id){fetch('system.php?page=get-job&id='+id).then(r=>r.json()).then(job=>{let html='<input type="hidden" name="id" value="'+job.id+'">';html+='<div class="form-row"><div class="form-group"><label>Job Number</label><input type="text" value="'+job.job_number+'" readonly class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Original Documents Received</label><input type="date" name="original_documents_date" value="'+(job.original_documents_date||'')+'" class="form-control"></div><div class="form-group"><label>Job Creation Dt</label><input type="date" name="job_date" value="'+(job.job_date||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Bonded Carrier Name</label><input type="text" name="bonded_carrier" value="'+(job.bonded_carrier||'')+'" class="form-control"></div><div class="form-group"><label>D/O Collected on</label><input type="date" name="do_collected_date" value="'+(job.do_collected_date||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Customer Name</label><input type="text" name="customer_name" value="'+(job.customer_name||'')+'" class="form-control"></div><div class="form-group"><label>Consignee</label><input type="text" name="consignee" value="'+(job.consignee||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>BL Number</label><input type="text" name="bl_number" value="'+(job.bl_number||'')+'" class="form-control"></div><div class="form-group"><label>Container Number</label><input type="text" name="container_number" value="'+(job.container_number||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Container Size</label><input type="text" name="container_size" value="'+(job.container_size||'')+'" class="form-control"></div><div class="form-group"><label>KHI Port</label><input type="text" name="khi_port" value="'+(job.khi_port||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Dry Port</label><input type="text" name="dry_port" value="'+(job.dry_port||'')+'" class="form-control"></div><div class="form-group"><label>Port of Loading</label><input type="text" name="port_of_loading" value="'+(job.port_of_loading||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Port of Discharge</label><input type="text" name="port_of_discharge" value="'+(job.port_of_discharge||'')+'" class="form-control"></div><div class="form-group"><label>Vessel Name</label><input type="text" name="vessel_name" value="'+(job.vessel_name||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Vessel Arrival Date</label><input type="date" name="vessel_arrival_date" value="'+(job.vessel_arrival_date||'')+'" class="form-control"></div><div class="form-group"><label>Shipping Line</label><input type="text" name="shipping_line" value="'+(job.shipping_line||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Freight Forwarder Name</label><input type="text" name="freight_forwarder_name" value="'+(job.freight_forwarder_name||'')+'" class="form-control"></div><div class="form-group"><label>Depositor</label><input type="text" name="depositor" value="'+(job.depositor||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>Free Time</label><input type="text" name="free_time" value="'+(job.free_time||'')+'" class="form-control"></div><div class="form-group"><label>Detention Tariff</label><input type="text" name="detention_tariff" value="'+(job.detention_tariff||'')+'" class="form-control"></div></div>';html+='<div class="form-row"><div class="form-group"><label>SOC</label><select name="soc"><option value="No">No</option><option value="Yes" '+(job.soc=='Yes'?'selected':'')+'>Yes</option></select></div><div class="form-group"><label>Mobile No</label><input type="text" name="mobile_no" value="'+(job.mobile_no||'')+'" class="form-control"></div></div>';html+='<div class="form-group"><label>Description</label><textarea name="description" rows="2" class="form-control">'+(job.description||'')+'</textarea></div>';html+='<div class="form-group"><label>Remarks</label><textarea name="remarks" rows="2" class="form-control">'+(job.remarks||'')+'</textarea></div>';html+='<div class="form-group"><label>Status</label><select name="status"><option value="active" '+(job.status=='active'?'selected':'')+'>Active</option><option value="completed" '+(job.status=='completed'?'selected':'')+'>Completed</option><option value="cancelled" '+(job.status=='cancelled'?'selected':'')+'>Cancelled</option></select></div>';document.getElementById('editJobBody').innerHTML=html;document.getElementById('editJobModal').style.display='flex';});}
function deleteJob(id){if(confirm('Delete this job? All associated bills will also be deleted!')){window.location.href='system.php?page=delete-job&id='+id;}}
var pendingJobId=null;
function checkAndCreateBill(jobId){fetch('system.php?page=check-existing-bills&job_id='+jobId).then(r=>r.json()).then(data=>{if(data.has_bills){pendingJobId=jobId;let html='<p>This job has existing bills.</p><ul>';for(var i=0;i<data.bills.length;i++)html+='<li>'+data.bills[i].bill_number+' - '+data.bills[i].bill_type+'</li>';html+='</ul><p>Create another bill?</p>';document.getElementById('existingBillsBody').innerHTML=html;document.getElementById('existingBillsModal').style.display='flex';}else{window.location.href='system.php?page=create-bill&job_id='+jobId;}});}
function proceedToCreateBill(){if(pendingJobId)window.location.href='system.php?page=create-bill&job_id='+pendingJobId;closeExistingBillsModal();}
function closeJobModal(){document.getElementById('jobModal').style.display='none';}
function closeEditJobModal(){document.getElementById('editJobModal').style.display='none';}
function closeExistingBillsModal(){document.getElementById('existingBillsModal').style.display='none';pendingJobId=null;}
document.addEventListener('DOMContentLoaded',function(){initTableSorting();initColumnFilters();});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>