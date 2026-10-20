<?php
/**
 * admin/regions.php - CRUD for job locations (regions table).
 *   Add:    POST action=add,    name
 *   Update: POST action=update, id, name
 *   Delete: POST action=delete, id   (jobs in this location are also deleted - CASCADE)
 *   Edit form: ?edit=ID
 */
require_once __DIR__ . '/../config/db.php';
require_admin();
$page_title = 'Job Locations';
$admin_area = true;

$errors = [];
$name   = '';
$edit   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');

    if ($action === 'delete') {
        $st = $pdo->prepare('DELETE FROM regions WHERE id = ?');
        $st->execute([$id]);
        set_flash('success', 'Location deleted.');
        redirect('/admin/regions.php');
    }

    // Validate name for add / update
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = 'Location name must be 2 to 100 characters.';
    } else {
        $st = $pdo->prepare('SELECT COUNT(*) FROM regions WHERE name = ? AND id <> ?');
        $st->execute([$name, $id]);
        if ($st->fetchColumn()) {
            $errors[] = 'A location with this name already exists.';
        }
    }

    if (empty($errors)) {
        if ($action === 'update') {
            $st = $pdo->prepare('UPDATE regions SET name = ? WHERE id = ?');
            $st->execute([$name, $id]);
            set_flash('success', 'Location updated.');
        } else {
            $st = $pdo->prepare('INSERT INTO regions (name) VALUES (?)');
            $st->execute([$name]);
            set_flash('success', 'Location added.');
        }
        redirect('/admin/regions.php');
    }
    if ($action === 'update') {
        $edit = ['id' => $id, 'name' => $name];
    }
}

// Load a row for editing
if (!$edit && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT id, name FROM regions WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$rows = $pdo->query(
    'SELECT c.id, c.name, c.created_at, COUNT(j.id) AS jobs
     FROM regions c LEFT JOIN jobs j ON j.region_id = c.id
     GROUP BY c.id, c.name, c.created_at ORDER BY c.name'
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-4">
    <h1 class="h3 mb-4">Job Locations</h1>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= $edit ? 'Edit Location' : 'Add Location' ?></h5>
                    <?php show_errors($errors); ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
                        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" maxlength="100" required
                                   value="<?= e($edit ? $edit['name'] : $name) ?>">
                        </div>
                        <button class="btn btn-primary"><?= $edit ? 'Update' : 'Add' ?></button>
                        <?php if ($edit): ?><a href="regions.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Jobs</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                            <?php if (empty($rows)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No locations yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= (int) $r['id'] ?></td>
                                    <td><?= e($r['name']) ?></td>
                                    <td><span class="badge bg-primary"><?= (int) $r['jobs'] ?></span></td>
                                    <td><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
                                    <td class="text-end text-nowrap">
                                        <a href="?edit=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="post" class="d-inline"
                                              onsubmit="return confirm('Delete this location? <?= (int) $r['jobs'] ?> job(s) in it will also be deleted.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
