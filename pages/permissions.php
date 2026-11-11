<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$target_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$users = getAllUsers($pdo);

if ($target_user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$target_user_id]);
    $target_user = $stmt->fetch();
    if ($target_user) {
        $jobs_with_permission = getJobsWithPermissionInfo($pdo, $target_user_id);
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="dashboard-header"><div><h1 class="page-title">Manage User Permissions</h1><p class="page-subtitle">Grant/Revoke job access to users</p></div></div>

<div class="card"><div class="card-header"><h3><i class="fas fa-users"></i> Select User</h3></div><div class="card-body"><div class="user-list" style="display:flex; flex-wrap:wrap; gap:10px;"><?php foreach($users as $u): ?><a href="system.php?page=manage-permissions&user_id=<?php echo $u['id']; ?>" class="user-btn" style="background: <?php echo ($target_user_id == $u['id'])?'#8e181e':'#f0f2f5'; ?>; border-radius:8px; padding:10px 20px; text-decoration:none; color: <?php echo ($target_user_id == $u['id'])?'white':'#333'; ?>;"><i class="fas fa-user"></i> <?php echo htmlspecialchars($u['full_name']); ?> (<?php echo htmlspecialchars($u['username']); ?>)</a><?php endforeach; ?></div></div></div>

<?php if ($target_user_id && isset($target_user)): ?>
<div class="card"><div class="card-header"><h3><i class="fas fa-key"></i> Job Permissions for: <?php echo htmlspecialchars($target_user['full_name']); ?></h3></div><div class="card-body"><div class="bulk-actions" style="margin-bottom:15px; padding:10px; background:#f8fafd; border-radius:8px; display:flex; gap:10px;"><button type="button" class="select-all-btn" onclick="toggleSelectAll()" style="background:#3a4984; color:white; border:none; padding:6px 12px; border-radius:5px;"><i class="fas fa-check-double"></i> Select All</button><button type="button" class="select-all-btn" onclick="deselectAll()" style="background:#999; color:white; border:none; padding:6px 12px; border-radius:5px;"><i class="fas fa-times"></i> Deselect All</button><button type="button" class="bulk-grant-btn" onclick="bulkGrantPermissions()" style="background:#4caf50; color:white; border:none; padding:6px 15px; border-radius:5px;"><i class="fas fa-check-circle"></i> Grant Selected</button><span class="selected-count" id="selectedCount">0 jobs selected</span></div>
<form id="bulkGrantForm" method="POST" action="system.php?page=bulk-grant-permissions"><input type="hidden" name="user_id" value="<?php echo $target_user_id; ?>"><div class="table-container"><table class="data-table"><thead><tr><th style="width:30px;"><input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAllFromCheckbox()"></th><th>Job #</th><th>Consignee</th><th>Customer Name</th><th>Created By</th><th>Status</th><th>Permission</th><th>Action</th></tr></thead><tbody><?php foreach($jobs_with_permission as $job): $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?"); $stmt->execute([$job['created_by']]); $creator = $stmt->fetch(); $creator_name = $creator['full_name'] ?? 'Unknown'; $canGrant = !$job['is_owner'] && !$job['has_permission']; ?>
<tr><td class="text-center"><?php if($canGrant): ?><input type="checkbox" name="job_ids[]" value="<?php echo $job['id']; ?>" class="job-checkbox" onchange="updateSelectedCount()"><?php else: ?><span style="color:#ccc;">—</span><?php endif; ?></td><td><strong><?php echo htmlspecialchars($job['job_number'] ?? ''); ?></strong></td><td><?php echo htmlspecialchars($job['consignee'] ?? '-'); ?></td><td><?php echo htmlspecialchars($job['customer_name'] ?? '-'); ?></td><td><?php echo htmlspecialchars($creator_name); ?></td><td><span class="status-badge <?php echo $job['status'] ?? 'active'; ?>"><?php echo $job['status'] ?? 'active'; ?></span></td>
<td><?php if($job['is_owner']): ?><span class="status-badge owner" style="background:#fff3e0; color:#ff9800;"><i class="fas fa-crown"></i> Owner</span><?php elseif($job['has_permission']): ?><span class="status-badge permitted" style="background:#e8f5e9; color:#4caf50;"><i class="fas fa-check-circle"></i> Granted</span><?php else: ?><span class="status-badge none" style="background:#ffebee; color:#f44336;"><i class="fas fa-times-circle"></i> No Access</span><?php endif; ?></td>
<td><?php if(!$job['is_owner']): ?><?php if($job['has_permission']): ?><a href="system.php?page=revoke-permission&user_id=<?php echo $target_user_id; ?>&job_id=<?php echo $job['id']; ?>" class="btn-danger" onclick="return confirm('Revoke permission?')" style="padding:5px 10px;"><i class="fas fa-ban"></i> Revoke</a><?php else: ?><a href="system.php?page=grant-permission&user_id=<?php echo $target_user_id; ?>&job_id=<?php echo $job['id']; ?>" class="btn-success" style="padding:5px 10px;"><i class="fas fa-check"></i> Grant</a><?php endif; ?><?php else: ?><span style="color:#81c784;"><i class="fas fa-lock"></i> Auto</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></form></div></div>
<script>
function updateSelectedCount(){var c=document.querySelectorAll('.job-checkbox:checked').length;document.getElementById('selectedCount').innerHTML=c+' job(s) selected';}
function toggleSelectAll(){var c=document.querySelectorAll('.job-checkbox');for(var i=0;i<c.length;i++)c[i].checked=true;updateSelectedCount();}
function deselectAll(){var c=document.querySelectorAll('.job-checkbox');for(var i=0;i<c.length;i++)c[i].checked=false;updateSelectedCount();}
function toggleSelectAllFromCheckbox(){var sa=document.getElementById('selectAllCheckbox');var c=document.querySelectorAll('.job-checkbox');for(var i=0;i<c.length;i++)c[i].checked=sa.checked;updateSelectedCount();}
function bulkGrantPermissions(){var c=document.querySelectorAll('.job-checkbox:checked');if(c.length===0){alert('Select at least one job!');return;}if(confirm('Grant permissions for '+c.length+' job(s)?')){document.getElementById('bulkGrantForm').submit();}}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>