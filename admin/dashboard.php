<?php
/**
 * admin/dashboard.php - Site-wide counts + latest pending jobs.
 */
require_once __DIR__ . '/../config/db.php';
require_admin();
$page_title = 'Admin Dashboard';
$admin_area = true;

$stats = [
    'seekers'      => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seeker'")->fetchColumn(),
    'employers'    => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn(),
    'jobs'         => (int) $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn(),
    'applications' => (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
    'pending'      => (int) $pdo->query('SELECT COUNT(*) FROM jobs WHERE status = 0')->fetchColumn(),
];

$pending_jobs = $pdo->query(
    'SELECT j.id, j.title, j.created_at, COALESCE(u.company_name, u.name) AS company
     FROM jobs j JOIN users u ON u.id = j.employer_id
     WHERE j.status = 0 ORDER BY j.created_at ASC LIMIT 5'
)->fetchAll();

$cards = [
    ['Job Seekers', $stats['seekers'], 'bi-people', 'primary', 'users.php?role=seeker'],
    ['Employers', $stats['employers'], 'bi-building', 'info', 'users.php?role=employer'],
    ['Total Jobs', $stats['jobs'], 'bi-briefcase', 'success', 'jobs.php'],
    ['Applications', $stats['applications'], 'bi-send', 'secondary', 'jobs.php'],
    ['Pending Jobs', $stats['pending'], 'bi-hourglass-split', 'warning', 'jobs.php?status=0'],
];

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-4">Admin Dashboard</h1>
    <div class="row g-3 mb-4">
        <?php foreach ($cards as $c): ?>
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= e($c[4]) ?>" class="card stat-card border-0 shadow-sm text-decoration-none h-100">
                    <div class="card-body">
                        <i class="bi <?= $c[2] ?> fs-3 text-<?= $c[3] ?>"></i>
                        <div class="stat-number text-dark"><?= $c[1] ?></div>
                        <div class="text-muted small"><?= e($c[0]) ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Jobs Waiting for Approval</h5>
                <a href="jobs.php" class="btn btn-sm btn-outline-primary">Manage Jobs</a>
            </div>
            <?php if (empty($pending_jobs)): ?>
                <div class="alert alert-success mb-0">No pending jobs. All caught up!</div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pending_jobs as $j): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong><?= e($j['title']) ?></strong> <small class="text-muted">by <?= e($j['company']) ?></small></span>
                            <small class="text-muted"><?= e(date('d M Y', strtotime($j['created_at']))) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
