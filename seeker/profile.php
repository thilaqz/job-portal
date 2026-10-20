<?php
/**
 * seeker/profile.php - Edit profile (name, phone, address, skills, photo)
 * and change password. Two separate forms, told apart by the "action" field.
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');
$page_title = 'My Profile';
$uid = current_user_id();

$st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$st->execute([$uid]);
$user = $st->fetch();

$profile_errors  = [];
$password_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    // ---------- 1. Update profile ----------
    if ($action === 'profile') {
        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $skills  = trim($_POST['skills'] ?? '');

        if (strlen($name) < 2 || strlen($name) > 100) {
            $profile_errors[] = 'Name must be 2 to 100 characters.';
        }
        if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
            $profile_errors[] = 'Phone number can contain only digits, spaces, + and - (7 to 20 characters).';
        }
        if (strlen($address) > 255) {
            $profile_errors[] = 'Address must be under 255 characters.';
        }
        if (strlen($skills) > 1000) {
            $profile_errors[] = 'Skills must be under 1000 characters.';
        }

        // Optional photo (JPG / PNG, max 1 MB)
        $photo = $user['photo'];
        if (empty($profile_errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $up = upload_file(
                $_FILES['photo'],
                ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']],
                1024 * 1024,
                PHOTO_DIR,
                'photo_' . $uid . '_'
            );
            if ($up['ok']) {
                // delete old photo file
                if ($photo && is_file(PHOTO_DIR . $photo)) {
                    unlink(PHOTO_DIR . $photo);
                }
                $photo = $up['name'];
            } else {
                $profile_errors[] = 'Photo: ' . $up['error'];
            }
        }

        if (empty($profile_errors)) {
            $st = $pdo->prepare('UPDATE users SET name = ?, phone = ?, address = ?, skills = ?, photo = ? WHERE id = ?');
            $st->execute([$name, $phone ?: null, $address ?: null, $skills ?: null, $photo, $uid]);
            $_SESSION['user_name'] = $name;
            set_flash('success', 'Profile updated successfully.');
            redirect('/seeker/profile.php');
        }
        // keep typed values in the form
        $user = array_merge($user, ['name' => $name, 'phone' => $phone, 'address' => $address, 'skills' => $skills]);
    }

    // ---------- 2. Change password ----------
    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $password_errors[] = 'Current password is incorrect.';
        }
        if (strlen($new) < 6) {
            $password_errors[] = 'New password must be at least 6 characters.';
        }
        if ($new !== $confirm) {
            $password_errors[] = 'New passwords do not match.';
        }
        if (empty($password_errors)) {
            $st = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $st->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            set_flash('success', 'Password changed successfully.');
            redirect('/seeker/profile.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-4">My Profile</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title">Profile Details</h5>
                    <?php show_errors($profile_errors); ?>
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="profile">
                        <div class="text-center mb-3">
                            <?php if (!empty($user['photo']) && is_file(PHOTO_DIR . $user['photo'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/profile/<?= e($user['photo']) ?>" id="photoPreview" class="profile-photo" alt="Photo">
                            <?php else: ?>
                                <img src="" id="photoPreview" class="profile-photo d-none" alt="Photo">
                                <i class="bi bi-person-circle display-1 text-primary" id="photoIcon"></i>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= e($user['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" maxlength="255" value="<?= e($user['address']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Skills <small class="text-muted">(comma separated)</small></label>
                            <textarea name="skills" class="form-control" rows="3"><?= e($user['skills']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Photo <small class="text-muted">(optional, JPG/PNG, max 1 MB)</small></label>
                            <input type="file" name="photo" id="photoInput" class="form-control" accept=".jpg,.jpeg,.png">
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-save"></i> Save Profile</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title">Change Password</h5>
                    <?php show_errors($password_errors); ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="password">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                        </div>
                        <button class="btn btn-outline-primary"><i class="bi bi-key"></i> Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
