<?php
/**
 * jobs/edit.php?id=X - Employer edits their OWN job.
 * After editing, the job goes back to Pending for admin re-approval.
 */
require_once __DIR__ . '/../config/db.php';
require_role('employer');
$page_title = 'Edit Job';

$id = (int) ($_GET['id'] ?? 0);

// Ownership check: job must belong to the logged-in employer
$st = $pdo->prepare('SELECT * FROM jobs WHERE id = ? AND employer_id = ?');
$st->execute([$id, current_user_id()]);
$job = $st->fetch();
if (!$job) {
    set_flash('danger', 'Job not found or you do not have permission to edit it.');
    redirect('/employer/my_jobs.php');
}

$categories = get_categories($pdo);
$regions    = get_regions($pdo);
$errors     = [];
$form       = $job;   // pre-fill with current values

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    list($form, $errors) = validate_job_form($pdo, $_POST);

    if (empty($errors)) {
        $st = $pdo->prepare(
            'UPDATE jobs SET category_id = ?, region_id = ?, title = ?, job_type = ?, salary = ?, vacancies = ?,
                    experience = ?, deadline = ?, description = ?, requirements = ?, status = 0
             WHERE id = ? AND employer_id = ?'
        );
        $st->execute([
            $form['category_id'], $form['region_id'], $form['title'], $form['job_type'], $form['salary'],
            (int) $form['vacancies'], $form['experience'], $form['deadline'], $form['description'],
            $form['requirements'], $id, current_user_id(),
        ]);
        set_flash('success', 'Job updated. It has been sent to admin for re-approval.');
        redirect('/employer/my_jobs.php');
    }
}

$submit_label = 'Update Job';
include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-1"><i class="bi bi-pencil-square text-primary"></i> Edit Job</h2>
            <p class="text-muted small">Current status: <?= job_status_badge($job['status']) ?> &middot; Saving changes sends the job back for admin approval.</p>
            <?php show_errors($errors); ?>
            <?php include __DIR__ . '/../includes/job_form.php'; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
