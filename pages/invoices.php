<?php
$current_path = isset($_GET['path']) ? $_GET['path'] : '';
$full_path = __DIR__ . '/../invoices/' . $current_path;
$userId = getUserId();

if (is_file($full_path)) {
    if (canUserViewInvoice($pdo, $userId, $current_path)) {
        header('Content-Type: text/html');
        readfile($full_path);
        exit();
    } else {
        $_SESSION['error'] = "Access denied!";
        header("Location: system.php?page=invoices");
        exit();
    }
}

$items = getUserInvoiceItems($pdo, $userId, $current_path);
?>

<?php require_once 'includes/header.php'; ?>

<div class="dashboard-header">
    <div><h1 class="page-title">Invoices</h1><p class="page-subtitle">Browse invoices by Year → Consignee - Customer Name</p></div>
    <?php if($current_path): ?><a href="system.php?page=invoices&path=<?php echo urlencode(dirname($current_path)); ?>" class="btn-primary"><i class="fas fa-arrow-left"></i> Back</a><?php endif; ?>
</div>

<div class="card"><div class="card-body">
<?php if(empty($items)): ?>
    <p style="text-align:center; padding:40px;">No invoices found. Create bills to see them here.</p>
<?php else: ?>
    <div class="folder-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px;">
    <?php foreach($items as $item): ?>
        <div class="folder-card" style="background:white; border-radius:12px; padding:20px; text-align:center; border:1px solid #e9edf2;">
            <?php if($item['is_dir']): ?>
                <div onclick="location.href='system.php?page=invoices&path=<?php echo urlencode($item['path']); ?>'">
                    <div class="folder-icon" style="font-size:3rem; color:#ff9800;"><i class="fas fa-folder"></i></div>
                    <div class="folder-name" style="font-weight:600; color:#3a4984;"><?php echo htmlspecialchars($item['name']); ?></div>
                    <small><?php echo $item['modified']; ?></small>
                </div>
                <div style="margin-top:10px;"><button class="btn-danger" onclick="deleteInvoiceItem('<?php echo urlencode($item['path']); ?>', 'folder')" style="padding:3px 8px; font-size:11px;"><i class="fas fa-trash"></i> Delete Folder</button></div>
            <?php else: ?>
                <div onclick="window.open('system.php?page=invoices&path=<?php echo urlencode($item['path']); ?>','_blank')">
                    <div class="folder-icon" style="font-size:3rem; color:#8e181e;"><i class="fas fa-file-invoice"></i></div>
                    <div class="folder-name" style="font-weight:600; color:#3a4984;"><?php echo htmlspecialchars($item['name']); ?></div>
                    <small><?php echo $item['modified']; ?></small>
                </div>
                <div style="margin-top:10px; display:flex; gap:5px; justify-content:center;">
                    <a href="system.php?page=download-invoice&path=<?php echo urlencode($item['path']); ?>" class="btn-success" style="padding:3px 8px; font-size:11px; text-decoration:none;"><i class="fas fa-download"></i> Download</a>
                    <button class="btn-danger" onclick="deleteInvoiceItem('<?php echo urlencode($item['path']); ?>', 'file')" style="padding:3px 8px; font-size:11px;"><i class="fas fa-trash"></i> Delete</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
</div></div>

<div id="deleteInvoiceModal" class="modal"><div class="modal-content"><div class="modal-header"><h3>Confirm Delete</h3><button class="close-modal" onclick="closeDeleteModal()">&times;</button></div><div class="modal-body" id="deleteModalBody"></div><div class="modal-footer"><button class="btn-danger" onclick="confirmDelete()">Yes, Delete</button><button class="btn-primary" onclick="closeDeleteModal()">Cancel</button></div></div></div>

<script>
let deletePath='',deleteType='';
function deleteInvoiceItem(path,type){deletePath=path;deleteType=type;document.getElementById('deleteModalBody').innerHTML='<p>Delete '+type+'? This cannot be undone.</p><p><strong>'+decodeURIComponent(path)+'</strong></p>';document.getElementById('deleteInvoiceModal').style.display='flex';}
function confirmDelete(){window.location.href='system.php?page=delete-invoice&path='+deletePath+'&type='+deleteType;}
function closeDeleteModal(){document.getElementById('deleteInvoiceModal').style.display='none';}
</script>

<?php require_once 'includes/footer.php'; ?>