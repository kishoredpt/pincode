</main>
<footer class="bg-light border-top mt-4 py-4">
    <div class="container small text-muted d-flex flex-wrap gap-3 justify-content-between">
        <span>© <?= date('Y'); ?> India Pincode Locator</span>
        <div class="d-flex gap-3">
            <a href="<?= e(siteUrl('legal/privacy.php')); ?>">Privacy</a>
            <a href="<?= e(siteUrl('legal/terms.php')); ?>">Terms</a>
            <a href="<?= e(siteUrl('legal/disclaimer.php')); ?>">Disclaimer</a>
            <a href="<?= e(siteUrl('legal/about.php')); ?>">About</a>
            <a href="<?= e(siteUrl('legal/contact.php')); ?>">Contact</a>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(siteUrl('assets/js/app.min.js')); ?>" defer></script>
</body>
</html>
