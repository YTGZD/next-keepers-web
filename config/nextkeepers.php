<?php

return [
    // Shown in the footer and privacy policy only when set in .env.
    'contact_email' => env('NK_CONTACT_EMAIL'),

    // Footer social links render only for the URLs that are set.
    'social' => [
        'linkedin' => env('NK_LINKEDIN_URL'),
        'youtube' => env('NK_YOUTUBE_URL'),
        'x' => env('NK_X_URL'),
    ],
];
