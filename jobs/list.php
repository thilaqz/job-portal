<?php
/**
 * jobs/list.php - All approved jobs with search, filters and pagination.
 * Filters (GET): keyword, category, region, job_type, page
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Browse Jobs';

$categories = get_categories($pdo);
$regions    = get_regions($pdo);

// ---- Read filters ----
$keyword  = trim($_GET['keyword'] ?? '');
$category = (int) ($_GET['category'] ?? 0);
$region   = (int) ($_GET['region'] ?? 0);
$job_type = $_GET['job_type'] ?? '';
if (!in_array($job_type, job_types(), true)) {
    $job_type = '';
}
$page = max(1, (int) ($_GET['page'] ?? 1));

// ---- Build WHERE clause with placeholders (safe from SQL injection) ----
$where  = ['j.status = 1'];
$params = [];

if ($keyword !== '') {
    $where[]  = '(j.title LIKE ? OR j.description LIKE ? OR u.company_name LIKE ?)';
    $like     = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($category > 0) {
    $where[]  = 'j.category_id = ?';
    $params[] = $category;
}
if ($region > 0) {
    $where[]  = 'j.region_id = ?';
    $params[] = $region;
}
if ($job_type !== '') {
    $where[]  = 'j.job_type = ?';
    $params[] = $job_type;
}
$where_sql = implode(' AND ', $where);

$from_sql = 'FROM jobs j
             JOIN categories c ON c.id = j.category_id
             JOIN regions r    ON r.id = j.region_id
             JOIN users u      ON u.id = j.employer_id';

// ---- Count for pagination ----
$st = $pdo->prepare("SELECT COUNT(*) $from_sql WHERE $where_sql");
$st->execute($params);
$total_jobs  = (int) $st->fetchColumn();
$total_pages = max(1, (int) ceil($total_jobs / JOBS_PER_PAGE));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * JOBS_PER_PAGE;

// ---- Fetch current page (LIMIT/OFFSET are integers we calculated, so safe) ----
$st = $pdo->prepare(
    "SELECT j.*, c.name AS category_name, r.name AS region_name,
            COALESCE(u.company_name, u.name) AS company
     $from_sql
     WHERE $where_sql
     ORDER BY j.created_at DESC, j.id DESC
     LIMIT " . JOBS_PER_PAGE . " OFFSET " . (int) $offset
);
$st->execute($params);
$jobs = $st->fetchAll();

/** Build a page link that keeps the current filters. */
function page_link($p)
{
    $q = $_GET;
    $q['page'] = $p;
    return BASE_URL . '/jobs/list.php?' . http_build_query($q);
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-banner text-white">
    <div class="container py-4">
        <h1 class="h2 mb-0">Browse Jobs</h1>
        <p class="mb-0"><?= $total_jobs ?> job(s) found</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row g-4">
        <!-- Filters -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-funnel"></i> Filters</h5>
                    <form method="get">
                        <div class="mb-3">
                            <label class="form-label small">Keyword</label>
                            <input type="text" name="keyword" class="form-control" value="<?= e($keyword) ?>" placeholder="e.g. PHP">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>" <?= $category === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Location</label>
                            <select name="region" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($regions as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>" <?= $region === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Job Type</label>
                            <select name="job_type" class="form-select">
                                <option value="">All</option>
                                <?php foreach (job_types() as $t): ?>
                                    <option value="<?= e($t) ?>" <?= $job_type === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100 mb-2"><i class="bi bi-search"></i> Apply Filters</button>
                        <a href="<?= BASE_URL ?>/jobs/list.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9">
            <div class="row g-4">
                <?php if (empty($jobs)): ?>
                    <div class="col-12"><div class="alert alert-info">No jobs match your search. Try different filters.</div></div>
                <?php endif; ?>
                <?php foreach ($jobs as $job): ?>
                    <?php include __DIR__ . '/../includes/job_card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e(page_link($page - 1)) ?>">&laquo; Prev</a>
                        </li>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e(page_link($p)) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e(page_link($page + 1)) ?>">Next &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
