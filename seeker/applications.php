<?php
/**
 * seeker/applications.php - List of my applications with status + Withdraw.
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');
$page_title = 'My Applications';
$uid = current_user_id();

// Withdraw (POST). Only own, still-pending applications.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $app_id = (int) ($_POST['application_id'] ?? 0);
    $st = $pdo->prepare('UPDATE applications SET status = 3 WHERE id = ? AND user_id = ? AND status = 0');
    $st->execute([$app_id, $uid]);
    if ($st->rowCount() > 0) {
        set_flash('success', 'Application withdrawn.');
    } else {
        set_flash('danger', 'Only your own pending applications can be withdrawn.');
    }
    redirect('/seeker/applications.php');
}

$st = $pdo->prepare(
    'SELECT a.id, a.status, a.applied_at, j.id AS job_id, j.title, j.job_type, j.status AS job_status,
            r.name AS region_name, COALESCE(u.company_name, u.name) AS company
     FROM applications a
     JOIN jobs j    ON j.id = a.job_id
     JOIN regions r ON r.id = j.region_id
     JOIN users u   ON u.id = j.employer_id
     WHERE a.user_id = ?
     ORDER BY a.applied_at DESC'
);
$st->execute([$uid]);
$apps = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-4">My Applications</h1>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($apps)): ?>
                <div class="alert alert-info mb-0">No applications yet. <a href="<?= BASE_URL ?>/jobs/list.php">Find a job</a> and apply!</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Job</th><th>Company</th><th>Location</th><th>Applied On</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($apps as $i => $a): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?php if ((int) $a['job_status'] === 1): ?>
                                        <a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $a['job_id'] ?>"><?= e($a['title']) ?></a>
                                    <?php else: ?>
                                        <?= e($a['title']) ?> <small class="text-muted">(no longer listed)</small>
                                    <?php endif; ?>
                                    <br><small class="text-muted"><?= e($a['job_type']) ?></small>
                                </td>
                                <td><?= e($a['company']) ?></td>
                                <td><?= e($a['region_name']) ?></td>
                                <td><?= e(date('d M Y', strtotime($a['applied_at']))) ?></td>
                                <td><?= app_status_badge($a['status']) ?></td>
                                <td>
                                    <?php if ((int) $a['status'] === 0): ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Withdraw this application?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="application_id" value="<?= (int) $a['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Withdraw</button>
                                        </form>
                                    <?php elseif ((int) $a['status'] === 3 && (int) $a['job_status'] === 1): ?>
                                        <a href="<?= BASE_URL ?>/jobs/apply.php?id=<?= (int) $a['job_id'] ?>" class="btn btn-sm btn-outline-primary">Re-apply</a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
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
