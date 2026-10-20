<?php
/**
 * seeker/saved.php - Jobs the seeker has bookmarked.
 * Unsave uses jobs/save.php (toggle).
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');
$page_title = 'Saved Jobs';

$st = $pdo->prepare(
    'SELECT s.saved_at, j.id, j.title, j.job_type, j.salary, j.deadline, j.status,
            r.name AS region_name, COALESCE(u.company_name, u.name) AS company
     FROM saved_jobs s
     JOIN jobs j    ON j.id = s.job_id
     JOIN regions r ON r.id = j.region_id
     JOIN users u   ON u.id = j.employer_id
     WHERE s.user_id = ?
     ORDER BY s.saved_at DESC'
);
$st->execute([current_user_id()]);
$saved = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-4">Saved Jobs</h1>
    <?php if (empty($saved)): ?>
        <div class="alert alert-info">You have no saved jobs. Click "Save Job" on any job page to bookmark it.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($saved as $j): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title mb-1"><?= e($j['title']) ?></h5>
                                <span class="badge bg-info-subtle text-info-emphasis align-self-start"><?= e($j['job_type']) ?></span>
                            </div>
                            <p class="text-muted small mb-2"><i class="bi bi-building"></i> <?= e($j['company']) ?> &middot;
                                <i class="bi bi-geo-alt"></i> <?= e($j['region_name']) ?></p>
                            <p class="small mb-2"><i class="bi bi-cash"></i> <?= e($j['salary']) ?> &middot;
                                <i class="bi bi-calendar-event"></i> Apply by <?= e(date('d M Y', strtotime($j['deadline']))) ?></p>
                            <?php if ((int) $j['status'] !== 1): ?>
                                <div class="alert alert-secondary small py-1 mb-2">This job is no longer listed.</div>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <?php if ((int) $j['status'] === 1): ?>
                                    <a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $j['id'] ?>" class="btn btn-sm btn-primary">View &amp; Apply</a>
                                <?php endif; ?>
                                <form method="post" action="<?= BASE_URL ?>/jobs/save.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>">
                                    <input type="hidden" name="return" value="/seeker/saved.php">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-bookmark-x"></i> Unsave</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
