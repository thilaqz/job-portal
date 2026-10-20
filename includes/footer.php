<?php
/**
 * includes/footer.php
 * Bottom of every page: footer links + Bootstrap JS.
 */
?>
</main>

<footer class="site-footer mt-5">
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-5">
                <h5 class="text-white"><i class="bi bi-briefcase-fill"></i> JobPortal</h5>
                <p class="small mb-0">A simple online job portal connecting job seekers with employers.
                    Built with PHP, MySQL and Bootstrap 5.</p>
            </div>
            <div class="col-6 col-md-3">
                <h6 class="text-white">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/jobs/list.php">Browse Jobs</a></li>
                    <li><a href="<?= BASE_URL ?>/about.php">About</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <h6 class="text-white">Accounts</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= BASE_URL ?>/auth/register.php">Create Account</a></li>
                    <li><a href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/login.php">Admin Login</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center small mb-0">&copy; <?= date('Y') ?> Online Job Portal. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
