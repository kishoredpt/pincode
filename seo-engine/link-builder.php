<?php

declare(strict_types=1);

require_once __DIR__ . '/engine.php';

function buildDistrictLinks(PDO $pdo, string $district): array
{
    $stmt = $pdo->prepare("SELECT DISTINCT pincode FROM post_offices WHERE LOWER(district)=LOWER(:district) ORDER BY pincode LIMIT 10");
    $stmt->execute(['district' => $district]);

    return array_map(static fn (array $row): array => [
        'label' => 'Pincode ' . $row['pincode'],
        'path' => '/pincode/' . $row['pincode'],
    ], $stmt->fetchAll());
}

function buildStateLinks(PDO $pdo, string $state): array
{
    $stmt = $pdo->prepare("SELECT DISTINCT district FROM post_offices WHERE LOWER(statename)=LOWER(:state) ORDER BY district LIMIT 10");
    $stmt->execute(['state' => $state]);

    return array_map(static fn (array $row): array => [
        'label' => $row['district'] . ' district',
        'path' => '/district/' . strtolower(str_replace(' ', '-', $row['district'])),
    ], $stmt->fetchAll());
}

function buildNearbyPincodeLinks(PDO $pdo, string $pincode): array
{
    $districtStmt = $pdo->prepare("SELECT district FROM post_offices WHERE pincode = :pincode LIMIT 1");
    $districtStmt->execute(['pincode' => $pincode]);
    $district = $districtStmt->fetchColumn();
    if (!$district) {
        return [];
    }

    $stmt = $pdo->prepare("SELECT DISTINCT pincode FROM post_offices WHERE LOWER(district)=LOWER(:district) AND pincode <> :pincode ORDER BY pincode LIMIT 10");
    $stmt->execute(['district' => $district, 'pincode' => $pincode]);

    return array_map(static fn (array $row): array => [
        'label' => 'Nearby ' . $row['pincode'],
        'path' => '/pincode/' . $row['pincode'],
    ], $stmt->fetchAll());
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $engine = new SeoEngine();

    $pages = $engine->pdo->query("SELECT entity_type, entity_id, page_path FROM seo_pages ORDER BY last_generated_at DESC LIMIT 1000")->fetchAll();
    $insert = $engine->pdo->prepare("INSERT INTO seo_internal_links (source_path, target_path, section_name) VALUES (:source, :target, :section)
                                    ON DUPLICATE KEY UPDATE updated_at=NOW()");

    foreach ($pages as $page) {
        $links = [];

        if ($page['entity_type'] === 'pincode') {
            $links = array_merge($links, buildNearbyPincodeLinks($engine->pdo, $page['entity_id']));
            $districtStmt = $engine->pdo->prepare("SELECT district, statename FROM post_offices WHERE pincode = :pin LIMIT 1");
            $districtStmt->execute(['pin' => $page['entity_id']]);
            $row = $districtStmt->fetch();
            if ($row) {
                $links = array_merge($links, buildDistrictLinks($engine->pdo, $row['district']));
                $links = array_merge($links, buildStateLinks($engine->pdo, $row['statename']));
            }
        }

        if ($page['entity_type'] === 'district') {
            $state = $engine->pdo->prepare("SELECT statename FROM post_offices WHERE LOWER(district)=LOWER(:district) LIMIT 1");
            $state->execute(['district' => str_replace('-', ' ', $page['entity_id'])]);
            $stateName = $state->fetchColumn();
            if ($stateName) {
                $links = array_merge($links, buildStateLinks($engine->pdo, (string) $stateName));
            }
        }

        $links = array_slice($links, 0, 30);
        foreach ($links as $link) {
            $insert->execute([
                'source' => $page['page_path'],
                'target' => $link['path'],
                'section' => 'auto',
            ]);
        }
    }

    echo "Internal link graph refreshed for " . count($pages) . " pages.\n";
}
