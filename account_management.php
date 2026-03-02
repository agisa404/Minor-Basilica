<?php
require_once __DIR__ . '/layout.php';
$user = current_user();

if ($user && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? $user['role']);
    $bio = trim($_POST['bio'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $avatarPath = $user['avatar_path'] ?? null;

    if ($fullName === '' || $email === '') {
        set_flash('danger', 'Full name and email are required.');
        header('Location: account_management.php?edit=1');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Please enter a valid email address.');
        header('Location: account_management.php?edit=1');
        exit();
    }

    $allowedRoles = ['user', 'minister', 'priest', 'staff', 'admin'];
    if (!in_array($role, $allowedRoles, true)) {
        $role = 'user';
    }
    if (($user['role'] ?? '') !== 'admin') {
        $role = $user['role'] ?? 'user';
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            set_flash('danger', 'Avatar upload failed. Please try again.');
            header('Location: account_management.php?edit=1');
            exit();
        }

        $tmpPath = $_FILES['avatar']['tmp_name'] ?? '';
        $originalName = $_FILES['avatar']['name'] ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpPath) ?: '';
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($extension, $allowedExt, true) || !in_array($mimeType, $allowedMime, true)) {
            set_flash('danger', 'Invalid avatar file type. Please upload an image.');
            header('Location: account_management.php?edit=1');
            exit();
        }

        if (!is_dir(__DIR__ . '/uploads/avatars')) {
            mkdir(__DIR__ . '/uploads/avatars', 0777, true);
        }

        $fileName = 'avatar_' . $user['id'] . '_' . time() . '.' . $extension;
        $targetPath = __DIR__ . '/uploads/avatars/' . $fileName;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            set_flash('danger', 'Unable to save avatar image.');
            header('Location: account_management.php?edit=1');
            exit();
        }

        $avatarPath = 'uploads/avatars/' . $fileName;
    }

    $emailCheck = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $emailCheck->bind_param('si', $email, $user['id']);
    $emailCheck->execute();
    $emailTaken = $emailCheck->get_result()->num_rows > 0;
    $emailCheck->close();
    if ($emailTaken) {
        set_flash('danger', 'That email is already used by another account.');
        header('Location: account_management.php?edit=1');
        exit();
    }

    $stmt = $conn->prepare('UPDATE users SET full_name = ?, email = ?, role = ?, avatar_path = ?, bio = ?, about = ? WHERE id = ?');
    $stmt->bind_param('ssssssi', $fullName, $email, $role, $avatarPath, $bio, $about, $user['id']);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        set_flash('danger', 'Unable to save profile right now. Please try again.');
        header('Location: account_management.php?edit=1');
        exit();
    }

    $_SESSION['user_role'] = $role;
    set_flash('success', 'Account profile updated.');
    header('Location: account_management.php');
    exit();
}

if ($user && isset($_POST['mark_read'])) {
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Notifications marked as read.');
    header('Location: account_management.php');
    exit();
}

