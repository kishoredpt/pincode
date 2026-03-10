<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$meta = [
    'title' => 'India Pincode Locator - Search Post Offices & PIN Codes',
    'description' => 'Find all Indian post offices, district-wise and state-wise PIN code information.',
    'canonical' => siteUrl('index.php'),
];
require __DIR__ . '/includes/header.php';
$states = fetchAll($pdo, 'SELECT state_name, slug FROM states ORDER BY state_name LIMIT 36');
$articles = fetchAll($pdo, 'SELECT title, slug, meta_description FROM articles ORDER BY created_at DESC LIMIT 6');
?>
<section class="hero p-4 p-md-5 rounded-3 mb-4 text-white bg-dark">
    <h1>India Pincode Locator</h1>
    <p class="lead">Search any PIN code, post office, district, or state in India.</p>
    <form action="search.php" method="get" class="row g-2">
        <div class="col-md-10"><input class="form-control form-control-lg" name="q" placeholder="Enter pincode, post office or district" required></div>
        <div class="col-md-2"><button class="btn btn-warning btn-lg w-100">Search</button></div>
    </form>
</section>
<section class="mb-4">
    <h2>Browse by State</h2>
    <div class="row g-3">
        <?php foreach ($states as $state): ?>
        <div class="col-6 col-md-3"><a class="card p-3 text-decoration-none" href="<?= e(siteUrl('state/state.php?slug=' . $state['slug'])); ?>"><?= e($state['state_name']); ?></a></div>
        <?php endforeach; ?>
    </div>
</section>
<section class="mb-4"><h2>Popular Cities</h2><p>Hyderabad, Mumbai, Delhi, Bengaluru, Chennai, Kolkata, Pune, Ahmedabad.</p></section>
<section class="mb-4"><h2>What is PIN Code in India</h2><p><?= e(introText('A PIN code in India', 520)); ?></p></section>
<section class="mb-4"><h2>Latest Blog Articles</h2><div class="row g-3"><?php foreach ($articles as $article): ?><div class="col-md-4"><article class="card h-100 p-3"><h3 class="h5"><a href="<?= e(siteUrl('blog/article.php?slug=' . $article['slug'])); ?>"><?= e($article['title']); ?></a></h3><p><?= e($article['meta_description']); ?></p></article></div><?php endforeach; ?></div></section>
<section class="mb-4"><h2>Postal Facts</h2><ul><li>India Post serves over 1.5 lakh post offices.</li><li>PIN codes have 6 digits for precise routing.</li><li>Rural and urban delivery are managed through divisions and circles.</li></ul></section>
<section class="mb-4"><h2>FAQ</h2><details><summary>How can I find my post office?</summary>Use the search box by entering locality or PIN code.</details><details><summary>Is this data official?</summary>Data is normalized from publicly available India Post datasets.</details></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
