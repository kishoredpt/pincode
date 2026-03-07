<?php

declare(strict_types=1);

final class MetaGenerator
{
    public static function forEntity(string $type, array $context, string $baseUrl): array
    {
        $canonical = rtrim($baseUrl, '/') . '/' . ltrim((string) ($context['path'] ?? '/'), '/');

        return match ($type) {
            'pincode' => [
                'meta_title' => sprintf('Pincode %s – Post Office Details in %s, %s', $context['pincode'], $context['district'], $context['state']),
                'meta_description' => sprintf('Find complete details of pincode %s including post office, district, state, and delivery status.', $context['pincode']),
                'canonical_url' => $canonical,
            ],
            'district' => [
                'meta_title' => sprintf('%s District Pincode List and Post Office Details', $context['district']),
                'meta_description' => sprintf('Explore all pincodes, post offices, and delivery information in %s district, %s.', $context['district'], $context['state']),
                'canonical_url' => $canonical,
            ],
            'state' => [
                'meta_title' => sprintf('%s Pincode Directory – District and Post Office Search', $context['state']),
                'meta_description' => sprintf('Browse complete pincode coverage, districts, and post office data for %s.', $context['state']),
                'canonical_url' => $canonical,
            ],
            default => [
                'meta_title' => sprintf('%s Area Pincode and Post Office Details', $context['area'] ?? 'Local'),
                'meta_description' => sprintf('Find pincode and postal information for %s in %s, %s.', $context['area'] ?? 'area', $context['district'] ?? 'district', $context['state'] ?? 'state'),
                'canonical_url' => $canonical,
            ],
        };
    }
}
