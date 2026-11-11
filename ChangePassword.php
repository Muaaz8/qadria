<div id="profileModal" class="modal">
    <div class="modal-content" style="max-width:500px;">

        <div class="modal-header">
            <h3><i class="fas fa-user-circle"></i> My Profile</h3>
            <button class="close-modal" onclick="closeProfileModal()">&times;</button>
        </div>

        <div class="modal-body">

            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:80px; height:80px; background:#8e181e; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:10px;">
                    <i class="fas fa-user" style="font-size:40px; color:white;"></i>
                </div>

                <h4><?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>
                <p style="color:#666;"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><span class="status-badge"><?php echo ucfirst($_SESSION['role']); ?></span></p>
            </div>

            <!-- PROFILE UPDATE -->
            <form method="POST" action="system.php?page=update-profile">

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Phone</label>
                    <input type="text" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn-primary" style="width:100%;">
                    <i class="fas fa-save"></i> Update Profile
                </button>

            </form>

            <hr style="margin:20px 0;">

            <!-- CHANGE PASSWORD -->
            <form method="POST" action="system.php?page=change-password">

                <h4 style="margin-bottom:15px;">Change Password</h4>

                <!-- CURRENT PASSWORD -->
                <div class="form-group" style="position:relative;">
                    <label><i class="fas fa-lock"></i> Current Password</label>

                    <input type="password"
                        name="current_password"
                        id="current_password"
                        class="form-control"
                        required>

                    <span onclick="togglePassword('current_password', this)"
                        style="position:absolute; right:15px; top:42px; cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <!-- NEW PASSWORD -->
                <div class="form-group" style="position:relative;">
                    <label><i class="fas fa-key"></i> New Password</label>

                    <input type="password"
                        name="new_password"
                        id="new_password"
                        class="form-control"
                        required>

                    <span onclick="togglePassword('new_password', this)"
                        style="position:absolute; right:15px; top:42px; cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <!-- CONFIRM PASSWORD -->
                <div class="form-group" style="position:relative;">
                    <label><i class="fas fa-check-circle"></i> Confirm New Password</label>

                    <input type="password"
                        name="confirm_password"
                        id="confirm_password"
                        class="form-control"
                        required>

                    <span onclick="togglePassword('confirm_password', this)"
                        style="position:absolute; right:15px; top:42px; cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <button type="submit"
                    class="btn-primary"
                    style="width:100%; background:#479ace;">

                    <i class="fas fa-key"></i> Change Password
                </button>

            </form>

        </div>

        <div class="modal-footer">
            <button class="btn-danger" onclick="closeProfileModal()">Close</button>
        </div>

    </div>
</div>

<script>
function togglePassword(fieldId, icon) {

    var input = document.getElementById(fieldId);
    var eyeIcon = icon.querySelector("i");

    if (input.type === "password") {
        input.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    }
}
</script>