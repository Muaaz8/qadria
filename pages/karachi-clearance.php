<?php
$userId = getUserId();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$clearance_records = getKarachiClearance($pdo, $search, $start_date, $end_date);
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
        <h1 class="page-title"><i class="fas fa-ship"></i> Karachi Clearance</h1>
        <p class="page-subtitle">Manage Karachi clearance records</p>
    </div>
    <button class="btn-primary" onclick="openClearanceModal()"><i class="fas fa-plus"></i> New Clearance Record</button>
</div>

<div class="date-filter" style="background:white; border-radius:12px; padding:15px 20px; margin-bottom:20px; display:flex; flex-wrap:wrap; align-items:flex-end; gap:15px; border:1px solid #e0e0e0;">
    <div class="date-filter-group"><label style="font-size:12px; color:#8e181e;"><i class="fas fa-calendar-alt"></i> Start Date</label><input type="date" id="clearance_start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px;"></div>
    <div class="date-filter-group"><label style="font-size:12px; color:#8e181e;"><i class="fas fa-calendar-alt"></i> End Date</label><input type="date" id="clearance_end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px;"></div>
    <div class="filter-actions" style="display:flex; gap:10px; margin-left:auto;">
        <button class="btn-primary" onclick="applyClearanceDateFilter()"><i class="fas fa-filter"></i> Apply Filter</button>
        <button class="btn-danger" onclick="clearClearanceDateFilter()" style="background:#81c784;"><i class="fas fa-times"></i> Clear</button>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-container" style="overflow-x:auto; border-radius:8px; border:1px solid #e0e0e0; background:white;">
            <table class="data-table clearance-table" id="clearanceTable" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#3a4984;">
                        <th style="color:white; padding:12px;">JOB #</th>
                        <th style="color:white; padding:12px;">DATE</th>
                        <th style="color:white; padding:12px;">SHIPPER</th>
                        <th style="color:white; padding:12px;">ON ACCOUNT</th>
                        <th style="color:white; padding:12px;">D/O COLLECTED</th>
                        <th style="color:white; padding:12px;">IMPORTER</th>
                        <th style="color:white; padding:12px;">BL NO.</th>
                        <th style="color:white; padding:12px;">CONTAINER NO.</th>
                        <th style="color:white; padding:12px;">VESSEL</th>
                        <th style="color:white; padding:12px;">SOC</th>
                        <th style="color:white; padding:12px;">STATUS</th>
                        <th style="color:white; padding:12px;">ACTIONS</th>
                    </tr>
                    <tr class="filter-row" style="background:#f8f9fa;">
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="0" placeholder="Filter Job #" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="1" placeholder="Filter Date" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="2" placeholder="Filter Shipper" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="3" placeholder="Filter Account" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="4" placeholder="Filter D/O" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="5" placeholder="Filter Importer" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="6" placeholder="Filter BL" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="7" placeholder="Filter Container" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="8" placeholder="Filter Vessel" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="9" placeholder="Filter SOC" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"><input type="text" class="col-filter" data-col="10" placeholder="Filter Status" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                        <th style="padding:6px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clearance_records as $record): ?>
                    <tr ondblclick="viewClearance(<?php echo $record['id']; ?>)" data-id="<?php echo $record['id']; ?>">
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><strong><?php echo htmlspecialchars($record['karachi_clearance_number']); ?></strong></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo date('d-m-Y', strtotime($record['date'])); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['shipper'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['on_account'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['clearing_agent'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['importer_name'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['bl_no'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['container_no'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($record['vessel_name'] ?? '-'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><span class="status-badge <?php echo ($record['soc'] ?? 'No') == 'Yes' ? 'active' : 'cancelled'; ?>"><?php echo $record['soc'] ?? 'No'; ?></span></td>
                        <td style="padding:10px; border-bottom:1px solid #e9edf2;"><span class="status-badge <?php echo $record['status'] ?? 'active'; ?>"><?php echo $record['status'] ?? 'active'; ?></span></td>
                        <td class="action-buttons" style="white-space:nowrap;">
                            <button class="btn-info btn-sm" onclick="event.stopPropagation(); viewClearance(<?php echo $record['id']; ?>)" title="View"><i class="fas fa-eye"></i></button>
                            <button class="btn-warning btn-sm" onclick="event.stopPropagation(); editClearance(<?php echo $record['id']; ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn-danger btn-sm" onclick="event.stopPropagation(); deleteClearance(<?php echo $record['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($clearance_records)): ?>
                    <tr><td colspan="12" style="text-align:center; padding:60px;"><i class="fas fa-folder-open" style="font-size:48px; color:#c8e6c9;"></i><p style="margin-top:10px;">No clearance records found.</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="clearanceModal" class="modal">
    <div class="modal-content" style="max-width:800px;">
        <div class="modal-header">
            <h3 id="clearanceModalTitle"><i class="fas fa-plus-circle"></i> Add New Clearance Record</h3>
            <button class="close-modal" onclick="closeClearanceModal()">&times;</button>
        </div>
        <form method="POST" action="system.php?page=create-karachi-clearance" id="clearanceForm">
            <input type="hidden" name="id" id="clearance_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-hashtag"></i> Job # (Auto)</label><input type="text" id="clearance_number" class="form-control" readonly style="background:#f5f9f5; font-weight:bold; color:#8e181e;"></div>
                    <div class="form-group"><label><i class="fas fa-calendar"></i> Date *</label><input type="date" name="date" id="clearance_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-truck"></i> Shipper *</label><input type="text" name="shipper" id="clearance_shipper" class="form-control" required></div>
                    <div class="form-group"><label><i class="fas fa-user"></i> On Account *</label><input type="text" name="on_account" id="clearance_on_account" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-calendar-check"></i> D/O Collected On *</label><input type="text" name="clearing_agent" id="clearance_clearing_agent" class="form-control" required></div>
                    <div class="form-group"><label><i class="fas fa-building"></i> Importer Name *</label><input type="text" name="importer_name" id="clearance_importer_name" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-barcode"></i> BL No. *</label><input type="text" name="bl_no" id="clearance_bl_no" class="form-control" required></div>
                    <div class="form-group"><label><i class="fas fa-box"></i> Container No. *</label><input type="text" name="container_no" id="clearance_container_no" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-ship"></i> Vessel Name *</label><input type="text" name="vessel_name" id="clearance_vessel_name" class="form-control" required></div>
                    <div class="form-group"><label><i class="fas fa-calendar-alt"></i> ETA</label><input type="date" name="eta" id="clearance_eta" class="form-control"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-toggle-on"></i> SOC *</label>
                        <select name="soc" id="clearance_soc" class="form-control" required><option value="No">No</option><option value="Yes">Yes</option></select>
                    </div>
                    <div class="form-group"><label><i class="fas fa-toggle-on"></i> Status *</label>
                        <select name="status" id="clearance_status" class="form-control" required><option value="active">Active</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select>
                    </div>
                </div>
                <div class="form-group"><label><i class="fas fa-file-alt"></i> Description *</label><textarea name="description" id="clearance_description" rows="3" class="form-control" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-danger" onclick="closeClearanceModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Record</button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div id="viewClearanceModal" class="modal">
    <div class="modal-content" style="max-width:700px;">
        <div class="modal-header"><h3>Clearance Record Details</h3><button class="close-modal" onclick="closeViewClearanceModal()">&times;</button></div>
        <div class="modal-body" id="viewClearanceBody"></div>
        <div class="modal-footer"><button class="btn-primary" onclick="window.print()">Print</button><button class="btn-danger" onclick="closeViewClearanceModal()">Close</button></div>
    </div>
</div>

<script>
function generateClearanceNumber(){fetch('system.php?page=get-next-clearance-number').then(r=>r.json()).then(data=>{if(data.number)document.getElementById('clearance_number').value=data.number;});}
function resetClearanceForm(){document.getElementById('clearanceModalTitle').innerHTML='<i class="fas fa-plus-circle"></i> Add New Clearance Record';document.getElementById('clearanceForm').action='system.php?page=create-karachi-clearance';document.getElementById('clearance_id').value='';generateClearanceNumber();document.getElementById('clearance_date').value=new Date().toISOString().split('T')[0];document.getElementById('clearance_shipper').value='';document.getElementById('clearance_on_account').value='';document.getElementById('clearance_clearing_agent').value='';document.getElementById('clearance_importer_name').value='';document.getElementById('clearance_bl_no').value='';document.getElementById('clearance_container_no').value='';document.getElementById('clearance_description').value='';document.getElementById('clearance_vessel_name').value='';document.getElementById('clearance_eta').value='';document.getElementById('clearance_soc').value='No';document.getElementById('clearance_status').value='active';}
function openClearanceModal(){resetClearanceForm();document.getElementById('clearanceModal').style.display='flex';}
function closeClearanceModal(){document.getElementById('clearanceModal').style.display='none';}
function viewClearance(id){fetch('system.php?page=get-karachi-clearance&id='+id).then(r=>r.json()).then(data=>{let html='<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:15px;">';html+='<div><strong>Job #:</strong><br>'+data.karachi_clearance_number+'</div>';html+='<div><strong>Date:</strong><br>'+data.date+'</div>';html+='<div><strong>Shipper:</strong><br>'+(data.shipper||'-')+'</div>';html+='<div><strong>On Account:</strong><br>'+(data.on_account||'-')+'</div>';html+='<div><strong>D/O Collected:</strong><br>'+(data.clearing_agent||'-')+'</div>';html+='<div><strong>Importer:</strong><br>'+(data.importer_name||'-')+'</div>';html+='<div><strong>BL No.:</strong><br>'+(data.bl_no||'-')+'</div>';html+='<div><strong>Container No.:</strong><br>'+(data.container_no||'-')+'</div>';html+='<div><strong>Vessel:</strong><br>'+(data.vessel_name||'-')+'</div>';html+='<div><strong>ETA:</strong><br>'+(data.eta||'-')+'</div>';html+='<div><strong>SOC:</strong><br>'+data.soc+'</div>';html+='<div><strong>Status:</strong><br>'+data.status+'</div>';html+='<div style="grid-column:span 2;"><strong>Description:</strong><br>'+(data.description||'-')+'</div></div>';document.getElementById('viewClearanceBody').innerHTML=html;document.getElementById('viewClearanceModal').style.display='flex';});}
function closeViewClearanceModal(){document.getElementById('viewClearanceModal').style.display='none';}
function editClearance(id){fetch('system.php?page=get-karachi-clearance&id='+id).then(r=>r.json()).then(data=>{document.getElementById('clearanceModalTitle').innerHTML='<i class="fas fa-edit"></i> Edit Clearance Record';document.getElementById('clearanceForm').action='system.php?page=edit-karachi-clearance';document.getElementById('clearance_id').value=data.id;document.getElementById('clearance_number').value=data.karachi_clearance_number;document.getElementById('clearance_date').value=data.date;document.getElementById('clearance_shipper').value=data.shipper||'';document.getElementById('clearance_on_account').value=data.on_account||'';document.getElementById('clearance_clearing_agent').value=data.clearing_agent||'';document.getElementById('clearance_importer_name').value=data.importer_name||'';document.getElementById('clearance_bl_no').value=data.bl_no||'';document.getElementById('clearance_container_no').value=data.container_no||'';document.getElementById('clearance_description').value=data.description||'';document.getElementById('clearance_vessel_name').value=data.vessel_name||'';document.getElementById('clearance_eta').value=data.eta||'';document.getElementById('clearance_soc').value=data.soc||'No';document.getElementById('clearance_status').value=data.status||'active';document.getElementById('clearanceModal').style.display='flex';});}
function deleteClearance(id){if(confirm('Delete this record?'))window.location.href='system.php?page=delete-karachi-clearance&id='+id;}
function applyClearanceDateFilter(){let s=document.getElementById('clearance_start_date').value;let e=document.getElementById('clearance_end_date').value;if(s&&e)window.location.href='system.php?page=karachi-clearance&start_date='+s+'&end_date='+e;else alert('Select both dates');}
function clearClearanceDateFilter(){window.location.href='system.php?page=karachi-clearance';}
function initClearanceFilters(){document.querySelectorAll('#clearanceTable .col-filter').forEach(f=>{f.addEventListener('keyup',function(){let ci=parseInt(this.dataset.col),fv=this.value.toLowerCase(),rows=document.querySelectorAll('#clearanceTable tbody tr');rows.forEach(row=>{if(row.querySelector('td[colspan]'))return;let cell=row.children[ci];if(cell)row.style.display=cell.textContent.toLowerCase().includes(fv)?'':(fv===''?'':'none');});});});}
document.addEventListener('DOMContentLoaded',function(){if(document.getElementById('clearance_number'))generateClearanceNumber();initClearanceFilters();});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>