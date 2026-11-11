<?php
$userId = getUserId();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$grouped_loading_files = getLoadingFilesGroupedByDate($pdo, $search, $start_date, $end_date);
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title"><i class="fas fa-truck-loading"></i> Loading File</h1>
        <p class="page-subtitle">Manage loading file records</p>
    </div>
    <button class="btn-primary" onclick="toggleAddTable()" id="addNewRowBtn"><i class="fas fa-plus-circle"></i> Add New Row</button>
</div>

<!-- DATE RANGE FILTER -->
<div class="date-filter" style="margin-bottom: 20px;">
    <div class="date-filter-group"><label><i class="fas fa-calendar-alt"></i> Loading Date Start</label><input type="date" id="loading_start_date" value="<?php echo htmlspecialchars($start_date); ?>"></div>
    <div class="date-filter-group"><label><i class="fas fa-calendar-alt"></i> Loading Date End</label><input type="date" id="loading_end_date" value="<?php echo htmlspecialchars($end_date); ?>"></div>
    <div class="filter-actions"><button class="btn-primary" onclick="applyLoadingDateFilter()"><i class="fas fa-filter"></i> Apply Filter</button><button class="btn-danger" onclick="clearLoadingDateFilter()" style="background:#81c784;"><i class="fas fa-times"></i> Clear</button></div>
</div>

<!-- ADD NEW ROW CONTAINER (IN-TABLE ADD - NO MODAL) -->
<div id="addNewRowContainer" style="display:none; margin-bottom:25px;">
    <div class="card" style="border:2px solid #8e181e;">
        <div class="card-header" style="background:#3a4984; color:white;">
            <h3 style="color:white;"><i class="fas fa-plus-circle"></i> Add New Loading Record</h3>
            <button class="btn-danger" onclick="toggleAddTable()" style="background:#fff; color:#3a4984; padding:5px 10px;"><i class="fas fa-times"></i> Close</button>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="jobs-google-sheets-container" style="overflow-x: auto; border-radius: 8px; border: 1px solid #e0e0e0; background: white;">
                <form id="addLoadingForm" onsubmit="event.preventDefault(); saveNewRow();">
                    <table class="jobs-google-sheets-table" style="width:100%; border-collapse: collapse; font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif; font-size: 13px; background: white;">
                        <thead>
                            <tr style="background-color: #3a4984;">
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">JOB #</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">CLEARING AGENT</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">BONDED CARRIER</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">IMPORTER</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">DRY PORT</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">SIZE</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">CONTAINER NO</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">SHIPPING LINE</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">FREE DAYS</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">VESSEL ARRIVAL</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">LOADING DATE</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">FILE CREATION</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">LAHORE ARRIVAL</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">STATUS</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">VEHICLES NO</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">TRUCKER CELL</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap; border-right: 1px solid rgba(255,255,255,0.2);">SOC</th>
                                <th style="background-color: #3a4984; color: white; padding: 12px 8px; font-weight: 600; font-size: 13px; white-space: nowrap;">ACTIONS</th>
                             </tr>
                        </thead>
                        <tbody>
                             <tr>
                                <td style="padding: 10px 8px; border-bottom: 1px solid #e8eaed; border-right: 1px solid #e8eaed;">
                                    <select id="newJobSelect" style="width:100%; padding:8px; border:1px solid #c8e6c9; border-radius:6px;" required>
                                        <option value="">-- Select Job --</option>
                                        <?php $jobsList = $pdo->query("SELECT id, job_number, consignee, customer_name, bonded_carrier, dry_port, container_size, container_number, shipping_line, vessel_arrival_date, soc FROM jobs ORDER BY job_number DESC")->fetchAll(); ?>
                                        <?php foreach($jobsList as $job): ?>
                                            <option value="<?php echo $job['id']; ?>" 
                                                data-job-number="<?php echo htmlspecialchars($job['job_number']); ?>"
                                                data-customer-name="<?php echo htmlspecialchars($job['customer_name']??''); ?>"
                                                data-bonded-carrier="<?php echo htmlspecialchars($job['bonded_carrier']??''); ?>"
                                                data-consignee="<?php echo htmlspecialchars($job['consignee']??''); ?>"
                                                data-dry-port="<?php echo htmlspecialchars($job['dry_port']??''); ?>"
                                                data-container-size="<?php echo htmlspecialchars($job['container_size']??''); ?>"
                                                data-container-number="<?php echo htmlspecialchars($job['container_number']??''); ?>"
                                                data-shipping-line="<?php echo htmlspecialchars($job['shipping_line']??''); ?>"
                                                data-vessel-arrival="<?php echo htmlspecialchars($job['vessel_arrival_date']??''); ?>"
                                                data-soc="<?php echo htmlspecialchars($job['soc']??'No'); ?>">
                                                <?php echo htmlspecialchars($job['job_number']); ?> - <?php echo htmlspecialchars($job['consignee']??$job['customer_name']??''); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="padding: 10px 8px;"><input type="text" id="newClearingAgent" placeholder="Clearing Agent" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newBondedCarrier" placeholder="Bonded Carrier" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newImporter" placeholder="IMPORTER" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newDryPort" placeholder="Dry Port" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newContainerSize" placeholder="Size" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newContainerNo" placeholder="Container No" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newShippingLine" placeholder="Shipping Line" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newFreeDays" placeholder="Free Days" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="date" id="newVesselArrival" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="date" id="newLoadingDate" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="datetime-local" id="newCreatedAt" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="date" id="newLahoreArrival" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;">
                                    <select id="newStatus" style="width:100%; padding:8px;">
                                        <option value="Pending">Pending</option><option value="Loaded">Loaded</option>
                                        <option value="In Transit">In Transit</option><option value="Delivered">Delivered</option>
                                    </select>
                                </td>
                                <td style="padding: 10px 8px;"><input type="text" id="newVehiclesNo" placeholder="Vehicles No" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;"><input type="text" id="newTruckerCell" placeholder="Trucker Cell" style="width:100%; padding:8px;"></td>
                                <td style="padding: 10px 8px;">
                                    <select id="newSoc" style="width:100%; padding:8px;">
                                        <option value="No">No</option><option value="Yes">Yes</option>
                                    </select>
                                </td>
                                <td style="padding: 10px 8px; text-align:center;">
                                    <button type="submit" class="btn-primary save-new-btn" style="background:#8e181e; padding:6px 12px;"><i class="fas fa-save"></i> Save</button>
                                </td>
                             </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- DATE-WISE GROUPED TABLES - GOOGLE SHEETS STYLE -->
