<?php

/**
 * This file configures default behavior for all workers
 *
 * To modify these parameters, copy this file into your own CakePHP config directory or copy the array into your existing file.
 */
return [
    'Queue' => [
        // seconds of running time after which the PHP process will terminate, 0 or null uses worker timeout * 2
        'default_timeout_php' => 0,
        // 
        'default_memory_limit' => "512M",
        // time (in seconds) after which a job is requeue if the worker doesn't report back
        'default_worker_timeout' => 900,
        // number of retries if a job fails or times out.
        'default_worker_retries' => 0,
        // seconds of running time after which the worker will terminate (0 = unlimited)
        'worker_max_runtime' => 120,
        // seconds to sleep() when no executable job is found
        'worker_sleep_time' => 10,
        // instruct a Workerprocess quit when there are no more tasks for it to execute (true = exit, false = keep running)
        'worker_exit_when_nothing_todo' => false,
        // minimum time (in seconds) which a task remains in the database before being cleaned up.
        'cleanup_timeout' => 2592000, // 30 days
        // probability in percent of a old job cleanup happening
        'clean_olds_prob' => 10,
        // set to true for multi server setup, this will affect web backend possibilities to kill/end workers
        'multiserver' => true,
        // set this to a limit that can work with your memory limits and alike, 0 => no limit
        'workers_max' => 3,
        // determine whether logging is enabled
        'log' => true,
        // set default Mailer class
        'mailerClass' => 'Cake\Mailer\Email',
        // set default datasource connection
        'database_connection' => null,
        // enable Search. requires friendsofcake/search
        'isSearchEnabled' => true,
        // enable Search. requires frontend assets
        'isStatisticEnabled' => true,
        //
        'defaultLocale' => 'en_US'
    ],
];
