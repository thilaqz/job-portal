<?php
/**
 * jobs/delete.php - Employer deletes their OWN job. POST only.
 * Applications and saved entries are removed automatically (ON DELETE CASCADE).
 */
require_once __DIR__ . '/../config/db.php';
require_role('employer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/employer/my_jobs.php');
}
verify_csrf();

$id = (int) ($_POST['job_id'] ?? 0);

// "AND employer_id = ?" makes sure employers can only delete their own jobs
$st = $pdo->prepare('DELETE FROM jobs WHERE id = ? AND employer_id = ?');
$st->execute([$id, current_user_id()]);

if ($st->rowCount() > 0) {
    set_flash('success', 'Job deleted successfully.');
} else {
    set_flash('danger', 'Job not found or you do not have permission to delete it.');
}
redirect('/employer/my_jobs.php');
