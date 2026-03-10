<?php
declare(strict_types=1);

function seoTags(array $meta): string
{
    $title = htmlspecialchars($meta['title'] ?? 'India Pincode Locator', ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($meta['description'] ?? 'Find post offices and PIN codes in India.', ENT_QUOTES, 'UTF-8');
    $canonical = htmlspecialchars($meta['canonical'] ?? '', ENT_QUOTES, 'UTF-8');

    $tags = "<title>{$title}</title>\n";
    $tags .= "<meta name=\"description\" content=\"{$description}\">\n";
    $tags .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    if ($canonical !== '') {
        $tags .= "<link rel=\"canonical\" href=\"{$canonical}\">\n";
    }
    return $tags;
}

function organizationSchema(): string
{
    return json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'India Pincode Locator',
        'url' => siteUrl(),
        'logo' => siteUrl('assets/images/logo.png'),
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
}

function websiteSchema(): string
{
    return json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'India Pincode Locator',
        'url' => siteUrl(),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => siteUrl('search.php?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
}

function breadcrumbSchema(array $items): string
{
    $listItems = [];
    foreach ($items as $i => $item) {
        $listItems[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    }
    return json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $listItems,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
}

function faqSchema(array $faqs): string
{
    $entities = array_map(static fn($faq) => [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq['a'],
        ],
    ], $faqs);

    return json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
}
