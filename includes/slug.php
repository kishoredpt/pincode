<?php

if (!function_exists('slugify_text')) {
    function slugify_text($value)
    {
        $value = (string) $value;
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (function_exists('transliterator_transliterate')) {
            $converted = transliterator_transliterate('Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove; Lower()', $value);
            if (is_string($converted)) {
                $value = $converted;
            }
        } else {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = strtolower($converted);
            } else {
                $value = strtolower($value);
            }
        }

        $value = preg_replace('/[\'"’`]+/', '', $value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string) $value, '-');
    }
}

if (!function_exists('is_malformed_office_slug')) {
    function is_malformed_office_slug($slug)
    {
        $slug = (string) $slug;
        if ($slug === '' || strlen($slug) < 3) {
            return true;
        }

        if (preg_match('/(^-|-$|--)/', $slug)) {
            return true;
        }

        $segments = array_values(array_filter(explode('-', $slug), static function ($part) {
            return $part !== '';
        }));

        if (count($segments) === 0) {
            return true;
        }

        // Reject heavily damaged slugs such as "-post-office" patterns or mostly single-letter chunks.
        $singleCharParts = 0;
        foreach ($segments as $segment) {
            if (strlen($segment) === 1) {
                $singleCharParts++;
            }
        }

        return $singleCharParts > 1;
    }
}
