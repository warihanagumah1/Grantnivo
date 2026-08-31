<?php

return [
    // Operator details confirmed by WAMEER; deployment may override them.
    'operator' => env('LEGAL_OPERATOR', 'WAMEER'),
    'country' => env('LEGAL_COUNTRY') ?: 'Ghana',
    'address' => env('LEGAL_ADDRESS') ?: 'PMB CT 3, Cantonments, Accra, Ghana',
    'contact_email' => env('PRIVACY_CONTACT_EMAIL') ?: 'support@grantnivo.com',
    'reviewed' => (bool) env('LEGAL_POLICIES_REVIEWED', false),
    // Keep historical policy copies when changing these versions.
    'privacy_version' => '2026-08-31',
    'terms_version' => '2026-08-31',
    'consent_version' => '2026-08-31',
];
