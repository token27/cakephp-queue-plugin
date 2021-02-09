<?php

namespace Queue\Shell;

# CAKEPHP

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Cake\I18n\Number;
use Cake\Utility\Text;
use Cake\I18n\FrozenTime;
use Cake\Utility\Inflector;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\Datasource\Exception\RecordNotFoundException;

# PLUGIN 
use Queue\TaskFinder;
use Queue\Config as WorkerConfig;
use Queue\Model\Entity\QueueTask;
use Queue\Model\Entity\QueueWorker;
use Queue\Model\ProcessEndingException;
use Queue\Model\QueueException;
use Queue\Shell\Task\QueueTaskInterface;
#
use Queue\TaskJob;
//use Queue\Transport\JobInterface;
//use Queue\Transport\QueueJobTaskInterface;
//use Queue\Transport\QueueJobTask;
//use Task\AddInterface;
//use App\QueueTasks\QueueProgressJobExampleTask;
# OTHERS
use RuntimeException;
use Throwable;
use InvalidArgumentException;

declare(ticks=1);

/**
 * Main shell to init and run queue workers.
 *
 * @author 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @property \Queue\Model\Table\QueueTasksTable $QueueTasks
 * @property \Queue\Model\Table\QueueWorkersTable $QueueWorkers
 */
class QueueWorkerShell extends Shell {

    /**
     * @var string
     */
    protected $modelClass = 'Queue.QueueTasks';

    /**
     * @var array|null
     */
    protected $_taskConf;

    /**
     * @var int
     */
    protected $_taskStartTime = 0;

    /**
     * @var int
     */
    protected $_taskFinishTime = 0;

    /**
     * @var bool
     */
    protected $_exit = false;

    /**
     * @var \Queue\Model\Task\QueueWorker $workerObject 
     */
    protected $_worker;

    /**
     * @var string|null
     */
    protected $_pid;

    /**
     * @var int
     */
    protected $_phpTimeout = 0;

    /**
     * @var int
     */
    protected $_workerStartTime = 0;

    /**
     * @var int
     */
    protected $_workerTimeout = 900;

    /**
     * Get option parser method to parse commandline options
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser {

        $parser = parent::getOptionParser();

        $parser->addSubcommand('memory', [
            'help' => __('Show current memory usage.'),
            'parser' => [
                'description' => [
                    __('Use this command to SHOW the current memory usage.'),
                ],
            ]
        ]);

        $parser->addSubcommand('run', [
            'help' => __('Run a worker.'),
            'parser' => [
                'description' => [
                    __('Use this command to RUN a workers.'),
                ],
            ]
        ]);

        $parser->addSubcommand('list', [
            'help' => __('Show all the workers.'),
            'parser' => [
                'description' => [
                    __('Use this command to SHOW all the workers.'),
                ],
                'arguments' => [
                    'type' => [
                        'help' => __('The type of workers to show.'),
                        'required' => false,
                        'choices' => [
                            'all',
                            'app',
                            'plugin'
                        ]
                    ],
                ],
            ]
        ]);

        $parser->addSubcommand('view', [
            'help' => __('View worker information.'),
            'parser' => [
                'description' => [
                    __('Use this command to view worker information.'),
                ],
                'arguments' => [
                    'queue-worker' => [
                        'help' => __('The worker id/pid/name of the database.'),
                        'required' => true
                    ],
                    'server-name' => [
                        'help' => __('The worker server name.'),
                        'required' => false
                    ],
                ],
            ]
        ]);

        $parser->addSubcommand('stats', [
            'help' => __('View worker stats.'),
            'parser' => [
                'description' => [
                    __('Use this command to VIEW worker STATS.'),
                ],
                'arguments' => [
                    'queue-worker-id' => [
                        'help' => __('The worker id of the database.'),
                        'required' => true
                    ],
                ],
            ]
        ]);

        $parser->addSubcommand('kill', [
            'help' => __('Kill worker process.'),
            'parser' => [
                'description' => [
                    __('Use this command to kill process and terminate the worker proccess.'),
                ],
                'arguments' => [
                    'queue-worker' => [
                        'help' => __('The worker id/pid/name of the database.'),
                        'required' => true
                    ],
                    'server-name' => [
                        'help' => __('The worker server name.'),
                        'required' => false
                    ],
                ],
            ]
        ]);

        $parser->addSubcommand('hardreset', [
            'help' => __('Kill all worker process (allow multiserver).'),
            'parser' => [
                'description' => [
                    __('Use this command to kill all workers process and terminate.'),
                ],
                'arguments' => [
                    'type' => [
                        'help' => __('The type of workers to kill.'),
                        'required' => false,
                        'choices' => [
                            'all',
                            'server',
                        ]
                    ],
                ],
            ]
        ]);

        return $parser;
    }

    /**
     * Overwrite shell initialize to dynamically load all Queue Related Tasks.
     *
     * @return void
     */
    public function initialize(): void {
        WorkerConfig::loadPluginConfiguration();
        $this->taskFinder = new TaskFinder();
//        var_dump($taskFinder);
//        exit();
        $this->tasks = $this->taskFinder->getAllTasks();
        $this->loadModel('Queue.QueueTasks');
        $this->loadModel('Queue.QueueWorkers');
        parent::initialize();
    }

