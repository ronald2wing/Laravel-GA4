<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics 4 Measurement ID
    |--------------------------------------------------------------------------
    |
    | Your GA4 measurement ID (format: G-XXXXXXXXXX).
    |
    | **Finding Your Measurement ID:**
    | 1. Go to Google Analytics admin panel
    | 2. Navigate: Admin > Data Streams > [Your Stream]
    | 3. Copy the "Measurement ID" (starts with "G-")
    |
    | **Environment Configuration:**
    | Set in your .env file:
    | GA4_MEASUREMENT_ID=G-XXXXXXXXXX
    |
    */
    'measurement_id' => env('GA4_MEASUREMENT_ID', ''),
];
