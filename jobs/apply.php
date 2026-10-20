<?php
/**
 * jobs/apply.php?id=X - Job seeker applies with their resume + cover letter.
 * One application per job per user (UNIQUE key in DB + check here).
 * A withdrawn application can be submitted again.
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');
$page_title = 'Apply for Job';

$user_id = current_user_id();
$job_id  = (int) ($_GET['id'] ?? ($_POST['job_id'] ?? 0));

// Job must exist, be approved and still open
$st = $pdo->prepare(
    'SELECT j.id, j.title, j.deadline, COALESCE(u.company_name, u.name) AS company
     FROM jobs j JOIN users u ON u.id = j.employer_id
     WHERE j.id = ? AND j.status = 1'
);
$st->execute([$job_id]);
$job = $st->fetch();
if (!$job) {
    set_flash('warning', 'Job not found.');
    redirect('/jobs/list.php');
}
if ($job['deadline'] < date('Y-m-d')) {
    set_flash('warning', 'Sorry, the deadline for this job has passed.');
    redirect('/jobs/details.php?id=' . $job_id);
}

// Seeker must have a resume
$st = $pdo->prepare('SELECT file_name, original_name FROM resumes WHERE user_id = ?');
$st->execute([$user_id]);
$resume = $st->fetch();
if (!$resume) {
    set_flash('warning', 'Please upload your resume before applying.');
    redirect('/seeker/upload_resume.php');
}

// Already applied?
$st = $pdo->prepare('SELECT id, status FROM applications WHERE user_id = ? AND job_id = ?');
$st->execute([$user_id, $job_id]);
$existing = $st->fetch();
if ($existing && (int) $existing['status'] !== 3) {
    set_flash('info', 'You have already applied for this job.');
    redirect('/seeker/applications.php');
}

$errors       = [];
$cover_letter = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $cover_letter = trim($_POST['cover_letter'] ?? '');

    if (strlen($cover_letter) < 20) {
        $errors[] = 'Cover letter must be at least 20 characters.';
    } elseif (strlen($cover_letter) > 2000) {
        $errors[] = 'Cover letter must be under 2000 characters.';
    }

    if (empty($errors)) {
        if ($existing) {
            // Re-apply after withdrawing: reuse the same row
            $st = $pdo->prepare('UPDATE applications SET resume_file = ?, cover_letter = ?, status = 0, applied_at = NOW() WHERE id = ? AND user_id = ?');
            $st->execute([$resume['file_name'], $cover_letter, $existing['id'], $user_id]);
        } else {
            try {
                $st = $pdo->prepare('INSERT INTO applications (user_id, job_id, resume_file, cover_letter) VALUES (?, ?, ?, ?)');
                $st->execute([$user_id, $job_id, $resume['file_name'], $cover_letter]);
            } catch (PDOException $ex) {
                // 23000 = duplicate key (user double-clicked submit)
                if ($ex->getCode() === '23000') {
                    set_flash('info', 'You have already applied for this job.');
                    redirect('/seeker/applications.php');
                }
                throw $ex;
            }
        }
        set_flash('success', 'Application submitted for "' . $job['title'] . '". Good luck!');
        redirect('/seeker/applications.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4">Apply for: <?= e($job['title']) ?></h2>
                    <p class="text-muted"><i class="bi bi-building"></i> <?= e($job['company']) ?></p>
                    <?php show_errors($errors); ?>

                    <div class="alert alert-light border d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-file-earmark-pdf text-danger"></i> Resume: <strong><?= e($resume['original_name']) ?></strong></span>
                        <a href="<?= BASE_URL ?>/seeker/upload_resume.php" class="small">Change</a>
                    </div>

                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="job_id" value="<?= (int) $job_id ?>">
                        <div class="mb-3">
                            <label class="form-label">Cover Letter *</label>
                            <textarea name="cover_letter" class="form-control" rows="7" maxlength="2000" required
                                      placeholder="Briefly tell the employer why you are a good fit for this role..."><?= e($cover_letter) ?></textarea>
                            <div class="form-text">20 - 2000 characters.</div>
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-send"></i> Submit Application</button>
                        <a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $job_id ?>" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
