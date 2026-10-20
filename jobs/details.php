<?php
/**
 * jobs/details.php?id=X - Full job information + Apply / Save buttons.
 */
require_once __DIR__ . '/../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
$st = $pdo->prepare(
    'SELECT j.*, c.name AS category_name, r.name AS region_name,
            COALESCE(u.company_name, u.name) AS company, u.email AS employer_email
     FROM jobs j
     JOIN categories c ON c.id = j.category_id
     JOIN regions r    ON r.id = j.region_id
     JOIN users u      ON u.id = j.employer_id
     WHERE j.id = ?'
);
$st->execute([$id]);
$job = $st->fetch();

// Only approved jobs are public. The owner employer and admin can preview any status.
$is_owner = $job && is_logged_in() && current_role() === 'employer' && (int) $job['employer_id'] === current_user_id();
if (!$job || ((int) $job['status'] !== 1 && !$is_owner && !is_admin())) {
    set_flash('warning', 'Job not found or not available.');
    redirect('/jobs/list.php');
}

$page_title = $job['title'];
$is_closed  = $job['deadline'] < date('Y-m-d');

// Extra info for logged-in job seekers
$application = null;
$is_saved    = false;
$has_resume  = false;
if (is_logged_in() && current_role() === 'seeker') {
    $st = $pdo->prepare('SELECT status FROM applications WHERE user_id = ? AND job_id = ?');
    $st->execute([current_user_id(), $id]);
    $application = $st->fetch();

    $st = $pdo->prepare('SELECT COUNT(*) FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $st->execute([current_user_id(), $id]);
    $is_saved = (bool) $st->fetchColumn();

    $st = $pdo->prepare('SELECT COUNT(*) FROM resumes WHERE user_id = ?');
    $st->execute([current_user_id()]);
    $has_resume = (bool) $st->fetchColumn();
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-banner text-white">
    <div class="container py-4">
        <span class="badge bg-light text-primary mb-2"><?= e($job['category_name']) ?></span>
        <h1 class="h2 mb-1"><?= e($job['title']) ?></h1>
        <p class="mb-0"><i class="bi bi-building"></i> <?= e($job['company']) ?> &nbsp; <i class="bi bi-geo-alt"></i> <?= e($job['region_name']) ?></p>
    </div>
</div>

<div class="container mt-4">
    <?php if ((int) $job['status'] !== 1): ?>
        <div class="alert alert-warning">Preview: this job is currently <?= job_status_badge($job['status']) ?> and is not visible to the public.</div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title">Job Description</h5>
                    <p class="mb-0"><?= nl2br(e($job['description'])) ?></p>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="section-title">Requirements</h5>
                    <ul class="mb-0">
                        <?php foreach (preg_split('/\r\n|\r|\n/', $job['requirements']) as $line): ?>
                            <?php if (trim($line) !== ''): ?><li><?= e(trim($line)) ?></li><?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">Job Overview</h5>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between"><span>Job Type</span><strong><?= e($job['job_type']) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Salary</span><strong><?= e($job['salary']) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Vacancies</span><strong><?= (int) $job['vacancies'] ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Experience</span><strong><?= e($job['experience']) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Location</span><strong><?= e($job['region_name']) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Posted On</span><strong><?= e(date('d M Y', strtotime($job['created_at']))) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Deadline</span>
                            <strong class="<?= $is_closed ? 'text-danger' : '' ?>"><?= e(date('d M Y', strtotime($job['deadline']))) ?></strong></li>
                    </ul>
                </div>
            </div>

            <!-- Apply box -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if ($is_closed): ?>
                        <div class="alert alert-secondary mb-0">Applications for this job are closed.</div>

                    <?php elseif (!is_logged_in()): ?>
                        <p class="small text-muted">Please login as a Job Seeker to apply for this job.</p>
                        <a href="<?= BASE_URL ?>/auth/login.php?redirect=<?= urlencode('/jobs/details.php?id=' . $id) ?>" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login to Apply Now</a>
                        <a href="<?= BASE_URL ?>/auth/register.php?role=seeker" class="btn btn-outline-primary w-100">Create Free Account</a>

                    <?php elseif (current_role() === 'employer'): ?>
                        <div class="alert alert-info mb-0 small">You are logged in as an employer. Only job seekers can apply.</div>

                    <?php elseif ($application && (int) $application['status'] !== 3): ?>
                        <div class="alert alert-success mb-2">You have applied for this job. Status: <?= app_status_badge($application['status']) ?></div>
                        <a href="<?= BASE_URL ?>/seeker/applications.php" class="btn btn-outline-primary w-100">My Applications</a>

                    <?php elseif (!$has_resume): ?>
                        <p class="small text-muted">Upload your resume (PDF) before applying.</p>
                        <a href="<?= BASE_URL ?>/seeker/upload_resume.php" class="btn btn-warning w-100"><i class="bi bi-upload"></i> Upload Resume</a>

                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/jobs/apply.php?id=<?= $id ?>" class="btn btn-primary btn-lg w-100"><i class="bi bi-send"></i> Apply Now</a>
                    <?php endif; ?>

                    <?php if (is_logged_in() && current_role() === 'seeker'): ?>
                        <form method="post" action="<?= BASE_URL ?>/jobs/save.php" class="mt-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="job_id" value="<?= $id ?>">
                            <input type="hidden" name="return" value="/jobs/details.php?id=<?= $id ?>">
                            <button class="btn btn-outline-secondary w-100">
                                <i class="bi <?= $is_saved ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i> <?= $is_saved ? 'Unsave Job' : 'Save Job' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
