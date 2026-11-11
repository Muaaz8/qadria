<?php
$userId = getUserId();
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if (isAdmin()) {
    if ($start_date && $end_date) {
        $stmt = $pdo->prepare("SELECT b.*, j.job_number, j.consignee FROM bills b LEFT JOIN jobs j ON b.job_id = j.id WHERE DATE(b.created_at) BETWEEN ? AND ? ORDER BY b.id DESC");
        $stmt->execute([$start_date, $end_date]);
        $bills = $stmt->fetchAll();
    } else {
        $bills = $pdo->query("SELECT b.*, j.job_number, j.consignee FROM bills b LEFT JOIN jobs j ON b.job_id = j.id ORDER BY b.id DESC")->fetchAll();
    }
} else {
    $bills = getUserVisibleBills($pdo, $userId);
    if ($start_date && $end_date) {
        $filtered_bills = [];
        foreach ($bills as $bill) {
            $bill_date = date('Y-m-d', strtotime($bill['created_at']));
            if ($bill_date >= $start_date && $bill_date <= $end_date) $filtered_bills[] = $bill;
        }
        $bills = $filtered_bills;
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="dashboard-header">
    <div><h1 class="page-title">Bills / Invoices</h1><p class="page-subtitle">Manage all invoices</p></div>
    <div><span style="background:#e8f5e9; padding:5px 12px; border-radius:20px; font-size:12px;"><i class="fas fa-database"></i> Total: <?php echo count($bills); ?> bills</span></div>
</div>

<div class="date-filter" style="background:white; border-radius:12px; padding:15px 20px; margin-bottom:20px; display:flex; flex-wrap:wrap; align-items:flex-end; gap:15px; border:1px solid #e0e0e0;">
    <div class="date-filter-group"><label style="font-size:12px; color:#8e181e;"><i class="fas fa-calendar-alt"></i> Start Date</label><input type="date" id="bill_start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px;"></div>
    <div class="date-filter-group"><label style="font-size:12px; color:#8e181e;"><i class="fas fa-calendar-alt"></i> End Date</label><input type="date" id="bill_end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="padding:8px 12px; border:1px solid #e0e0e0; border-radius:8px;"></div>
    <div class="filter-actions" style="display:flex; gap:10px; margin-left:auto;">
        <button class="btn-primary" onclick="applyBillDateFilter()"><i class="fas fa-filter"></i> Apply Filter</button>
        <button class="btn-danger" onclick="clearBillDateFilter()" style="background:#81c784;"><i class="fas fa-times"></i> Clear</button>
        <button class="btn-success" onclick="exportBills('excel')" style="background:#2196f3;"><i class="fas fa-file-excel"></i> Excel</button>
        <button class="btn-info" onclick="exportBills('pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
    </div>
</div>

<div class="card"><div class="card-body" style="padding:0;">
    <div class="table-container" style="overflow-x:auto; border-radius:8px; border:1px solid #e0e0e0; background:white;">
        <table class="data-table" id="billsTable" style="width:100%;">
            <thead>
                <tr style="background:#3a4984;">
                    <th style="color:white; padding:12px;">BILL #</th><th style="color:white; padding:12px;">JOB #</th><th style="color:white; padding:12px;">CONSIGNEE</th>
                    <th style="color:white; padding:12px;">TYPE</th><th style="color:white; padding:12px;">TOTAL AMOUNT</th><th style="color:white; padding:12px;">BALANCE</th>
                    <th style="color:white; padding:12px;">DATE</th><th style="color:white; padding:12px;">ACTIONS</th>
                </tr>
                <tr class="filter-row" style="background:#f8f9fa;">
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="0" placeholder="Filter Bill #" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="1" placeholder="Filter Job #" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="2" placeholder="Filter Consignee" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="3" placeholder="Filter Type" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="4" placeholder="Filter Amount" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="5" placeholder="Filter Balance" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"><input type="text" class="col-filter" data-col="6" placeholder="Filter Date" style="width:100%; padding:6px; font-size:11px; border:1px solid #ddd; border-radius:4px;"></th>
                    <th style="padding:6px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bills as $bill): ?>
                <tr ondblclick="location.href='system.php?page=view-bill&id=<?php echo $bill['id']; ?>'">
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><strong><?php echo htmlspecialchars($bill['bill_number']); ?></strong></td>
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($bill['job_number'] ?? '-'); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo htmlspecialchars($bill['consignee'] ?? '-'); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo ucfirst($bill['bill_type']); if($bill['is_revised']) echo ' <span style="background:#ff9800; color:white; padding:2px 6px; border-radius:10px; font-size:9px;">Revised</span>'; ?></td>
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo formatCurrency($bill['total_amount']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo formatCurrency($bill['balance_amount']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #e9edf2;"><?php echo date('d-m-Y', strtotime($bill['created_at'])); ?></td>
                    <td class="action-buttons" style="white-space:nowrap;">
                        <button class="btn-info btn-sm" onclick="event.stopPropagation(); location.href='system.php?page=view-bill&id=<?php echo $bill['id']; ?>'"><i class="fas fa-eye"></i></button>
                        <?php if(!$bill['is_revised']): ?><button class="btn-warning btn-sm" onclick="event.stopPropagation(); location.href='system.php?page=revise-bill&id=<?php echo $bill['id']; ?>'"><i class="fas fa-copy"></i></button><?php endif; ?>
                        <button class="btn-danger btn-sm" onclick="event.stopPropagation(); location.href='system.php?page=export-pdf&id=<?php echo $bill['id']; ?>'"><i class="fas fa-file-pdf"></i></button>
                        <button class="btn-danger btn-sm" onclick="event.stopPropagation(); if(confirm('Delete?')) location.href='system.php?page=delete-bill&id=<?php echo $bill['id']; ?>'"><i class="fas fa-trash"></i></button>
                    </div>
                </table>
                <?php endforeach; ?>
                <?php if(empty($bills)): ?>
                <tr><td colspan="8" style="text-align:center; padding:60px;"><i class="fas fa-folder-open" style="font-size:48px; color:#c8e6c9;"></i><p>No bills found.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>

<script>
function applyBillDateFilter(){let s=document.getElementById('bill_start_date').value,e=document.getElementById('bill_end_date').value;if(s&&e)window.location.href='system.php?page=bills&start_date='+s+'&end_date='+e;else alert('Select both dates');}
function clearBillDateFilter(){window.location.href='system.php?page=bills';}
function exportBills(type){let s=document.getElementById('bill_start_date').value,e=document.getElementById('bill_end_date').value;if(!s||!e){alert('Select date range first');return;}let f=document.createElement('form');f.method='POST';f.action='system.php?page=export-bills-batch';let si=document.createElement('input');si.type='hidden';si.name='start_date';si.value=s;let ei=document.createElement('input');ei.type='hidden';ei.name='end_date';ei.value=e;let ti=document.createElement('input');ti.type='hidden';ti.name='export_type';ti.value=type;f.appendChild(si);f.appendChild(ei);f.appendChild(ti);document.body.appendChild(f);f.submit();document.body.removeChild(f);}
function initBillFilters(){document.querySelectorAll('#billsTable .col-filter').forEach(f=>{f.addEventListener('keyup',function(){let ci=parseInt(this.dataset.col),fv=this.value.toLowerCase(),rows=document.querySelectorAll('#billsTable tbody tr');rows.forEach(row=>{if(row.querySelector('td[colspan]'))return;let cell=row.children[ci];if(cell)row.style.display=cell.textContent.toLowerCase().includes(fv)?'':(fv===''?'':'none');});});});}
document.addEventListener('DOMContentLoaded',function(){initBillFilters();});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>