<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<?php require_once 'includes/header.php'; ?>

<div class="dashboard-header">
    <div><h1 class="page-title">Users</h1><p class="page-subtitle">Manage system users (Admin Only)</p></div>
    <button class="btn-primary" onclick="openUserModal()"><i class="fas fa-user-plus"></i> Add User</button>
</div>

<div class="card"><div class="card-body" style="padding:0;"><div class="table-container"><table class="data-table"><thead><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr></thead><tbody><?php foreach($users as $user): ?><tr><td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td><td><?php echo htmlspecialchars($user['full_name']); ?></td><td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td><td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td><td><?php if($user['role']=='admin'): ?><span class="status-badge active">Admin</span><?php else: ?><span class="status-badge completed">User</span><?php endif; ?></td>
<td class="action-buttons"><?php if($user['role'] != 'admin'): ?><button class="btn-warning btn-sm" onclick='openEditUserModal(<?php echo $user['id']; ?>,"<?php echo addslashes($user['full_name']); ?>","<?php echo addslashes($user['email']??''); ?>","<?php echo addslashes($user['phone']??''); ?>","<?php echo $user['role']; ?>")'><i class="fas fa-edit"></i></button><a href="system.php?page=delete-user&id=<?php echo $user['id']; ?>" class="btn-danger btn-sm" onclick="return confirm('Delete user?')"><i class="fas fa-trash"></i></a><?php else: ?><span style="color:#4caf50;"><i class="fas fa-shield-alt"></i> System Admin</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>

<div id="userModal" class="modal"><div class="modal-content" style="max-width:500px;"><div class="modal-header"><h3>Add New User</h3><button class="close-modal" onclick="closeUserModal()">&times;</button></div><form method="POST" action="system.php?page=create-user"><div class="modal-body"><div class="form-group"><label>Username *</label><input type="text" name="username" required class="form-control"></div><div class="form-group"><label>Password *</label><input type="password" name="password" required class="form-control"></div><div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required class="form-control"></div><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div><div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div><div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="user">User</option><option value="admin">Admin</option></select></div></div><div class="modal-footer"><button type="button" class="btn-danger" onclick="closeUserModal()">Cancel</button><button type="submit" class="btn-primary">Create User</button></div></form></div></div>

<div id="editUserModal" class="modal"><div class="modal-content" style="max-width:500px;"><div class="modal-header"><h3>Edit User</h3><button class="close-modal" onclick="closeEditUserModal()">&times;</button></div><form method="POST" action="system.php?page=update-user"><input type="hidden" name="id" id="edit_user_id"><div class="modal-body"><div class="form-group"><label>Full Name *</label><input type="text" name="full_name" id="edit_full_name" required class="form-control"></div><div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control"></div><div class="form-group"><label>Phone</label><input type="text" name="phone" id="edit_phone" class="form-control"></div><div class="form-group"><label>Role</label><select name="role" id="edit_role" class="form-control"><option value="user">User</option><option value="admin">Admin</option></select></div></div><div class="modal-footer"><button type="button" class="btn-danger" onclick="closeEditUserModal()">Cancel</button><button type="submit" class="btn-primary">Update User</button></div></form></div></div>

<script>
function openUserModal(){document.getElementById('userModal').style.display='flex';}
function closeUserModal(){document.getElementById('userModal').style.display='none';}
function openEditUserModal(id,n,e,p,r){document.getElementById('edit_user_id').value=id;document.getElementById('edit_full_name').value=n;document.getElementById('edit_email').value=e;document.getElementById('edit_phone').value=p;document.getElementById('edit_role').value=r;document.getElementById('editUserModal').style.display='flex';}
function closeEditUserModal(){document.getElementById('editUserModal').style.display='none';}
</script>

<?php require_once 'includes/footer.php'; ?>