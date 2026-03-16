<?php
require_once __DIR__ . '/includes/site-settings.php';

header('Content-Type: text/plain; charset=utf-8');

$publisherId = adsense_publisher_id();

if ($publisherId !== '') {
    echo "google.com, {$publisherId}, DIRECT, f08c47fec0942fa0\n";
    exit;
}

echo "# AdSense publisher ID is not configured yet.\n";
echo "# Set ADSENSE_PUBLISHER_ID as environment variable in format ca-pub-################\n";
