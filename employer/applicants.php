<?php
/**
 * employer/applicants.php?job_id=X - Applicants for one of MY jobs.
 *   POST status change   -> Shortlisted (1) / Rejected (2)
 *   ?download=APP_ID     -> download that applicant's resume
 */
require_once __DIR__ . '/../config/db.php';
require_role('employer');
$page_title = 'Applicants';
$uid = current_user_id();

// ---------- Resume download (ownership checked through the job) ----------
if (isset($_GET['download'])) {
    $st = $pdo->prepare(
        'SELECT a.resume_file, u.name
         FROM applications a
         JOIN jobs j  ON j.id = a.job_id
         JOIN users u ON u.id = a.user_id
         WHERE a.id = ? AND j.employer_id = ?'
    );
    $st->execute([(int) $_GET['download'], $uid]);
    $row = $st->fetch();
    if (!$row) {
        set_flash('danger', 'Resume not found or access denied.');
        redirect('/employer/my_jobs.php');
    }
    send_pdf(RESUME_DIR . basename($row['resume_file']), 'Resume_' . $row['name'] . '.pdf');
}

$job_id = (int) ($_GET['job_id'] ?? ($_POST['job_id'] ?? 0));

// The job must belong to this employer
$st = $pdo->prepare('SELECT id, title, status FROM jobs WHERE id = ? AND employer_id = ?');
$st->execute([$job_id, $uid]);
$job = $st->fetch();
if (!$job) {
    set_flash('danger', 'Job not found or access denied.');
    redirect('/employer/my_jobs.php');
}

// ---------- Change status ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $app_id = (int) ($_POST['application_id'] ?? 0);
    $status = (int) ($_POST['status'] ?? -1);
    if (in_array($status, [1, 2], true)) {
        // Withdrawn applications (3) cannot be changed
        $st = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ? AND job_id = ? AND status <> 3');
        $st->execute([$status, $app_id, $job_id]);
        set_flash('success', $st->rowCount() > 0
            ? 'Applicant marked as ' . ($status === 1 ? 'Shortlisted' : 'Rejected') . '.'
            : 'No change made.');
    } else {
        set_flash('danger', 'Invalid status.');
    }
    redirect('/employer/applicants.php?job_id=' . $job_id);
}

// ---------- List ----------
$st = $pdo->prepare(
    'SELECT a.id, a.cover_letter, a.status, a.applied_at,
            u.name, u.email, u.phone, u.skills
     FROM applications a JOIN users u ON u.id = a.user_id
     WHERE a.job_id = ?
     ORDER BY a.status = 3, a.applied_at DESC'
);
$st->execute([$job_id]);
$applicants = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <a href="my_jobs.php" class="small"><i class="bi bi-arrow-left"></i> Back to My Jobs</a>
    <h1 class="h3 mt-2">Applicants for: <?= e($job['title']) ?></h1>
    <p class="text-muted">Job status: <?= job_status_badge($job['status']) ?> &middot; <?= count($applicants) ?> application(s)</p>

    <?php if (empty($applicants)): ?>
        <div class="alert alert-info">No one has applied for this job yet.</div>
    <?php endif; ?>

    <div class="row g-3">
    <?php foreach ($applicants as $a): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h5 class="mb-1"><?= e($a['name']) ?> <?= app_status_badge($a['status']) ?></h5>
                            <p class="small text-muted mb-1">
                                <i class="bi bi-envelope"></i> <?= e($a['email']) ?>
                                <?php if ($a['phone']): ?> &middot; <i class="bi bi-telephone"></i> <?= e($a['phone']) ?><?php endif; ?>
                                &middot; Applied <?= e(date('d M Y', strtotime($a['applied_at']))) ?>
                            </p>
                            <?php if ($a['skills']): ?><p class="small mb-1"><strong>Skills:</strong> <?= e($a['skills']) ?></p><?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="?download=<?= (int) $a['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Resume</a>
                            <?php if ((int) $a['status'] !== 3): ?>
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="job_id" value="<?= (int) $job_id ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $a['id'] ?>">
                                    <button name="status" value="1" class="btn btn-sm btn-success" <?= (int) $a['status'] === 1 ? 'disabled' : '' ?>><i class="bi bi-check-circle"></i> Shortlist</button>
                                    <button name="status" value="2" class="btn btn-sm btn-danger" <?= (int) $a['status'] === 2 ? 'disabled' : '' ?>><i class="bi bi-x-circle"></i> Reject</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <details class="mt-2">
                        <summary class="small text-primary">Cover letter</summary>
                        <p class="small mt-2 mb-0"><?= nl2br(e($a['cover_letter'])) ?></p>
                    </details>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
