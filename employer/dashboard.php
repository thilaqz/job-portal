<?php
/**
 * employer/dashboard.php - Counts of posted jobs, pending jobs and applicants.
 */
require_once __DIR__ . '/../config/db.php';
require_role('employer');
$page_title = 'Employer Dashboard';
$uid = current_user_id();

$st = $pdo->prepare(
    'SELECT COUNT(*) AS total,
            COALESCE(SUM(status = 0), 0) AS pending,
            COALESCE(SUM(status = 1), 0) AS approved,
            COALESCE(SUM(status = 2), 0) AS rejected
     FROM jobs WHERE employer_id = ?'
);
$st->execute([$uid]);
$jobs_count = $st->fetch();

// Applicants on my jobs (withdrawn not counted)
$st = $pdo->prepare(
    'SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id
     WHERE j.employer_id = ? AND a.status <> 3'
);
$st->execute([$uid]);
$total_applicants = (int) $st->fetchColumn();

// Latest 5 applicants
$st = $pdo->prepare(
    'SELECT a.status, a.applied_at, u.name AS seeker_name, j.id AS job_id, j.title
     FROM applications a
     JOIN jobs j  ON j.id = a.job_id
     JOIN users u ON u.id = a.user_id
     WHERE j.employer_id = ? AND a.status <> 3
     ORDER BY a.applied_at DESC LIMIT 5'
);
$st->execute([$uid]);
$recent = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Employer Dashboard</h1>
        <a href="<?= BASE_URL ?>/jobs/post.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Post New Job</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number"><?= (int) $jobs_count['total'] ?></div><div class="text-muted small">Posted Jobs</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number text-warning"><?= (int) $jobs_count['pending'] ?></div><div class="text-muted small">Pending Approval</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number text-success"><?= (int) $jobs_count['approved'] ?></div><div class="text-muted small">Live Jobs</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number text-info"><?= $total_applicants ?></div><div class="text-muted small">Total Applicants</div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recent Applicants</h5>
                <a href="my_jobs.php" class="btn btn-outline-primary btn-sm">Manage My Jobs</a>
            </div>
            <?php if (empty($recent)): ?>
                <div class="alert alert-info mb-0">No applications received yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Applicant</th><th>Job</th><th>Applied</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><?= e($r['seeker_name']) ?></td>
                                <td><?= e($r['title']) ?></td>
                                <td><?= e(date('d M Y', strtotime($r['applied_at']))) ?></td>
                                <td><?= app_status_badge($r['status']) ?></td>
                                <td><a href="applicants.php?job_id=<?= (int) $r['job_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
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
