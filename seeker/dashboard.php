<?php
/**
 * seeker/dashboard.php - Job seeker home: quick stats + profile summary.
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');
$page_title = 'Job Seeker Dashboard';
$uid = current_user_id();

$st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$st->execute([$uid]);
$user = $st->fetch();

// Count applications by status
$st = $pdo->prepare('SELECT status, COUNT(*) AS total FROM applications WHERE user_id = ? GROUP BY status');
$st->execute([$uid]);
$counts = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
foreach ($st->fetchAll() as $row) {
    $counts[(int) $row['status']] = (int) $row['total'];
}
$total_apps = $counts[0] + $counts[1] + $counts[2];   // withdrawn not counted

$st = $pdo->prepare('SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?');
$st->execute([$uid]);
$saved_count = (int) $st->fetchColumn();

$st = $pdo->prepare('SELECT original_name, uploaded_at FROM resumes WHERE user_id = ?');
$st->execute([$uid]);
$resume = $st->fetch();

// 5 most recent applications
$st = $pdo->prepare(
    'SELECT a.status, a.applied_at, j.id AS job_id, j.title
     FROM applications a JOIN jobs j ON j.id = a.job_id
     WHERE a.user_id = ? ORDER BY a.applied_at DESC LIMIT 5'
);
$st->execute([$uid]);
$recent = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-4">Welcome, <?= e($user['name']) ?></h1>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number"><?= $total_apps ?></div><div class="text-muted small">Applications</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number text-warning"><?= $counts[0] ?></div><div class="text-muted small">Pending</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number text-success"><?= $counts[1] ?></div><div class="text-muted small">Shortlisted</div></div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card border-0 shadow-sm"><div class="card-body">
            <div class="stat-number text-info"><?= $saved_count ?></div><div class="text-muted small">Saved Jobs</div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <?php if (!empty($user['photo']) && is_file(PHOTO_DIR . $user['photo'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/profile/<?= e($user['photo']) ?>" class="profile-photo mb-2" alt="Photo">
                    <?php else: ?>
                        <i class="bi bi-person-circle display-1 text-primary"></i>
                    <?php endif; ?>
                    <h5 class="mb-0"><?= e($user['name']) ?></h5>
                    <p class="text-muted small"><?= e($user['email']) ?></p>
                    <p class="small mb-1"><i class="bi bi-telephone"></i> <?= e($user['phone'] ?: 'Not added') ?></p>
                    <p class="small"><i class="bi bi-geo-alt"></i> <?= e($user['address'] ?: 'Not added') ?></p>
                    <p class="small"><strong>Skills:</strong> <?= e($user['skills'] ?: 'Not added') ?></p>
                    <a href="profile.php" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit Profile</a>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6><i class="bi bi-file-earmark-pdf text-danger"></i> Resume</h6>
                    <?php if ($resume): ?>
                        <p class="small mb-2"><?= e($resume['original_name']) ?><br>
                            <span class="text-muted">Uploaded <?= e(date('d M Y', strtotime($resume['uploaded_at']))) ?></span></p>
                        <a href="upload_resume.php" class="btn btn-outline-primary btn-sm">Manage Resume</a>
                    <?php else: ?>
                        <div class="alert alert-warning small mb-2">No resume uploaded. You need one to apply.</div>
                        <a href="upload_resume.php" class="btn btn-warning btn-sm">Upload Resume</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Recent Applications</h5>
                        <a href="applications.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                    <?php if (empty($recent)): ?>
                        <div class="alert alert-info mb-0">You have not applied for any jobs yet.
                            <a href="<?= BASE_URL ?>/jobs/list.php">Browse jobs</a>.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Job</th><th>Applied</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($recent as $r): ?>
                                    <tr>
                                        <td><a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $r['job_id'] ?>"><?= e($r['title']) ?></a></td>
                                        <td><?= e(date('d M Y', strtotime($r['applied_at']))) ?></td>
                                        <td><?= app_status_badge($r['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