    /**
     * @return void
     */
    public function startup(): void {
        if ($this->param('quiet')) {
            $this->interactive = false;
        }
        parent::startup();
    }

    /**
     * Main
     *
     * @access public
     */
    public function main() {
        $this->out($this->OptionParser->help());
        return true;
    }

    /**
     * Run
     *
     * @access public
     */
    public function memory() {
        $this->out('  ');
        $this->hr();
        $this->info(' -> Current memory usage');
        $memory = $this->_memoryUsage();
        $this->warn('  ! Memory usage: ' . $memory);
    }

    /**
     * Run
     *
     * @access public
     */
    public function run() {

        try {
            $this->_initPid();
        } catch (PersistenceFailedException $exception) {
            $this->err($exception->getMessage());
            $limit = (int) WorkerConfig::workersMax();
            if ($limit) {
                $this->out('Cannot start worker: Too many workers already/still running on this server (' . $limit . '/' . $limit . ')');
            }

            $this->QueueWorkers->cleanTimeouts();

            $this->QueueWorkers->cleanEnded();

            return static::CODE_ERROR;
        }

        $this->out('  ');
        $this->hr();
        $this->info(' -> Worker settings');
        $this->setExit(false);
        $startTime = time();
        $groups = $this->_stringToArray((string) $this->param('group'));
        $types = $this->_stringToArray((string) $this->param('type'));

        $this->_configMemoryLimit();
        $this->_configWorkerTimeout();
        $this->_configPhpTimeout();
        $this->setWorkerStartTime(time());

        if (!empty($groups)) {
            $this->out('  * Groups: ' . implode(",", $groups));
        } else {
            $this->success('  * Groups: ALL');
        }
        if (!empty($types)) {
            $this->out('  * Groups: ' . implode(",", $types));
        } else {
            $this->success('  * Types: ALL');
        }
        $this->out('  * Clean old task/workers probability: ' . WorkerConfig::cleanProb() . '%');
        $this->hr();
        $this->info(' -> Functions');

        /**
         * @note Enable Garbage Collector (PHP >= 5.3)
         */
        if (function_exists('gc_enable')) {
            gc_enable();
            $this->success('  * gc_enable OK.');
        } else {
            $this->out('  * gc_enable not exists.');
        }

        if (function_exists('getmygid')) {
            $this->success('  * getmygid OK.');
        } else {
            $this->out('  * getmygid not exists.');
        }

        /**
         * @note pcntl_signal
         */
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [&$this, '_exit']);
            pcntl_signal(SIGINT, [&$this, '_abort']);
            pcntl_signal(SIGTSTP, [&$this, '_abort']);
            pcntl_signal(SIGQUIT, [&$this, '_abort']);
            $this->success('  * pcntl_signal OK.');
        } else {
            $this->out('  * pcntl_signal not exists.');
        }

        /**
         * @note posix_kill
         */
        if (function_exists('posix_kill')) {
            $this->success('  * posix_kill OK.');
        } else {
            $this->out('  * posix_kill not exists.');
        }

        /**
         * @note posix_getpid
         */
        if (function_exists('posix_getpid')) {
            $this->success('  * posix_getpid OK.');
        } else {
            $this->out('  * posix_getpid not exists.');
        }


        $this->hr();
        $this->info(' -> Timeouts');
        $this->warn('  ! Worker Runtime: ' . $this->getWorkerTimeout() . ' seconds.');
        $this->warn('  ! PHP Timeout: ' . $this->getPhpTimeout() . ' seconds.');
        $this->hr();
        $memoryUsage = $this->_memoryUsage();
        $this->info(' -> Memory Limit');
        $this->warn('  ! Current Usage: ' . $memoryUsage);
        $this->warn('  ! Limit: ' . WorkerConfig::defaultMemoryLimit());
        if (!$this->getWorkerTimeout()) {
            $this->err('  !! Be careful with this configuration, the worker can be in zombie mode.');
        } else {
            if ($this->getPhpTimeout() < $this->getWorkerTimeout()) {
                $this->err('  !! Be careful with this configuration, PHP timeout cannot be below than worker run time.');
            }
        }

        $this->hr();
        $this->info(' -> Starting worker <-');
        $this->out('  * Server: ' . $this->QueueWorkers->buildServerString());
        $this->out('  * Id: ' . $this->_worker->id);
        $this->out('  * Name: ' . $this->_worker->name);
        $this->out('  * Pid: ' . $this->_worker->pid);
        $this->out('  ');
        sleep(mt_rand(2, 7));

        while (!$this->isExit()) {
            try {
                $this->_updatePid($this->_worker->pid);
                $this->_waitingPid($this->_worker->pid);
            } catch (RecordNotFoundException $exception) {
                // Manually killed
                $this->setExit(true);
//                $this->_pid = null;
//                $pid = null;
                $this->err(' !! Record not found: ' . $exception->getMessage());
                continue;
            } catch (ProcessEndingException $exception) {
                // Soft killed, e.g. during deploy update
                $this->setExit(true);
//                $this->_pid = null;
//                $pid = null;
                $this->err(' !! Process Ending: ' . $exception->getMessage());
                continue;
            }

            if ($this->param('verbose')) {
                $this->_log('runworker', $pid, false);
            }
            $this->info(' -> Looking for Queue Task...');
            $this->out('  * ' . date('H:i:s d-m-Y'));
            $queueTask = $this->QueueTasks->requestQueueTask($this->_getTaskConf(), $groups, $types, $this->_worker->toArray());


            if ($queueTask) {
                $this->_workingPid($this->_worker->pid);
                $this->out(' -> Success, queue task found. <- ');
                $this->out(' ');




                $this->runTask($queueTask);
            } elseif (Configure::read('Queue.worker_exit_when_nothing_todo')) {
                $this->warn('  ! Nothing to do, exiting.');
                $this->setExit(true);
                $this->_terminatePid($pid);
            } else {
                $this->out('  * Nothing to do, sleeping ' . WorkerConfig::workerSleeptime() . ' seconds.');
                sleep(WorkerConfig::workerSleeptime());
            }


            /**
             * @note check if we are over the maximum runtime and end processing if so.
             */
            if ($this->isRuntimeReached()) {
                $this->err(' !! Runtime time reached, ' . (time() - $this->getWorkerStartTime()) . ' of ' . $this->getWorkerTimeout() . ' seconds, terminating...');
                $this->setExit(true);
                $this->_terminatePid($pid);
            } else {
                $this->success(' -> Running time ' . $this->getWorkerTimeRunning() . ' of ' . $this->getWorkerTimeout() . ' seconds, continue...');
            }

            /**
             * @note Check if the status has been changed from another server. (multiserver)
             */
            if (!$this->isExit()) {
                if ($this->isTerminatedRequired($this->_worker->pid)) {
                    $this->err(' !! Remote signall kill recieved, terminating...');
                    $this->setExit(true);
                    $this->_terminatePid($this->_worker->pid);
                }
            }


            /**
             * @note Cleanup
             */
            if ($this->isExit() || $this->isCleanupTime()) {
                $this->hr();
                $this->warn('  ! Performing cleanup');
                $this->info(' -> Cleaning olds workers...');
                $this->QueueWorkers->cleanEnded($this->getWorkerTimeout());
                $this->QueueWorkers->cleanTimeouts();
                $this->info(' -> Cleaning olds tasks...');
                $this->QueueTasks->cleanOldTasks();
//                $this->QueueTasks->cleanTimeouts($this->getWorkerTimeout(), $pid);
                $this->success(' -> Success, cleanup finished. <-');
            }
            $this->hr();
        }

        $this->_deletePid($this->_worker->pid);

        if ($this->param('verbose')) {
            $this->_log('endworker', $this->_worker->pid);
        }

        return static::CODE_SUCCESS;
    }

    /**
     * 
     * @return bool
     */
    public function isRuntimeReached() {
        if (intval($this->getWorkerTimeout()) > 0) {
            return (bool) ((time() - $this->getWorkerStartTime()) >= $this->getWorkerTimeout());
        }
        return false;
    }

    /**
     * 
     * @return bool
     */
    public function isCleanupTime() {
        return (bool) (mt_rand(0, 100) > (100 - (int) WorkerConfig::cleanProb()));
    }

    /**
     * 
     * @return boolean
     */
    public function isTerminatedRequired($pid) {
        $terminate = false;
        $worker_database = $this->QueueWorkers->getWorker($pid, $this->QueueWorkers->buildServerString());
        if ($worker_database) {
            if ($worker_database['status'] == 3) {
                $terminate = true;
            }
        }
        return $terminate;
    }

    /**
     * @param \Queue\Model\Entity\QueueTask $queueTask     
     * @return void
     */
    protected function runTask(QueueTask $queueTask) {
        $this->hr();
        $this->hr();
        $this->hr();
        $this->out(' ');
        $this->info('  -> Running Task: "' . $queueTask->name . '"');
        $this->out(' ');
        $this->hr();
        $this->_log('task ' . $queueTask->name . ', id ' . $queueTask->id, $this->_worker->pid, false);
        $taskName = 'Queue' . $queueTask->name;
        $taskTimeStart = time();
        $this->setTaskStartTime(time());

        /**
         * @TODO Funcition callback before start
         */
//        if ($task instanceof QueueTaskCallbackInterface) {
//             QueueTaskCallbackInterface::class
//        }

        $return = $failureMessage = null;
        try {

            $additional_data = [];
            if ($queueTask->additional_data !== null && $queueTask->additional_data !== '') {
                if (!is_array($queueTask->additional_data)) {
                    $additional_data = (array) unserialize($queueTask->additional_data);
                } else {
                    $additional_data = (array) $queueTask->additional_data;
                }
            }

            /**
             *  @var \Queue\Shell\Task\QueueTask $task 
             */
            $task = $this->{$taskName};
            if (!$task instanceof QueueTaskInterface) {
                echo 'Task must implement ' . QueueTaskInterface::class . PHP_EOL;
//                throw new RuntimeException('Task must implement ' . QueueJobTask::class);
            } else {
                echo QueueTaskInterface::class . ' OK ' . PHP_EOL;
            }
            if (!$task instanceof QueueTask) {
                echo 'Task must implement ' . QueueTask::class . PHP_EOL;
//                throw new RuntimeException('Task must implement ' . QueueJobTask::class);
            } else {
                echo QueueTask::class . ' OK ' . PHP_EOL;
            }
            if (!$task instanceof AddInterface) {
                echo 'Task must implement ' . AddInterface::class . PHP_EOL;
//                throw new RuntimeException('Task must implement ' . QueueJobTask::class);
            } else {
                echo AddInterface::class . ' OK ' . PHP_EOL;
            }

            var_dump($queueTask);
            var_dump($this->_worker);
            var_dump($task);
            sleep(20);

            $this->QueueTasks->markTaskInProgress($queueTask, $this->_worker->id);

//            $job_data = $additional_data;
            $taskJob = new TaskJob($queueTask, $this->_worker, $task);
            $taskJob->run();

//            var_dump($taskJob);
//            exit();
            $task->run($queueTask->id, (array) $additional_data);
        } catch (Throwable $e) {
            $return = false;

            $failureMessage = get_class($e) . ': ' . $e->getMessage();
            if (!($e instanceof QueueException)) {
                $failureMessage .= "\n" . $e->getTraceAsString();
            }

            $this->_logError($taskName . ' (task ' . $queueTask->id . ')' . "\n" . $failureMessage, $this->_worker->pid);
        }

        if ($return === false) {
            $this->QueueTasks->markTaskFailed($queueTask, $failureMessage);
            $failedStatus = $this->QueueTasks->getFailedStatus($queueTask, $this->_getTaskConf());
            $this->_log('task ' . $queueTask->name . ', id ' . $queueTask->id . ' failed and ' . $failedStatus, $this->_worker->pid);
            $this->err(' !! Task did not finish, ' . $failedStatus . ' after try ' . $queueTask->failed . '.');

            return;
        }
        $taskTimeEnd = time();
        $duration = intval($taskTimeEnd - $taskTimeStart);

        $this->QueueTasks->markTaskDone($queueTask);
        $this->hr();
        $this->out(' ');
        $this->success(' -> Success, "' . $queueTask->name . '" Task Finished, working time ' . $duration . ' second(s). <-');
        $this->out(' ');
        $this->hr();
        $this->hr();
        $this->hr();

        /**
         * @TODO Funcition callback after end
         */
//        if ($task instanceof QueueTaskCallbackInterface) {
//        }
    }

    /**
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     */

    /**
     * List
     *
     * @access public
     */
    public function list() {
        $this->out('  ');
        $this->hr();
        $this->info(' -> Listing workers');
        $workers_running = $this->QueueWorkers->getRunning();
        $this->hr();
        if (!empty($workers_running)) {
            $this->success(' -> Running workers: ' . count($workers_running));
            foreach ($workers_running as $worker_running) {
                $this->out('  * (' . $worker_running['server_name'] . ') ' . $worker_running['pid'] . '  ');
            }
        } else {
            $this->err(' -> No one worker was running.');
        }

        $workers_timeouts = $this->QueueWorkers->getTimeouts();
        $this->hr();
        if (!empty($workers_timeouts)) {
            $this->warn(' -> Timeouts workers: ' . count($workers_timeouts));
            foreach ($workers_timeouts as $worker_timeout) {
                $this->out('  * (' . $worker_timeout['server_name'] . ') ' . $worker_timeout['pid'] . '  ');
            }
        } else {
            $this->success(' -> No one worker in timeout.');
        }
    }

    /**
     * View
     *
     * @access public
     */
    public function view() {
        $this->out('  ');
        $this->hr();
        $server_name = "";
        $queue_worker_id = "";
        $worker_database = null;
        if (isset($this->args) && !empty($this->args)) {
            $queue_worker_id = $this->args[0];

            $this->info(' -> Searching worker');
            $this->out('  * By: ' . $queue_worker_id);
            if (isset($this->args[1])) {
                $server_name = $this->args[1];
                $this->out('  * Server: ' . $server_name);
            }
            $this->hr();

            $worker_database = $this->QueueWorkers->getWorker($queue_worker_id, $server_name);
            if ($worker_database) {
                $this->info(' -> Worker information');
                $this->out('  * Id: ' . $worker_database['id']);
                $this->out('  * Pid: ' . $worker_database['pid']);
                $this->out('  * Server: ' . $worker_database['server_name']);
                $this->out('  * Name: ' . $worker_database['name']);
                $this->out('  * Tasks Logs: ' . $worker_database['queue_log_count']);
                $this->out('  * Tasks Processed: ' . $worker_database['queue_task_count']);
                $this->hr();
                if ($worker_database['status'] == 1) {
                    $this->success('  * Status: working');
                } else if ($worker_database['status'] == 2) {
                    $this->warn('  * Status: waiting');
                } else if ($worker_database['status'] == 3) {
                    $this->err('  * Status: terminated');
                }
                $this->out('  * Created: ' . $worker_database['created']->format('H:i:s d-m-Y'));
                $this->info('  * Modified: ' . $worker_database['modified']->format('H:i:s d-m-Y'));
                $this->out('  * Timeout: ' . $this->getWorkerTimeout() . ' seconds.');

                if ($worker_database['status'] == 3) {
                    $terminated_at = null;
                    if (isset($worker_database['terminated_at']) && $worker_database['terminated_at']) {
                        $terminated_at = $worker_database['terminated_at']->format('H:i:s d-m-Y');
                    }
                    $this->err('  * Terminated ' . $terminated_at);
                } else {
                    $running_time = time() - strtotime($worker_database['created']->format('Y-m-d H:i:s'));
                    $this->out('  * Running time: ' . $running_time . ' seconds.');
                    if ($running_time > $this->getWorkerTimeout()) {
                        $this->err('  ! Worker in timeout, require terminate.');
                    } else {
                        $time_left = $this->getWorkerTimeout() - $running_time;
                        $this->out('  * Time left: ' . $time_left . ' seconds.');
                    }
                }
            } else {
                $this->warn('  ! Worker not found.');
            }
            $this->hr();
        }
    }

    /**
     * Stats
     *
     * @access public
     */
    public function stats() {
        $this->out(' ');
    }

    /**
     * Kill
     *
     * @access public
     */
    public function kill() {
        $this->out('  ');
        $this->hr();
        $server_name = "";
        $queue_worker_id = "";
        $worker_database = null;
        if (isset($this->args) && !empty($this->args)) {
            $queue_worker_id = $this->args[0];

            $this->info(' -> Searching worker');
            $this->out('  * By: ' . $queue_worker_id);
            if (isset($this->args[1])) {
                $server_name = $this->args[1];
                $this->out('  * Server: ' . $server_name);
            } else {
                $this->out('  * Server: ' . $this->QueueWorkers->buildServerString());
            }
            $this->hr();

            $worker_database = $this->QueueWorkers->getWorker($queue_worker_id, $server_name);
            if ($worker_database) {
                if ($worker_database['server_name'] == $this->QueueWorkers->buildServerString()) {
                    $this->QueueWorkers->killWorkerProcess($worker_database['pid']);
                    $this->_deletePid($worker_database['pid']);
                    $this->success(' -> Success, worker killed: (' . $worker_database['server_name'] . ') ' . $worker_database['name'] . ' - ' . $worker_database['pid'] . ' <-');
                } else {
                    $this->warn('  ! The worker is not in this server (' . $this->QueueWorkers->buildServerString() . ').');
                    $this->warn('  ! Sending remote kill...');
                    $this->QueueWorkers->endWorker($worker_database['pid']);
                    sleep(3);
                    $c = 0;
                    $max = mt_rand(7, 10);
                    $killed = false;
                    while ($c < $max) {
                        $c++;
                        $worker_db = $this->QueueWorkers->getWorker($worker_database['pid']);
                        if ($worker_db) {
                            $this->warn('  ! Worker is not killed.');
                            if ($worker_database['status'] == 1) {
                                $this->success('  * Status: working');
                            } else if ($worker_database['status'] == 2) {
                                $this->warn('  * Status: waiting');
                            } else if ($worker_database['status'] == 3) {
                                $this->err('  * Status: terminated');
                            }
                            sleep(mt_rand(2, 7));
                        } else {
                            $killed = true;
                            $c = $max;
                        }
                    }
                    if ($killed) {
                        $this->success(' -> Success, remote worker killed: (' . $worker_database['server_name'] . ') ' . $worker_database['name'] . ' - ' . $worker_database['pid'] . ' <-');
                    }
                }
            } else {
                $this->warn('  ! Worker not found.');
            }
        }
    }

    /**
     * Hard Reset
     *
     * @access public
     */
    public function hardreset() {
        $this->out('  ');
        $this->hr();
        $server_name = "";
        $queue_worker_id = "";
        $worker_database = null;
        if (isset($this->args) && !empty($this->args)) {
            $queue_worker_id = $this->args[0];

            $this->info(' -> Searching worker');
            $this->out('  * By: ' . $queue_worker_id);
            if (isset($this->args[1])) {
                $server_name = $this->args[1];
                $this->out('  * Server: ' . $server_name);
            } else {
                $this->out('  * Server: ' . $this->QueueWorkers->buildServerString());
            }
            $this->hr();

            $worker_database = $this->QueueWorkers->getWorker($queue_worker_id, $server_name);
            if ($worker_database) {
                if ($worker_database['server_name'] == $this->QueueWorkers->buildServerString()) {
                    $this->QueueWorkers->killWorkerProcess($worker_database['pid']);
                    $this->_deletePid($worker_database['pid']);
                    $this->success(' -> Success, worker killed: (' . $worker_database['server_name'] . ') ' . $worker_database['name'] . ' - ' . $worker_database['pid'] . ' <-');
                } else {
                    $this->warn('  ! The worker is not in this server (' . $this->QueueWorkers->buildServerString() . ').');
                    $this->warn('  ! Sending remote kill...');
                    $this->QueueWorkers->endWorker($worker_database['pid']);
                    sleep(3);
                    $c = 0;
                    $max = mt_rand(7, 10);
                    $killed = false;
                    while ($c < $max) {
                        $c++;
                        $worker_db = $this->QueueWorkers->getWorker($worker_database['pid']);
                        if ($worker_db) {
                            $this->warn('  ! Worker is not killed.');
                            if ($worker_database['status'] == 1) {
                                $this->success('  * Status: working');
                            } else if ($worker_database['status'] == 2) {
                                $this->warn('  * Status: waiting');
                            } else if ($worker_database['status'] == 3) {
                                $this->err('  * Status: terminated');
                            }
                            sleep(mt_rand(2, 7));
                        } else {
                            $killed = true;
                            $c = $max;
                        }
                    }
                    if ($killed) {
                        $this->success(' -> Success, remote worker killed: (' . $worker_database['server_name'] . ') ' . $worker_database['name'] . ' - ' . $worker_database['pid'] . ' <-');
                    }
                }
            } else {
                $this->warn('  ! Worker not found.');
            }
        }
    }

    /*     * *
     * 
     * 
     * 
     */

    /**
     * @return string
     */
    protected function _initPid() {
        $pid = $this->_retrievePid();
        $this->_worker = $this->QueueWorkers->add($pid);
        $this->_pid = $pid;
        return $pid;
    }

    /**
     * @return string
     */
    protected function _retrievePid() {
        if (function_exists('posix_getpid')) {
            $pid = (string) posix_getpid();
        } else if (function_exists('getmypid')) {
            $pid = (string) getmypid();
        } else {
            $pid = $this->QueueWorkers->key();
        }
        return $pid;
    }

    /**
     * @param string $pid
     *
     * @return void
     */
    protected function _updatePid($pid) {
        $this->QueueWorkers->update($pid);
    }

    /**
     * @param string $pid
     *
     * @return void
     */
    protected function _waitingPid($pid) {
        $this->QueueWorkers->waiting($pid);
    }

    /**
     * @param string $pid
     *
     * @return void
     */
    protected function _workingPid($pid) {
        $this->QueueWorkers->working($pid);
    }

    /**
     * @param string $pid
     *
     * @return void
     */
    protected function _terminatePid($pid) {
        $this->QueueWorkers->terminate($pid);
    }

    /**
     * @param string|null $pid
     *
     * @return void
     */
    protected function _deletePid($pid) {
        if (!$pid) {
            $pid = $this->getPid();
        }
        if (!$pid) {
            return;
        }

        $this->QueueWorkers->remove($pid);
    }

    /**
     * @return string
     */
    protected function _timeNeeded() {
        $diff = $this->_time() - $this->_time($this->getTaskStartTime());
        $seconds = max($diff, 1);

        return $seconds . 's';
    }

    /**
     * @param int|null $providedTime
     *
     * @return int
     */
    protected function _time($providedTime = null) {
        if ($providedTime) {
            return $providedTime;
        }

        return time();
    }

    /**
     * @param string|null $param
     * @return string[]
     */
    protected function _stringToArray($param) {
        if (!$param) {
            return [];
        }

        $array = Text::tokenize($param);

        return array_filter($array);
    }

    /**
     * Returns a List of available QueueTasks and their individual configuration.
     *
     * @return array
     */
    protected function _getTaskConf() {
        if (!is_array($this->_taskConf)) {
            /** @var array $tasks */
            $tasks = $this->tasks;
            $this->_taskConf = WorkerConfig::taskConfig($tasks);
        }

        return $this->_taskConf;
    }

    /**
     * Signal handling to queue worker for clean shutdown
     *
     * @param int $signal
     * @return void
     */
    protected function _exit($signal) {
        $this->_exit = true;
    }

    /**
     * Signal handling for Ctrl+C
     *
     * @param int $signal
     * @return void
     */
    protected function _abort($signal) {
        $this->_deletePid($this->_pid);
        exit(1);
    }

    protected function _configMemoryLimit() {
        $memoryLimit = WorkerConfig::defaultMemoryLimit();
        try {
            ini_set("memory_limit", "$memoryLimit");
        } catch (Throwable $ex) {
            
        } catch (Exception $ex) {
            
        }
    }

    /**
     * Makes sure accidental overriding isn't possible, uses worker_max_runtime times 100 by default.
     * If available, uses worker_timeout config directly.
     *
     * @return void
     */
    protected function _configPhpTimeout() {
        $timeLimit = (int) WorkerConfig::defaultPhpTimeout();

        $this->_configWorkerTimeout();

        if (!$timeLimit) {
            $timeLimit = $this->getWorkerTimeout() * 2;
        } else if ($timeLimit < $this->getWorkerTimeout()) {
            $timeLimit += $this->getWorkerTimeout();
        }

        $this->setPhpTimeout($timeLimit);

        try {
            set_time_limit($timeLimit);
            ini_set("set_time_limit", "$timeLimit");
            ini_set("max_execution_time", "$timeLimit");
        } catch (Throwable $ex) {
            
        } catch (Exception $ex) {
            
        }
    }

    /**
     * 
     * @return void
     */
    protected function _configWorkerTimeout() {
        $timeLimit = (int) WorkerConfig::workerMaxRuntime() * 100;
        if (WorkerConfig::defaultWorkerTimeout() !== null) {
            $timeLimit = (int) WorkerConfig::defaultWorkerTimeout();
        }
        if (!$this->getPhpTimeout()) {
            $this->setPhpTimeout(WorkerConfig::defaultPhpTimeout());
        }
        if ($this->getPhpTimeout() < $timeLimit) {
            $this->setPhpTimeout(($this->getPhpTimeout() + $timeLimit));
        }
        $this->setWorkerTimeout($timeLimit);
    }

    /**
     * Timestamped log.
     *
     * @param string $message Log type
     * @param string|null $pid PID of the process
     * @param bool $addDetails
     * @return void
     */
    protected function _log($message, $pid = null, $addDetails = true) {
        if (!Configure::read('Queue.log')) {
            return;
        }

        if ($addDetails) {
            $timeNeeded = $this->_timeNeeded();
            $memoryUsage = $this->_memoryUsage();
            $message .= ' [' . $timeNeeded . ', ' . $memoryUsage . ']';
        }

        if ($pid) {
            $message .= ' (pid ' . $pid . ')';
        }
        Log::write('info', $message, ['scope' => 'queue']);
    }

    /**
     * @param string $message
     * @param string|null $pid PID of the process
     * @return void
     */
    protected function _logError($message, $pid = null) {
        $timeNeeded = $this->_timeNeeded();
        $memoryUsage = $this->_memoryUsage();
        $message .= ' [' . $timeNeeded . ', ' . $memoryUsage . ']';

        if ($pid) {
            $message .= ' (pid ' . $pid . ')';
        }
        $serverString = $this->QueueWorkers->buildServerString();
        if ($serverString) {
            $message .= ' {' . $serverString . '}';
        }

        Log::write('error', $message);
    }

    /**
     * @return string Memory usage in MB.
     */
    protected function _memoryUsage() {
        $used = "";

        try {
            $limit = ini_get('memory_limit');
            $used = number_format(memory_get_peak_usage(true) / (1024 * 1024), 0) . 'MB';
            if ($limit !== '-1') {
                $used .= '/' . $limit;
            }
        } catch (Throwable $ex) {
            
        } catch (\Exception $ex) {
            
        }

        return $used;
    }

    /**
     * 
     *      FUNCTIONS IN PRODUCTION
     */

    /**
     * Gracefully end running workers when deploying.
     *
     * Use $in
     * - all: to end all workers on all servers
     * - server: to end the ones on this server
     *
     * @param string|null $in
     * @return void
     */
    public function endWorkers($in = null) {
        $workers = $this->QueueWorkers->getWorkers($in === 'server');
        if (!$workers) {
            $this->out('No workers found');

            return;
        }

        $this->out(count($workers) . ' workers:');
        foreach ($workers as $worker => $timestamp) {
            $this->out(' - ' . $worker . ' (last run @ ' . (new FrozenTime($timestamp)) . ')');
        }

        $options = array_keys($workers);
        $options[] = 'all';
        if ($in === null) {
            $in = $this->in('Worker', $options, 'all');
        }

        if ($in === 'all' || $in === 'server') {
            foreach ($workers as $worker => $timestamp) {
                $this->QueueWorkers->endWorker($worker);
            }

            $this->out('All ' . count($workers) . ' workers ended.');

            return;
        }

        $this->QueueTaks->endWorker((int) $in);
    }

    /**
     * @return void
     */
    public function killAllWorkers() {
        $workers = $this->QueueWorkers->getWorkers();
        if (!$workers) {
            $this->out('No workers found');

            return;
        }

        $this->out(count($workers) . ' workers:');
        foreach ($workers as $worker => $timestamp) {
            $this->out(' - ' . $worker . ' (last run @ ' . (new FrozenTime($timestamp)) . ')');
        }

        if (Configure::read('Queue.multiserver')) {
            $this->abort('Cannot kill by PID in multiserver environment.');
        }

        $options = array_keys($workers);
        $options[] = 'all';
        $in = $this->in('Worker', $options, 'all');

        if ($in === 'all') {
            foreach ($workers as $worker => $timestamp) {
                $this->QueueWorkers->killWorkerProcess((int) $worker);
            }

            return;
        }

        $this->QueueWorkers->killWorkerProcess((int) $in);
    }

    /**
     * 
     *      GETTER AND SETTERS
     * 
     */
    public function setExit(bool $_exit): void {
        $this->_exit = $_exit;
    }

    public function setTaskStartTime(int $time): void {
        $this->_taskStartTime = $time;
    }

    public function getTaskStartTime(): int {
        return $this->_taskStartTime;
    }

    public function setTaskStartFinish(int $time): void {
        $this->_taskFinishTime = $time;
    }

    public function getTaskStartFinish(): int {
        return $this->_taskFinishTime;
    }

    public function getTaskTimeRunning(int $finishTime = 0): int {
        $this->_taskFinishTime = time();
        if ($finishTime) {
            $this->_taskFinishTime = $finishTime;
        }
        return ($this->_taskFinishTime - $this->_taskStartTime);
    }

    public function isExit(): bool {
        return $this->_exit;
    }

    public function getPid(): ?string {
        return $this->_pid;
    }

    public function getPhpTimeout(): int {
        return $this->_phpTimeout;
    }

    protected function setPhpTimeout(int $_phpTimeout): void {
        $this->_phpTimeout = $_phpTimeout;
    }

    public function getWorkerTimeout(): int {
        return $this->_workerTimeout;
    }

    protected function setWorkerTimeout(int $_workerTimeout): void {
        $this->_workerTimeout = $_workerTimeout;
    }

    public function getWorkerStartTime(): int {
        return $this->_workerStartTime;
    }

    public function setWorkerStartTime(int $_workerStartTime): void {
        $this->_workerStartTime = $_workerStartTime;
    }

    public function getWorkerTimeRunning(int $time = 0): int {
        if (!$time) {
            $time = time();
        }
        return ($time - $this->_workerStartTime);
    }

    private function _loadPluginConfiguration() {
        if (file_exists(ROOT . DS . 'config' . DS . 'app_queue.php')) {
            Configure::load('app_queue');
        } else {
            Configure::load('Queue.app_queue');
        }
    }

}
