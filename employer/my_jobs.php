<?php
/**
 * employer/my_jobs.php - All jobs posted by the logged-in employer.
 */
require_once __DIR__ . '/../config/db.php';
require_role('employer');
$page_title = 'My Jobs';

$st = $pdo->prepare(
    'SELECT j.id, j.title, j.job_type, j.deadline, j.status, j.created_at,
            c.name AS category_name, r.name AS region_name,
            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.status <> 3) AS applicants
     FROM jobs j
     JOIN categories c ON c.id = j.category_id
     JOIN regions r    ON r.id = j.region_id
     WHERE j.employer_id = ?
     ORDER BY j.created_at DESC, j.id DESC'
);
$st->execute([current_user_id()]);
$jobs = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Jobs</h1>
        <a href="<?= BASE_URL ?>/jobs/post.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Post New Job</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($jobs)): ?>
                <div class="alert alert-info mb-0">You have not posted any jobs yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Title</th><th>Category</th><th>Location</th><th>Deadline</th><th>Status</th><th>Applicants</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $j['id'] ?>"><?= e($j['title']) ?></a><br>
                                    <small class="text-muted"><?= e($j['job_type']) ?></small></td>
                                <td><?= e($j['category_name']) ?></td>
                                <td><?= e($j['region_name']) ?></td>
                                <td class="<?= $j['deadline'] < date('Y-m-d') ? 'text-danger' : '' ?>"><?= e(date('d M Y', strtotime($j['deadline']))) ?></td>
                                <td><?= job_status_badge($j['status']) ?></td>
                                <td><a href="applicants.php?job_id=<?= (int) $j['id'] ?>" class="badge bg-primary text-decoration-none"><?= (int) $j['applicants'] ?> view</a></td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= BASE_URL ?>/jobs/edit.php?id=<?= (int) $j['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="<?= BASE_URL ?>/jobs/delete.php" class="d-inline"
                                          onsubmit="return confirm('Delete this job? All its applications will also be deleted.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
