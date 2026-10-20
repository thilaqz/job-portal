<?php
/**
 * admin/jobs.php - Approve / Reject / Delete jobs.
 * Pending jobs are listed first. Optional filter: ?status=0|1|2
 */
require_once __DIR__ . '/../config/db.php';
require_admin();
$page_title = 'Manage Jobs';
$admin_area = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $job_id = (int) ($_POST['job_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve' || $action === 'reject') {
        $st = $pdo->prepare('UPDATE jobs SET status = ? WHERE id = ?');
        $st->execute([$action === 'approve' ? 1 : 2, $job_id]);
        set_flash('success', 'Job ' . ($action === 'approve' ? 'approved' : 'rejected') . '.');
    } elseif ($action === 'delete') {
        $st = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
        $st->execute([$job_id]);
        set_flash('success', 'Job deleted.');
    } else {
        set_flash('danger', 'Unknown action.');
    }
    $back = isset($_POST['filter']) && $_POST['filter'] !== '' ? '?status=' . (int) $_POST['filter'] : '';
    redirect('/admin/jobs.php' . $back);
}

$filter = isset($_GET['status']) && in_array($_GET['status'], ['0', '1', '2'], true) ? (int) $_GET['status'] : null;

$sql = 'SELECT j.id, j.title, j.job_type, j.status, j.created_at, j.deadline,
               c.name AS category_name, r.name AS region_name,
               COALESCE(u.company_name, u.name) AS company,
               (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicants
        FROM jobs j
        JOIN categories c ON c.id = j.category_id
        JOIN regions r    ON r.id = j.region_id
        JOIN users u      ON u.id = j.employer_id';
$params = [];
if ($filter !== null) {
    $sql     .= ' WHERE j.status = ?';
    $params[] = $filter;
}
// FIELD(): pending (0) first, then approved (1), then rejected (2)
$sql .= ' ORDER BY FIELD(j.status, 0, 1, 2), j.created_at DESC';
$st = $pdo->prepare($sql);
$st->execute($params);
$jobs = $st->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-3">Manage Jobs</h1>
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a class="nav-link <?= $filter === null ? 'active' : '' ?>" href="jobs.php">All</a></li>
        <li class="nav-item"><a class="nav-link <?= $filter === 0 ? 'active' : '' ?>" href="?status=0">Pending</a></li>
        <li class="nav-item"><a class="nav-link <?= $filter === 1 ? 'active' : '' ?>" href="?status=1">Approved</a></li>
        <li class="nav-item"><a class="nav-link <?= $filter === 2 ? 'active' : '' ?>" href="?status=2">Rejected</a></li>
    </ul>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($jobs)): ?>
                <div class="alert alert-info mb-0">No jobs found.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Title</th><th>Company</th><th>Category / Location</th><th>Posted</th><th>Apps</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($jobs as $j): ?>
                        <tr class="<?= (int) $j['status'] === 0 ? 'table-warning' : '' ?>">
                            <td><?= (int) $j['id'] ?></td>
                            <td><a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $j['id'] ?>" target="_blank"><?= e($j['title']) ?></a><br>
                                <small class="text-muted"><?= e($j['job_type']) ?></small></td>
                            <td><?= e($j['company']) ?></td>
                            <td><?= e($j['category_name']) ?><br><small class="text-muted"><?= e($j['region_name']) ?></small></td>
                            <td><?= e(date('d M Y', strtotime($j['created_at']))) ?></td>
                            <td><?= (int) $j['applicants'] ?></td>
                            <td><?= job_status_badge($j['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>">
                                    <input type="hidden" name="filter" value="<?= $filter === null ? '' : $filter ?>">
                                    <?php if ((int) $j['status'] !== 1): ?>
                                        <button name="action" value="approve" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                    <?php endif; ?>
                                    <?php if ((int) $j['status'] !== 2): ?>
                                        <button name="action" value="reject" class="btn btn-sm btn-warning" title="Reject"><i class="bi bi-x-lg"></i></button>
                                    <?php endif; ?>
                                    <button name="action" value="delete" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Delete this job permanently?');"><i class="bi bi-trash"></i></button>
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
