<?php

namespace Queue\Queue;

use Cake\Core\App;
use Cake\Core\Configure;
use RuntimeException;

class Config {

    public function __constructor() {
        self::loadPluginConfiguration();
    }

    public static function loadPluginConfiguration() {
        if (file_exists(ROOT . DS . 'config' . DS . 'app_queue.php')) {
            Configure::load('app_queue');
        } else {
            Configure::load('Queue.app_queue');
        }
    }

    public static function defaultDatabaseConnection() {
        return Configure::read('Queue.database_connection', null);
    }

    public static function defaultMemoryLimit() {
        return Configure::read('Queue.default_memory_limit', "512M");
    }

    /**
     * Timeout in seconds, after which the Task is reassigned to a new worker
     * if not finished successfully.
     * This should be high enough that it cannot still be running on a zombie worker (>> 2x).
     *
     * @return int
     */
    public static function defaultPhpTimeout() {
        return Configure::read('Queue.default_timeout_php', 0); // worker_max_run_time * 100
    }

    /**
     * Timeout in seconds, after which the Task is reassigned to a new worker
     * if not finished successfully.
     * This should be high enough that it cannot still be running on a zombie worker (>> 2x).
     *
     * @return int
     */
    public static function defaultWorkerTimeout() {
        return Configure::read('Queue.default_worker_timeout', 600); // 10min
    }

    /**
     * @return int
     */
    public static function defaultWorkerRetries() {
        return Configure::read('Queue.default_worker_retries', 1);
    }

    /**
     * Seconds of running time after which the worker will terminate (0 = unlimited)
     *
     * @return int
     */
    public static function workerMaxRuntime() {
        return Configure::read('Queue.worker_max_runtime', 120);
    }

    /**
     * @return int
     */
    public static function workerSleeptime() {
        return Configure::read('Queue.worker_sleep_time', 10);
    }

    /**
     * Minimum number of seconds before a cleanup run will remove a completed task (set to 0 to disable)
     *
     * @return int
     */
    public static function cleanupTimeout() {
        return Configure::read('Queue.cleanup_timeout', 2592000); // 30 days
    }

    /**
     * @return int
     */
    public static function workersMax() {
        return Configure::read('Queue.workers_max', 3);
    }

    /**
     * @return int
     */
    public static function cleanProb() {
        return Configure::read('Queue.clean_olds_prob', 10);
    }

    /**
     * @param string[] $tasks
     *
     * @return array
     * @throws \RuntimeException
     */
    public static function taskConfig(array $tasks): array {
        $config = [];

        foreach ($tasks as $task) {

            /**
             * @TODO Fix this
             */
//            if ($task === 'Queue.QueueJob' || $task === 'Queue.Queue')
//                continue;


            $className = App::className($task, 'Shell/Task', 'Task');
            if (!$className) {
                throw new RuntimeException('Cannot find class name for task `' . $task . '`');
            }
            [$pluginName, $taskName] = pluginSplit($task);

            /** @var \Queue\Shell\Task\QueueJob $taskObject */
            $taskObject = new $className();


            /**
             * @TODO Fix, fix, fix, and more fix !!!
             * @warning If you read this code your head may explode, try not to read much !
             */
            $config[$taskName]['name'] = substr($taskName, 6);
            $config[$taskName]['plugin'] = $pluginName;
            $config[$taskName]['timeout'] = $taskObject->timeout ?? static::defaultWorkerTimeout();
            $config[$taskName]['retries'] = $taskObject->retries ?? static::defaultWorkerRetries();
            $config[$taskName]['rate'] = $taskObject->rate;
            $config[$taskName]['cpu_percentage_costs'] = $taskObject->cpu_percentage_costs;
            $config[$taskName]['unique'] = $taskObject->unique;
        }

        return $config;
    }

}
