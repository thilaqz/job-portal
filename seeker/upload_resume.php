<?php
/**
 * seeker/upload_resume.php - Upload / replace / view own resume.
 * Rules: PDF only, max 2 MB, saved with a random unique name.
 *   ?view=1  -> open own resume in the browser
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');
$page_title = 'My Resume';
$uid = current_user_id();

$st = $pdo->prepare('SELECT * FROM resumes WHERE user_id = ?');
$st->execute([$uid]);
$resume = $st->fetch();

// View own resume
if (isset($_GET['view']) && $resume) {
    send_pdf(RESUME_DIR . $resume['file_name'], $resume['original_name'], true);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $up = upload_file(
        $_FILES['resume'] ?? [],
        ['pdf' => ['application/pdf']],
        2 * 1024 * 1024,                       // 2 MB
        RESUME_DIR,
        'resume_' . $uid . '_'
    );

    if (!$up['ok']) {
        $errors[] = $up['error'];
    } else {
        $original = substr(basename($_FILES['resume']['name']), 0, 200);
        if ($resume) {
            // Replace. Keep the old file if an application still points to it.
            $st = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE resume_file = ?');
            $st->execute([$resume['file_name']]);
            if (!$st->fetchColumn() && is_file(RESUME_DIR . $resume['file_name'])) {
                unlink(RESUME_DIR . $resume['file_name']);
            }
            $st = $pdo->prepare('UPDATE resumes SET file_name = ?, original_name = ?, uploaded_at = NOW() WHERE user_id = ?');
            $st->execute([$up['name'], $original, $uid]);
        } else {
            $st = $pdo->prepare('INSERT INTO resumes (user_id, file_name, original_name) VALUES (?, ?, ?)');
            $st->execute([$uid, $up['name'], $original]);
        }
        set_flash('success', 'Resume uploaded successfully. New applications will use this resume.');
        redirect('/seeker/upload_resume.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4"><i class="bi bi-file-earmark-pdf text-danger"></i> My Resume</h1>

                    <?php if ($resume): ?>
                        <div class="alert alert-light border d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= e($resume['original_name']) ?></strong><br>
                                <small class="text-muted">Uploaded on <?= e(date('d M Y, h:i A', strtotime($resume['uploaded_at']))) ?></small>
                            </div>
                            <a href="?view=1" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">You have not uploaded a resume yet. A resume is required to apply for jobs.</div>
                    <?php endif; ?>

                    <?php show_errors($errors); ?>
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label"><?= $resume ? 'Replace Resume' : 'Upload Resume' ?> *</label>
                            <input type="file" name="resume" id="resumeInput" class="form-control" accept=".pdf,application/pdf" required>
                            <div class="form-text">PDF only, maximum size 2 MB.</div>
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
