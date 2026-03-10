<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$topics = [
    'What is PIN Code in India', 'How to Find Your Pincode', 'History of Indian Postal System', 'Postal Zones Explained',
    'Speed Post vs Registered Post', 'Postal Delivery Process', 'How Address Sorting Works in India',
    'How Ecommerce Uses PIN Code Intelligence', 'Difference Between GPO, HO, SO, BO', 'How to Track a Speed Post Shipment'
];

$target = 1000;
$inserted = 0;
for ($i = 1; $i <= $target; $i++) {
    $topic = $topics[array_rand($topics)] . ' #' . $i;
    $slug = slugify($topic);
    $content = '<h2>Introduction</h2><p>' . introText($topic, 350) . '</p>'
        . '<h2>Detailed Guide</h2><p>' . introText($topic . ' guide', 500) . '</p>'
        . '<h2>FAQ</h2><h3>Why is this important?</h3><p>' . introText('postal awareness', 160) . '</p>';
    $stmt = $pdo->prepare('INSERT IGNORE INTO articles(title,slug,content,meta_title,meta_description,created_at) VALUES(:title,:slug,:content,:meta_title,:meta_description,NOW())');
    $stmt->execute([
        ':title' => $topic,
        ':slug' => $slug,
        ':content' => $content,
        ':meta_title' => $topic . ' | India Pincode Locator',
        ':meta_description' => 'Read an in-depth guide on ' . $topic . ' with FAQs and practical postal information.',
    ]);
    $inserted += (int) $stmt->rowCount();
}

echo "Articles generated/inserted: {$inserted}\n";
