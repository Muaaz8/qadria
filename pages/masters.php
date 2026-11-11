<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'bonded_carrier';
$masters = getMasters($pdo, $type);
$types = [
    'bonded_carrier'=>'Bonded Carrier Name',
    'do'=>'D/O', 
    'customer_name'=>'Customer Name',
    'consignee'=>'Consignee',
    'container_size'=>'Container Size',
    'khi_port'=>'KHI Port',
    'dry_port'=>'Dry Port',
    'vessel_name'=>'Vessel Name',
    'shipping_line'=>'Shipping Line',
    'freight_forwarder_name'=>'Freight Forwarder Name',
    'depositor'=>'Depositor'
];
?>

<?php require_once 'includes/header.php'; ?>

<div class="dashboard-header">
    <div><h1 class="page-title">Masters</h1><p class="page-subtitle">Manage dropdown options (Admin Only)</p></div>
    <button class="btn-primary" onclick="openMasterModal()"><i class="fas fa-plus"></i> Add New</button>
</div>

<div class="master-types" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
    <?php foreach($types as $key=>$label): ?>
        <a href="system.php?page=masters&type=<?php echo $key; ?>" class="master-type-btn" style="background: <?php echo ($type==$key)?'#8e181e':'#f8fafd'; ?>; border:1px solid #c8e6c9; padding:8px 16px; border-radius:20px; text-decoration:none; color: <?php echo ($type==$key)?'white':'#3a4984'; ?>; font-size:0.85rem;">
            <?php echo $label; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card"><div class="card-body" style="padding:0;"><div class="table-container"><table class="data-table"><thead><tr><th>Code</th><th>Name</th><th>Sort Order</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($masters as $master): ?><tr><td><strong><?php echo htmlspecialchars($master['code'] ?? ''); ?></strong></td><td><?php echo htmlspecialchars($master['name'] ?? ''); ?></td><td><?php echo $master['sort_order'] ?? 0; ?></td><td><span class="status-badge <?php echo ($master['is_active'] ?? 1) ? 'active' : 'cancelled'; ?>"><?php echo ($master['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?></span></td>
<td class="action-buttons"><button class="btn-warning btn-sm" onclick='editMaster(<?php echo $master['id']; ?>,"<?php echo addslashes($master['code'] ?? ''); ?>","<?php echo addslashes($master['name'] ?? ''); ?>",<?php echo $master['sort_order'] ?? 0; ?>,<?php echo $master['is_active'] ?? 1; ?>)'><i class="fas fa-edit"></i></button><button class="btn-danger btn-sm" onclick="deleteMaster(<?php echo $master['id']; ?>)"><i class="fas fa-trash"></i></button></td></tr><?php endforeach; ?></tbody></table></div></div></div>

<div id="masterModal" class="modal"><div class="modal-content" style="max-width:500px;"><div class="modal-header"><h3 id="masterModalTitle">Add New Item</h3><button class="close-modal" onclick="closeMasterModal()">&times;</button></div><div class="modal-body"><input type="hidden" id="masterId"><div class="form-group"><label>Type</label><select id="masterType" class="form-control"><?php foreach($types as $key=>$label): ?><option value="<?php echo $key; ?>" <?php echo ($key==$type)?'selected':''; ?>><?php echo $label; ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Code</label><input type="text" id="masterCode" class="form-control"></div><div class="form-group"><label>Name</label><input type="text" id="masterName" class="form-control"></div><div class="form-group"><label>Sort Order</label><input type="number" id="masterOrder" value="0" class="form-control"></div><div class="form-group"><label>Status</label><select id="masterStatus" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div></div><div class="modal-footer"><button class="btn-danger" onclick="closeMasterModal()">Cancel</button><button class="btn-primary" onclick="saveMaster()">Save</button></div></div></div>

<script>
function openMasterModal(){document.getElementById('masterModalTitle').innerText='Add New Item';document.getElementById('masterId').value='';document.getElementById('masterCode').value='';document.getElementById('masterName').value='';document.getElementById('masterOrder').value='0';document.getElementById('masterStatus').value='1';document.getElementById('masterModal').style.display='flex';}
function editMaster(id,c,n,o,s){document.getElementById('masterModalTitle').innerText='Edit Item';document.getElementById('masterId').value=id;document.getElementById('masterCode').value=c;document.getElementById('masterName').value=n;document.getElementById('masterOrder').value=o;document.getElementById('masterStatus').value=s;document.getElementById('masterModal').style.display='flex';}
function saveMaster(){var id=document.getElementById('masterId').value;var t=document.getElementById('masterType').value;var c=document.getElementById('masterCode').value;var n=document.getElementById('masterName').value;var o=document.getElementById('masterOrder').value;var s=document.getElementById('masterStatus').value;var url=id?'system.php?page=update-master':'system.php?page=add-master';fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id,type:t,code:c,name:n,sort_order:o,is_active:s})}).then(()=>location.reload());}
function deleteMaster(id){if(confirm('Delete this item?')){fetch('system.php?page=delete-master&id='+id).then(()=>location.reload());}}
function closeMasterModal(){document.getElementById('masterModal').style.display='none';}
</script>

<?php require_once 'includes/footer.php'; ?>