<div id="groupedTablesContainer">
    <?php if(empty($grouped_loading_files)): ?>
        <div class="card"><div class="card-body" style="text-align:center; padding:60px;"><i class="fas fa-folder-open" style="font-size:48px; color:#c8e6c9;"></i><p>No loading records found.</p></div></div>
    <?php else: foreach($grouped_loading_files as $date => $records): usort($records,function($a,$b){return $b['id']-$a['id'];}); $file_creation_date = !empty($records[0]['created_at']) ? date('d-m-Y', strtotime($records[0]['created_at'])) : '-'; ?>
    <div class="card" style="margin-bottom:25px;">
        <div class="card-header" style="background:#8e181e; color:white;">
            <h3 style="color:white;"><i class="fas fa-calendar-day"></i> Loading Date: <?php echo date('d-m-Y', strtotime($date)); ?>
                <span style="font-size:12px; background:rgba(255,255,255,0.2); padding:3px 10px; border-radius:20px; margin-left:10px;"><i class="fas fa-list"></i> <?php echo count($records); ?> Records</span>
                <span style="margin-left:15px;"><i class="fas fa-calendar-alt"></i> File Creation: <?php echo $file_creation_date; ?></span>
            </h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="jobs-google-sheets-container" style="overflow-x: auto; border-radius: 8px; background: white;">
                <table class="jobs-google-sheets-table" id="loadingTable_<?php echo str_replace('-', '_', $date); ?>" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #3a4984;">
                            <th class="sortable" data-column="0">JOB #</th>
                            <th class="sortable" data-column="1">CLEARING AGENT</th>
                            <th class="sortable" data-column="2">BONDED CARRIER</th>
                            <th class="sortable" data-column="3">IMPORTER</th>
                            <th class="sortable" data-column="4">DRY PORT</th>
                            <th class="sortable" data-column="5">SIZE</th>
                            <th class="sortable" data-column="6">CONTAINER NO</th>
                            <th class="sortable" data-column="7">SHIPPING LINE</th>
                            <th class="sortable" data-column="8">FREE DAYS</th>
                            <th class="sortable" data-column="9">VESSEL ARRIVAL</th>
                            <th class="sortable" data-column="10">LOADING DATE</th>
                            <th class="sortable" data-column="11">FILE CREATION</th>
                            <th class="sortable" data-column="12">LAHORE ARRIVAL</th>
                            <th class="sortable" data-column="13">STATUS</th>
                            <th class="sortable" data-column="14">VEHICLES NO</th>
                            <th class="sortable" data-column="15">TRUCKER CELL</th>
                            <th class="sortable" data-column="16">SOC</th>
                            <th>ACTIONS</th>
                         </tr>
                        <tr class="filter-row" style="background-color: #f8f9fa;">
                            <th><input type="text" class="col-filter" data-col="0" placeholder="Filter Job #" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="1" placeholder="Filter Agent" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="2" placeholder="Filter Carrier" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="3" placeholder="Filter Importer" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="4" placeholder="Filter Port" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="5" placeholder="Filter Size" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="6" placeholder="Filter Container" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="7" placeholder="Filter Line" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="8" placeholder="Filter Days" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="9" placeholder="Filter Arrival" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="10" placeholder="Filter Loading" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="11" placeholder="Filter Creation" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="12" placeholder="Filter Lahore" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="13" placeholder="Filter Status" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="14" placeholder="Filter Vehicles" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="15" placeholder="Filter Cell" style="width:100%; padding:6px;"></th>
                            <th><input type="text" class="col-filter" data-col="16" placeholder="Filter SOC" style="width:100%; padding:6px;"></th>
                            <th></th>
                         </tr>
                    </thead>
                    <tbody>
                        <?php foreach($records as $record): ?>
                        <tr data-id="<?php echo $record['id']; ?>">
                            <td><strong><?php echo htmlspecialchars($record['job_number_ref'] ?? $record['job_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($record['clearing_agent'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['bonded_carrier'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['importer'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['dry_port'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['container_size'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['container_no'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['shipping_line'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['free_days'] ?? '-'); ?></td>
                            <td><?php echo !empty($record['vessel_arrival']) ? date('d-m-Y', strtotime($record['vessel_arrival'])) : '-'; ?></td>
                            <td><?php echo !empty($record['loading_date']) ? date('d-m-Y', strtotime($record['loading_date'])) : '-'; ?></td>
                            <td><?php echo !empty($record['created_at']) ? date('d-m-Y H:i', strtotime($record['created_at'])) : '-'; ?></td>
                            <td><?php echo !empty($record['lahore_arrival']) ? date('d-m-Y', strtotime($record['lahore_arrival'])) : '-'; ?></td>
                            <td><span class="loading-status-badge <?php echo match($record['status']??'Pending'){'Loaded'=>'loaded','In Transit'=>'in-transit','Delivered'=>'delivered',default=>'pending'}; ?>"><?php echo $record['status']??'Pending'; ?></span></td>
                            <td><?php echo htmlspecialchars($record['vehicles_no'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['trucker_cell'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo ($record['soc']??'No')=='Yes'?'active':'cancelled'; ?>"><?php echo $record['soc']??'No'; ?></span></td>
                            <td class="action-cell"><button class="btn-warning btn-sm" onclick="editLoadingRow(<?php echo $record['id']; ?>)" title="Edit"><i class="fas fa-edit"></i></button><button class="btn-danger btn-sm" onclick="deleteLoading(<?php echo $record['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- Edit Modal -->
<div id="editLoadingModal" class="modal"><div class="modal-content" style="max-width:900px;"><div class="modal-header"><h3>Edit Loading Record</h3><button class="close-modal" onclick="closeEditModal()">&times;</button></div><form method="POST" action="system.php?page=update-loading"><input type="hidden" name="id" id="edit_id"><div class="modal-body"><div class="form-row"><div class="form-group"><label>Job #</label><select name="job_id" id="edit_job_id" class="form-control" required><option value="">Select Job</option><?php foreach($jobsList as $job): ?><option value="<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['job_number']); ?> - <?php echo htmlspecialchars($job['consignee']??$job['customer_name']??''); ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Clearing Agent</label><input type="text" name="clearing_agent" id="edit_clearing_agent" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Bonded Carrier</label><input type="text" name="bonded_carrier" id="edit_bonded_carrier" class="form-control"></div><div class="form-group"><label>Importer</label><input type="text" name="importer" id="edit_importer" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Dry Port</label><input type="text" name="dry_port" id="edit_dry_port" class="form-control"></div><div class="form-group"><label>Container Size</label><input type="text" name="container_size" id="edit_container_size" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Container No</label><input type="text" name="container_no" id="edit_container_no" class="form-control"></div><div class="form-group"><label>Shipping Line</label><input type="text" name="shipping_line" id="edit_shipping_line" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Free Days</label><input type="text" name="free_days" id="edit_free_days" class="form-control"></div><div class="form-group"><label>Vessel Arrival</label><input type="date" name="vessel_arrival" id="edit_vessel_arrival" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Loading Date</label><input type="date" name="loading_date" id="edit_loading_date" class="form-control"></div><div class="form-group"><label>Lahore Arrival</label><input type="date" name="lahore_arrival" id="edit_lahore_arrival" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Status</label><select name="status" id="edit_status" class="form-control"><option value="Pending">Pending</option><option value="Loaded">Loaded</option><option value="In Transit">In Transit</option><option value="Delivered">Delivered</option></select></div><div class="form-group"><label>Vehicles No</label><input type="text" name="vehicles_no" id="edit_vehicles_no" class="form-control"></div></div><div class="form-row"><div class="form-group"><label>Trucker Cell</label><input type="text" name="trucker_cell" id="edit_trucker_cell" class="form-control"></div><div class="form-group"><label>SOC</label><select name="soc" id="edit_soc" class="form-control"><option value="No">No</option><option value="Yes">Yes</option></select></div></div></div><div class="modal-footer"><button type="button" class="btn-danger" onclick="closeEditModal()">Cancel</button><button type="submit" class="btn-primary">Update Record</button></div></form></div></div>

<style>
.loading-status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.loading-status-badge.pending { background: #fff3e0; color: #ff9800; }
.loading-status-badge.loaded { background: #e8f5e9; color: #4caf50; }
.loading-status-badge.in-transit { background: #e3f2fd; color: #2196f3; }
.loading-status-badge.delivered { background: #e8eaf6; color: #3f51b5; }
.jobs-google-sheets-table th.sortable { cursor: pointer; position: relative; }
.jobs-google-sheets-table th.sortable::after { content: '⇅'; position: absolute; right: 6px; top: 50%; transform: translateY(-50%); opacity: 0.6; font-size: 11px; color: white; }
.jobs-google-sheets-table th.sortable.asc::after { content: '↑'; }
.jobs-google-sheets-table th.sortable.desc::after { content: '↓'; }
</style>

<script>
document.getElementById('newJobSelect')?.addEventListener('change',function(){var opt=this.options[this.selectedIndex];if(opt&&opt.value){document.getElementById('newClearingAgent').value=opt.getAttribute('data-customer-name')||'';document.getElementById('newBondedCarrier').value=opt.getAttribute('data-bonded-carrier')||'';document.getElementById('newImporter').value=opt.getAttribute('data-consignee')||'';document.getElementById('newDryPort').value=opt.getAttribute('data-dry-port')||'';document.getElementById('newContainerSize').value=opt.getAttribute('data-container-size')||'';document.getElementById('newContainerNo').value=opt.getAttribute('data-container-number')||'';document.getElementById('newShippingLine').value=opt.getAttribute('data-shipping-line')||'';document.getElementById('newVesselArrival').value=opt.getAttribute('data-vessel-arrival')||'';document.getElementById('newSoc').value=opt.getAttribute('data-soc')||'No';}});

function toggleAddTable(){var c=document.getElementById('addNewRowContainer'),b=document.getElementById('addNewRowBtn');if(c.style.display==='none'||!c.style.display){c.style.display='block';b.innerHTML='<i class="fas fa-times"></i> Close Form';b.style.background='#dc3545';}else{c.style.display='none';b.innerHTML='<i class="fas fa-plus-circle"></i> Add New Row';b.style.background='#8e181e';}}

function saveNewRow(){var jobId=document.getElementById('newJobSelect').value;if(!jobId){alert('Select a job!');return;}var ca=document.getElementById('newCreatedAt').value;if(!ca){var now=new Date();ca=now.toISOString().slice(0,16);}var fd=new FormData();fd.append('id','');fd.append('job_id',jobId);fd.append('clearing_agent',document.getElementById('newClearingAgent').value);fd.append('bonded_carrier',document.getElementById('newBondedCarrier').value);fd.append('importer',document.getElementById('newImporter').value);fd.append('dry_port',document.getElementById('newDryPort').value);fd.append('container_size',document.getElementById('newContainerSize').value);fd.append('container_no',document.getElementById('newContainerNo').value);fd.append('shipping_line',document.getElementById('newShippingLine').value);fd.append('free_days',document.getElementById('newFreeDays').value);fd.append('vessel_arrival',document.getElementById('newVesselArrival').value);fd.append('loading_date',document.getElementById('newLoadingDate').value);fd.append('created_at',ca);fd.append('lahore_arrival',document.getElementById('newLahoreArrival').value);fd.append('status',document.getElementById('newStatus').value);fd.append('vehicles_no',document.getElementById('newVehiclesNo').value);fd.append('trucker_cell',document.getElementById('newTruckerCell').value);fd.append('soc',document.getElementById('newSoc').value);var btn=document.querySelector('#addNewRowContainer .save-new-btn'),ot=btn.innerHTML;btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...';fetch('system.php?page=save-loading-file-ajax',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{if(data.success){window.location.href='system.php?page=loading-files';}else{alert('Error: '+data.message);btn.disabled=false;btn.innerHTML=ot;}}).catch(err=>{alert('Network error');btn.disabled=false;btn.innerHTML=ot;});}

function editLoadingRow(id){fetch('system.php?page=get-loading-file&id='+id).then(r=>r.json()).then(data=>{document.getElementById('edit_id').value=data.id;document.getElementById('edit_job_id').value=data.job_id;document.getElementById('edit_clearing_agent').value=data.clearing_agent||'';document.getElementById('edit_bonded_carrier').value=data.bonded_carrier||'';document.getElementById('edit_importer').value=data.importer||'';document.getElementById('edit_dry_port').value=data.dry_port||'';document.getElementById('edit_container_size').value=data.container_size||'';document.getElementById('edit_container_no').value=data.container_no||'';document.getElementById('edit_shipping_line').value=data.shipping_line||'';document.getElementById('edit_free_days').value=data.free_days||'';document.getElementById('edit_vessel_arrival').value=data.vessel_arrival||'';document.getElementById('edit_loading_date').value=data.loading_date||'';document.getElementById('edit_lahore_arrival').value=data.lahore_arrival||'';document.getElementById('edit_status').value=data.status||'Pending';document.getElementById('edit_vehicles_no').value=data.vehicles_no||'';document.getElementById('edit_trucker_cell').value=data.trucker_cell||'';document.getElementById('edit_soc').value=data.soc||'No';document.getElementById('editLoadingModal').style.display='flex';});}

function closeEditModal(){document.getElementById('editLoadingModal').style.display='none';}

function deleteLoading(id){if(confirm('Delete this record?')){fetch('system.php?page=delete-loading-file-ajax&id='+id).then(r=>r.json()).then(data=>{if(data.success)location.reload();else alert('Error deleting');});}}

function applyLoadingDateFilter(){var s=document.getElementById('loading_start_date').value,e=document.getElementById('loading_end_date').value;if(s&&e)window.location.href='system.php?page=loading-files&start_date='+s+'&end_date='+e;else alert('Select both dates');}

function clearLoadingDateFilter(){window.location.href='system.php?page=loading-files';}

function initLoadingTableFeatures(){document.querySelectorAll('#groupedTablesContainer .jobs-google-sheets-table').forEach(table=>{var filters=table.querySelectorAll('.col-filter');filters.forEach(filter=>{filter.addEventListener('keyup',function(){var ci=parseInt(this.dataset.col),fv=this.value.toLowerCase(),rows=table.querySelectorAll('tbody tr');rows.forEach(row=>{if(row.querySelector('td[colspan]'))return;var cell=row.children[ci];if(cell)row.style.display=cell.textContent.toLowerCase().includes(fv)?'':(fv===''?'':'none');});});});var headers=table.querySelectorAll('th.sortable'),currentSortColumn=null,currentSortDirection='asc';headers.forEach(header=>{header.addEventListener('click',function(e){if(e.target.classList&&e.target.classList.contains('col-filter'))return;var column=parseInt(this.dataset.column),tbody=table.querySelector('tbody'),rows=Array.from(tbody.querySelectorAll('tr'));if(rows.length===1&&rows[0].querySelector('td[colspan]'))return;headers.forEach(h=>h.classList.remove('asc','desc'));if(currentSortColumn===column)currentSortDirection=currentSortDirection==='asc'?'desc':'asc';else{currentSortColumn=column;currentSortDirection='asc';}this.classList.add(currentSortDirection);rows.sort((a,b)=>{var av=a.children[column]?a.children[column].innerText.trim():'',bv=b.children[column]?b.children[column].innerText.trim():'';var an=parseFloat(av.replace(/[^0-9.-]/g,'')),bn=parseFloat(bv.replace(/[^0-9.-]/g,''));if(!isNaN(an)&&!isNaN(bn))return currentSortDirection==='asc'?an-bn:bn-an;av=av.toLowerCase();bv=bv.toLowerCase();return currentSortDirection==='asc'?av.localeCompare(bv):bv.localeCompare(av);});rows.forEach(row=>tbody.appendChild(row));});});});}

document.addEventListener('DOMContentLoaded',function(){initLoadingTableFeatures();});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>