<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics 4 Measurement ID
    |--------------------------------------------------------------------------
    |
    | Your GA4 measurement ID (format: G-XXXXXXXXXX).
    |
    | This is the primary configuration for the Laravel GA4 package.
    | The measurement ID is required for GA4 tracking to work.
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
    | **Important Notes:**
    | - If empty or not set, GA4 script will not be rendered
    | - Script only renders when measurement ID is configured
    | - Prevents tracking in development/testing unless configured
    | - Whitespace is automatically trimmed from the measurement ID
    | - Non-string values (null, false, arrays, objects) are treated as empty
    |
    | **Security Best Practices:**
    | - Never commit .env files to version control
    | - Use different measurement IDs for different environments
    | - Review generated JavaScript for any sensitive data
    |
    | **Validation:**
    | - The measurement ID must be a non-empty string
    | - Leading and trailing whitespace is automatically trimmed
    | - The service handles HTML escaping for security
    |
    */
    'measurement_id' => env('GA4_MEASUREMENT_ID', ''),
];
