<?php
/**
 * admin/users.php - View and delete job seekers and employers.
 * ?role=seeker (default) or ?role=employer
 * Deleting a user also deletes their jobs/applications (ON DELETE CASCADE).
 */
require_once __DIR__ . '/../config/db.php';
require_admin();
$page_title = 'Manage Users';
$admin_area = true;

$role = ($_GET['role'] ?? ($_POST['role'] ?? 'seeker')) === 'employer' ? 'employer' : 'seeker';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $user_id = (int) ($_POST['user_id'] ?? 0);

    // Collect uploaded files so we can remove them from disk after deleting
    $files = [];
    $st = $pdo->prepare('SELECT photo FROM users WHERE id = ?');
    $st->execute([$user_id]);
    $photo = $st->fetchColumn();
    if ($photo) {
        $files[] = PHOTO_DIR . $photo;
    }
    $st = $pdo->prepare('SELECT resume_file FROM applications WHERE user_id = ? UNION SELECT file_name FROM resumes WHERE user_id = ?');
    $st->execute([$user_id, $user_id]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $f) {
        $files[] = RESUME_DIR . basename($f);
    }

    $st = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = ?');
    $st->execute([$user_id, $role]);
    if ($st->rowCount() > 0) {
        foreach ($files as $f) {
            if (is_file($f)) {
                unlink($f);
            }
        }
        set_flash('success', 'User deleted.');
    } else {
        set_flash('danger', 'User not found.');
    }
    redirect('/admin/users.php?role=' . $role);
}

if ($role === 'employer') {
    $st = $pdo->prepare(
        "SELECT u.*, (SELECT COUNT(*) FROM jobs j WHERE j.employer_id = u.id) AS extra
         FROM users u WHERE u.role = 'employer' ORDER BY u.created_at DESC"
    );
} else {
    $st = $pdo->prepare(
        "SELECT u.*, (SELECT COUNT(*) FROM applications a WHERE a.user_id = u.id) AS extra
         FROM users u WHERE u.role = 'seeker' ORDER BY u.created_at DESC"
    );
}
$st->execute();
$users = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-3">Manage Users</h1>
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a class="nav-link <?= $role === 'seeker' ? 'active' : '' ?>" href="?role=seeker">Job Seekers</a></li>
        <li class="nav-item"><a class="nav-link <?= $role === 'employer' ? 'active' : '' ?>" href="?role=employer">Employers</a></li>
    </ul>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="alert alert-info mb-0">No <?= $role === 'employer' ? 'employers' : 'job seekers' ?> registered yet.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Name</th><th>Email</th>
                            <?php if ($role === 'employer'): ?><th>Company</th><?php else: ?><th>Skills</th><?php endif; ?>
                            <th>Phone</th><th><?= $role === 'employer' ? 'Jobs' : 'Applications' ?></th><th>Joined</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int) $u['id'] ?></td>
                            <td><?= e($u['name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($role === 'employer' ? $u['company_name'] : $u['skills']) ?></td>
                            <td><?= e($u['phone']) ?></td>
                            <td><?= (int) $u['extra'] ?></td>
                            <td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
                            <td class="text-end">
                                <form method="post" onsubmit="return confirm('Delete this user and all their data?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="role" value="<?= e($role) ?>">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
