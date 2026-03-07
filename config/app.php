<?php

return [
    'name' => getenv('APP_NAME') ?: 'India Pincode Locator',
    'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'),
    'env' => getenv('APP_ENV') ?: 'production',
    'cache_ttl' => (int) (getenv('CACHE_TTL') ?: 900),
    'ad_slots_enabled' => filter_var(getenv('AD_SLOTS_ENABLED') ?: false, FILTER_VALIDATE_BOOL),
    'contact_email' => getenv('CONTACT_EMAIL') ?: 'support@example.com',
];
