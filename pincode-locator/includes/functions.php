<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function sanitizeInput(?string $value): string
{
    return trim(filter_var((string) $value, FILTER_SANITIZE_SPECIAL_CHARS));
}

function siteUrl(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim($scheme . '://' . $host . '/pincode-locator', '/');
    return $base . '/' . ltrim($path, '/');
}

function canonicalTag(string $path): string
{
    return '<link rel="canonical" href="' . e(siteUrl($path)) . '">';
}

function fetchAll(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchOne(PDO $pdo, string $sql, array $params = []): ?array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function introText(string $topic, int $words = 280): string
{
    $base = "{$topic} has a strong postal network that supports households, businesses, and government services. "
        . "India Post uses PIN code mapping to improve sorting accuracy and delivery speed across urban and rural areas. "
        . "This portal helps citizens discover branch post offices, sub offices, and head offices with practical guidance. "
        . "You can use location details, delivery status, and related postal divisions to plan mailing activities. "
        . "Reliable postal data also helps ecommerce teams, compliance departments, and logistics professionals. ";

    $wordsPerBase = str_word_count($base);
    $repeat = max(1, (int) ceil($words / $wordsPerBase));
    return trim(str_repeat($base, $repeat));
}
