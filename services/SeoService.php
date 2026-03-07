<?php

declare(strict_types=1);

class SeoService
{
    public function forPage(string $type, array $context, array $appConfig): array
    {
        $title = $appConfig['name'];
        $description = 'Search India PIN code, district and post office data with verified structure.';

        if ($type === 'pincode' && !empty($context['summary'])) {
            $s = $context['summary'];
            $title = "{$s['pincode']} PIN Code | {$s['district']}, {$s['statename']} Post Office List";
            $description = "Find {$s['pincode']} PIN code post office details, delivery coverage, district links, and state postal information.";
        }

        if ($type === 'state' && !empty($context['summary'])) {
            $title = "{$context['summary']['statename']} PIN Code Directory";
            $description = "Browse districts and postal coverage for {$context['summary']['statename']} with structured navigation.";
        }

        if ($type === 'district' && !empty($context['summary'])) {
            $title = "{$context['summary']['district']} District PIN Code List";
            $description = "Explore {$context['summary']['district']} district post offices and pincode mapping.";
        }

        return ['title' => $title, 'description' => $description];
    }

    public function canonical(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . ($path === '/' ? '/' : '/' . trim($path, '/'));
    }
}
