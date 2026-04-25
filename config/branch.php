<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IT Support Contact Information
    |--------------------------------------------------------------------------
    |
    | Contact details displayed on the Branch Manager dashboard for IT support.
    |
    */
    'it_support' => [
        'email' => env('IT_SUPPORT_EMAIL', 'it-support@arrahnu.com'),
        'phone' => env('IT_SUPPORT_PHONE', '+603-XXXX-XXXX'),
        'hours' => env('IT_SUPPORT_HOURS', 'Mon-Fri 9:00 AM - 6:00 PM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that control Branch Manager dashboard behaviour.
    |
    */
    'dashboard' => [
        // Livewire poll interval for auto-refresh
        'poll_interval' => env('BRANCH_POLL_INTERVAL', '30s'),

        // Hours a newly deployed feature shows a "New" badge
        'new_feature_badge_hours' => 24,

        // Hours of inactivity before a staff member is flagged as inactive
        'inactive_staff_threshold_hours' => 4,

        // Minutes within which a staff is considered "currently active"
        'active_staff_window_minutes' => 15,

        // Percentage drop in feature usage (week-over-week) that triggers an alert
        'usage_drop_threshold_percent' => 30,

        // Minimum avg executions per hour before a performance degradation alert fires
        'perf_degradation_threshold_per_hour' => 2,
    ],
];
