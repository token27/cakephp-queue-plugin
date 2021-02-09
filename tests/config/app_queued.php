<?php

return [
    'Queue' => [
        // time (in seconds) after which a job is requeue if the worker doesn't report back
        'default_worker_timeout' => 1800,
        // seconds of running time after which the worker will terminate (0 = unlimited)
        'worker_max_run_time' => 120,
        // minimum time (in seconds) which a task remains in the database before being cleaned up.
        'cleanup_timeout' => 2592000, // 30 days

        /* Optional */
        'isSearchEnabled' => true,
    ],
];
