<?php
/**
 * jobs/save.php - Save / unsave a job (toggle). POST only.
 * Fields: job_id, return (page to go back to)
 */
require_once __DIR__ . '/../config/db.php';
require_role('seeker');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/jobs/list.php');
}
verify_csrf();

$user_id = current_user_id();
$job_id  = (int) ($_POST['job_id'] ?? 0);
$return  = safe_path($_POST['return'] ?? '', '/seeker/saved.php');

$st = $pdo->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
$st->execute([$user_id, $job_id]);

if ($st->fetch()) {
    // Already saved -> remove it
    $pdo->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$user_id, $job_id]);
    set_flash('info', 'Job removed from your saved list.');
} else {
    // Only approved jobs can be saved
    $st = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND status = 1');
    $st->execute([$job_id]);
    if ($st->fetch()) {
        $pdo->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?, ?)')->execute([$user_id, $job_id]);
        set_flash('success', 'Job saved. You can find it under "Saved Jobs".');
    } else {
        set_flash('warning', 'Job not found.');
    }
}
redirect($return);