$notifications = [];
if ($user) {
    $stmt = $conn->prepare('SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$displayName = $user ? ($user['full_name'] ?? '') : '';
$displayName = trim($displayName) !== '' ? $displayName : ($user ? ($user['email'] ?? 'Member') : 'Member');
$aboutText = ($user && trim((string)($user['about'] ?? '')) !== '') ? $user['about'] : 'There is currently no information about this member.';
$bioText = ($user && trim((string)($user['bio'] ?? '')) !== '') ? $user['bio'] : 'Minor Basilica Information Management System';
$joinedDate = ($user && !empty($user['created_at'])) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A';
$lastActivity = !empty($notifications)
    ? date('M d, Y h:i A', strtotime($notifications[0]['created_at']))
    : 'No recent activity';
$avatarInitial = strtoupper(substr($displayName, 0, 1));
$authMode = ($_GET['auth'] ?? '') === 'register' ? 'register' : 'login';
$openEditPanel = isset($_GET['edit']);

render_header('Account Management', 'account');
?>
<h2 class="mb-3">Account</h2>

<?php if (!$user): ?>
    <div class="auth-shell">
        <div class="auth-card card border-warning-subtle">
            <div class="card-body p-4 p-md-5">
                <div class="auth-head mb-4">
                    <?php if ($authMode === 'register'): ?>
                        <h5 class="card-title text-warning mb-1">Create Account</h5>
                        <p class="auth-subtitle mb-0">Sign up to access the full parish system modules.</p>
                        <form method="POST" action="register.php">
                            <label class="form-label">Full Name</label>
                            <input class="form-control mb-2" type="text" name="full_name" required>
                            <label class="form-label">Email</label>
                            <input class="form-control mb-2" type="email" name="email" required>
                            <label class="form-label">Password</label>
                            <input class="form-control mb-2" type="password" name="password" required>
                            <label class="form-label">Confirm Password</label>
                            <input class="form-control mb-2" type="password" name="confirm_password" required>
                            <label class="form-label">Role</label>
                            <select class="form-select mb-3" name="role" required>
                                <option value="user">User</option>
                                <option value="minister">Minister</option>
                                <option value="priest">Priest</option>
                                <option value="staff">Church Staff</option>
                            </select>
                            <div class="d-flex gap-2 auth-actions">
                                <button class="btn btn-warning" type="submit">Register</button>
                                <a class="btn btn-outline-light" href="account_management.php?auth=login">Back to Login</a>
                                <a class="btn btn-outline-light" href="index.php">Back to Homepage</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h5 class="card-title text-warning mb-1">LOG IN</h5>
                        <p class="auth-subtitle mb-0">Enter your account credentials to continue.</p>
                        <form method="POST" action="login.php">
                            <label class="form-label">Email</label>
                            <input class="form-control mb-3" type="email" name="email" required>
                            <label class="form-label">Password</label>
                            <input class="form-control mb-3" type="password" name="password" required>
                            <div class="d-flex gap-2 auth-actions">
                                <button class="btn btn-warning" type="submit">Login</button>
                                <a class="btn btn-outline-light" href="account_management.php?auth=register">Sign up</a>
                                <a class="btn btn-outline-light" href="index.php">Back to Homepage</a>
                            </div>

                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="account-modern">
        <section class="profile-hero mb-4">
            <div class="profile-cover"></div>
            <div class="profile-head p-3 p-md-4">
                <div class="profile-avatar-wrap">
                    <?php if (!empty($user['avatar_path'])): ?>
                        <img class="profile-avatar" src="<?php echo e($user['avatar_path']); ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="profile-avatar profile-avatar-fallback"><?php echo e($avatarInitial); ?></div>
                    <?php endif; ?>
                </div>
                <div class="profile-main">
                    <h3 class="mb-1"><?php echo e($displayName); ?></h3>
                    <p class="profile-sub mb-0"><?php echo e($bioText); ?></p>
                </div>
                <button class="btn btn-info profile-edit-btn" type="button" data-bs-toggle="collapse" data-bs-target="#editAccountPanel" aria-expanded="<?php echo $openEditPanel ? 'true' : 'false'; ?>" aria-controls="editAccountPanel">
                    Edit
                </button>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="account-panel p-3 p-md-4 mb-4">
                    <h4 class="mb-3">About</h4>
                    <p class="mb-0"><?php echo nl2br(e($aboutText)); ?></p>
                </section>

                <section class="account-panel p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Notifications</h5>
                        <form method="POST">
                            <input type="hidden" name="mark_read" value="1">
                            <button class="btn btn-sm btn-outline-light" type="submit">Mark all read</button>
                        </form>
                    </div>
                    <?php if (!$notifications): ?>
                        <p class="mb-0">No notifications yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $n): ?>
                                <div class="list-group-item bg-transparent border-secondary-subtle">
                                    <div class="small <?php echo $n['is_read'] ? 'text-secondary' : 'text-info'; ?>"><?php echo e($n['created_at']); ?></div>
                                    <div><?php echo e($n['message']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section id="editAccountPanel" class="account-panel p-3 p-md-4 mt-4 collapse<?php echo $openEditPanel ? ' show' : ''; ?>">
                    <h5 class="mb-3">Manage Account</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        <label class="form-label">Avatar</label>
                        <input class="form-control mb-2" type="file" name="avatar" accept="image/*">
                        <label class="form-label">Full Name</label>
                        <input class="form-control mb-2" type="text" name="full_name" value="<?php echo e($user['full_name'] ?? ''); ?>" required>
                        <label class="form-label">Email</label>
                        <input class="form-control mb-2" type="email" name="email" value="<?php echo e($user['email']); ?>" required>
                        <label class="form-label">Role</label>
                        <select class="form-select mb-3" name="role">
                            <?php
                            $editableRoles = ['user', 'minister', 'priest', 'staff'];
                            if (($user['role'] ?? '') === 'admin') {
                                $editableRoles[] = 'admin';
                            }
                            foreach ($editableRoles as $role):
                            ?>
                                <option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>><?php echo strtoupper($role); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="form-label">Bio</label>
                        <textarea class="form-control mb-2" name="bio" rows="3" placeholder="Short description"><?php echo e($user['bio'] ?? ''); ?></textarea>
                        <label class="form-label">About</label>
                        <textarea class="form-control mb-3" name="about" rows="4" placeholder="Tell more about yourself"><?php echo e($user['about'] ?? ''); ?></textarea>
                        <button class="btn btn-warning" type="submit">Save Changes</button>
                        <?php if (is_admin_or_staff($user)): ?>
                            <a class="btn btn-outline-light ms-2" href="admin_dashboard.php">Go to Admin Dashboard</a>
                        <?php endif; ?>
                    </form>
                </section>
            </div>

            <div class="col-lg-4">
                <section class="account-panel p-3 p-md-4">
                    <h4 class="mb-3">Account</h4>
                    <div class="account-line"><strong>Joined</strong><span><?php echo e($joinedDate); ?></span></div>
                    <div class="account-line"><strong>Last Activity</strong><span><?php echo e($lastActivity); ?></span></div>
                    <div class="account-line"><strong>Role</strong><span class="text-uppercase"><?php echo e($user['role']); ?></span></div>
                    <div class="account-actions mt-3">
                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#editAccountPanel" aria-expanded="false" aria-controls="editAccountPanel">Login Credentials</button>
                    </div>
                </section>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php render_footer(); ?>
