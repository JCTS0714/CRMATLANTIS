<?php

return [
    // Enables request-level observability logs without changing business logic.
    'request_metrics_enabled' => (bool) env('REQUEST_METRICS_ENABLED', true),

    // Percentage of normal requests logged (1-100).
    'request_metrics_sample_rate' => (int) env('REQUEST_METRICS_SAMPLE_RATE', 20),

    // Slow requests are always logged at warning level.
    'request_metrics_slow_ms' => (int) env('REQUEST_METRICS_SLOW_MS', 800),

    // Existing logging channel where metrics are emitted.
    'request_metrics_channel' => env('REQUEST_METRICS_CHANNEL', 'stack'),

    // Paths that add little diagnostic value and can be ignored.
    'request_metrics_ignored_paths' => [
        'up',
        '_ignition/*',
        'vendor/*',
    ],
];
