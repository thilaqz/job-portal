<?php
/**
 * jobs/post.php - Employer posts a new job.
 * New jobs start with status 0 (Pending) until admin approves them.
 */
require_once __DIR__ . '/../config/db.php';
require_role('employer');
$page_title = 'Post a Job';

$categories = get_categories($pdo);
$regions    = get_regions($pdo);
$errors     = [];

// Empty form values
$form = [
    'title' => '', 'category_id' => 0, 'region_id' => 0, 'job_type' => '', 'salary' => '',
    'vacancies' => '1', 'experience' => '', 'deadline' => date('Y-m-d', strtotime('+30 days')),
    'description' => '', 'requirements' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    list($form, $errors) = validate_job_form($pdo, $_POST);

    if (empty($errors)) {
        $st = $pdo->prepare(
            'INSERT INTO jobs (employer_id, category_id, region_id, title, job_type, salary, vacancies,
                               experience, deadline, description, requirements, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)'
        );
        $st->execute([
            current_user_id(), $form['category_id'], $form['region_id'], $form['title'], $form['job_type'],
            $form['salary'], (int) $form['vacancies'], $form['experience'], $form['deadline'],
            $form['description'], $form['requirements'],
        ]);
        set_flash('success', 'Job posted successfully! It will be visible after admin approval.');
        redirect('/employer/my_jobs.php');
    }
}

$submit_label = 'Post Job';
include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-1"><i class="bi bi-plus-circle text-primary"></i> Post a New Job</h2>
            <p class="text-muted small">All fields marked * are required. The job goes live after admin approval.</p>
            <?php show_errors($errors); ?>
            <?php include __DIR__ . '/../includes/job_form.php'; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
