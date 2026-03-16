<?php

if (!function_exists('site_env')) {
    function site_env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value !== false && trim((string)$value) !== '') {
            return trim((string)$value);
        }

        if (!empty($_SERVER[$key])) {
            return trim((string)$_SERVER[$key]);
        }

        if (!empty($_ENV[$key])) {
            return trim((string)$_ENV[$key]);
        }

        return $default;
    }
}

if (!function_exists('adsense_publisher_id')) {
    function adsense_publisher_id(): string
    {
        $id = site_env('ADSENSE_PUBLISHER_ID', '');
        if ($id === '') {
            return '';
        }

        if (preg_match('/^ca-pub-\d{16}$/', $id)) {
            return $id;
        }

        return '';
    }
}

if (!function_exists('ga4_measurement_id')) {
    function ga4_measurement_id(): string
    {
        $id = site_env('GA4_MEASUREMENT_ID', '');
        return preg_match('/^G-[A-Z0-9]+$/', $id) ? $id : '';
    }
}

if (!function_exists('gsc_site_verification')) {
    function gsc_site_verification(): string
    {
        return site_env('GSC_SITE_VERIFICATION', '');
    }
}
