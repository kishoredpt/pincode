<?php

declare(strict_types=1);

require_once __DIR__ . '/engine.php';
require_once __DIR__ . '/meta-generator.php';

$engine = new SeoEngine();
$pdo = $engine->pdo;

$weeklyCount = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE YEARWEEK(created_at, 1)=YEARWEEK(NOW(), 1)")->fetchColumn();
$toGenerate = max(0, min(2, 3 - $weeklyCount));

$topics = [
    ['title' => 'What is a PIN Code and Why It Matters in India', 'slug' => 'what-is-a-pin-code-india'],
    ['title' => 'How PIN Codes Work in India', 'slug' => 'how-pin-codes-work-in-india'],
];

$state = $pdo->query("SELECT statename FROM post_offices GROUP BY statename ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn();
if ($state) {
    $stateSlug = strtolower(str_replace(' ', '-', (string) $state));
    $topics[] = ['title' => "List of Pincodes in {$state}", 'slug' => 'list-of-pincodes-in-' . $stateSlug];
}

$district = $pdo->query("SELECT district FROM post_offices GROUP BY district ORDER BY COUNT(*) DESC LIMIT 1")->fetchColumn();
if ($district) {
    $districtSlug = strtolower(str_replace(' ', '-', (string) $district));
    $topics[] = ['title' => "Postal Services in {$district}", 'slug' => 'postal-services-in-' . $districtSlug];
}

$existing = $pdo->query('SELECT slug FROM blog_posts')->fetchAll(PDO::FETCH_COLUMN);
$existingMap = array_fill_keys(array_map('strval', $existing), true);

$insert = $pdo->prepare('INSERT INTO blog_posts (title, slug, content, meta_title, meta_description, created_at) VALUES (:title, :slug, :content, :meta_title, :meta_description, NOW())');
$generated = 0;

foreach ($topics as $topic) {
    if ($generated >= $toGenerate) {
        break;
    }
    if (isset($existingMap[$topic['slug']])) {
        continue;
    }

    $paragraphs = [
        $topic['title'] . ' is a foundational topic for anyone dealing with Indian addresses, shipping, and location validation workflows.',
        'A six-digit PIN code enables accurate routing at sorting facilities and improves delivery predictability for last-mile carriers.',
        'Businesses rely on clean pincode mapping to avoid order failures, while citizens use it for forms, banking, and logistics communication.',
        'This article summarizes practical use-cases, common mistakes, and modern best practices for ensuring postal data quality.',
        'Our platform links this context directly to state, district, area, and pincode pages so users can move from theory to exact records.',
    ];

    while (str_word_count(implode(' ', $paragraphs)) < 650) {
        $paragraphs[] = 'Maintaining a reliable address taxonomy with pincode enrichment helps search engines and users alike discover precise local information quickly.';
    }

    $content = implode("\n\n", $paragraphs);
    $meta = [
        'meta_title' => $topic['title'] . ' | India Pincode Guide',
        'meta_description' => 'SEO guide on ' . strtolower($topic['title']) . ' with practical postal lookup insights.',
    ];

    $insert->execute([
        'title' => $topic['title'],
        'slug' => $topic['slug'],
        'content' => $content,
        'meta_title' => $meta['meta_title'],
        'meta_description' => $meta['meta_description'],
    ]);
    $generated++;
}

echo "Generated blog posts: {$generated}\n";
