<?php

namespace Token27\Queue\Shell\Task;

# CAKEPHP

use Cake\Core\App;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
# PLUGIN
use Token27\Queue\Shell\Task\QueueTask;
use Token27\Queue\Shell\Task\AddInterface;
use Token27\Queue\Model\QueueException;

/**
 * Execute a Local command on the server.
 *
 * @property \Queue\Model\Table\QueueProcessesTable $QueueProcesses
 */
class QueueExecuteShellTask extends QueueTask implements AddInterface {

    /**
     * Add functionality.
     * Will create one example task in the queue, which later will be executed using run();
     * 
     * To invoke from CLI execute:
     * - bin/cake queue add Execute <command> [<param1>] [<param2>] ...
     *
     * @return void
     */
    public function add() {
        $this->hr();
        $this->info('CakePHP Queue Execute Shell task.');
        $this->hr();
        if (!isset($this->params['command']) || $this->params['command'] === "") {
            $this->out('This will run an cakephp shell command on the Server.');
            $this->out('The task is mainly intended to serve as a kind of buffer for programm calls from a CakePHP application.');
            $this->out(' ');
            $this->info('Call like this:');
            $this->out('    bin/cake queue add ExecuteShell *cakephp_shell_name* --command=""');
            $this->out('    bin/cake queue add ExecuteShell *cakephp_shell_name* --command="" --plugin="" ');
            $this->out(' ');
            $this->info('For command and plugin name use " around it. ');
            $this->out('E.g.');
            $this->out('        `bin/cake queue add ExecuteShell Queue --command="list all" --plugin="Queue"`.');
            $this->out('        `bin/cake queue add ExecuteShell QueueWorker --command="list" --plugin="Queue"`.');
            $this->out(' ');
            $this->info('Help:');
            $this->out('    bin/cake queue add ExecuteShell --help');
            return;
        }

        $plugin_name = null;
        if (isset($this->params['plugin'])) {
            if ($this->params['plugin'] !== "") {
                $plugin_name = $this->params['plugin'];
            }
            unset($this->params['plugin']);
        }
        $params = explode(' ', $this->params['command']);

        $additional_data = [
            'name' => $this->args[1],
            'plugin' => $plugin_name,
            'params' => $params,
        ];

        $this->QueueTasks->addQueueTask('ExecuteShell', $additional_data);
        $this->success('OK, queue task created, now run the worker');
    }

    /**
     * Run function.
     * This function is executed, when a worker is executing a task.
     * The return parameter will determine, if the task will be marked completed, or be requeue.
     *
     * @param string $queueTaskId The id of the QueueTask entity
     * @param array $additional_data The array passed to QueueTasksTable::addQueueTask()
     * @throws \Queue\Model\QueueException
     * @return void
     */
    public function run(string $queueTaskId, array $additional_data): void {

        $additional_data += [
            'name' => null,
            'plugin' => null,
            'params' => [],
            'redirect' => true,
            'escape' => false,
            'log' => false,
            'accepted' => [static::CODE_SUCCESS],
        ];
        $cakeRoot = ROOT;
        $cakeRoot .= DS . 'bin';
        $cakeBin = $cakeRoot . DS . 'cake';

        $command_cake_shell = $cakeBin . ' ';
        if ($additional_data['plugin']) {
            $command_cake_shell .= Inflector::camelize($additional_data['plugin']) . '.';
        }
        $command_cake_shell .= $additional_data['name'];

        if ($additional_data['params']) {
            $params = $additional_data['params'];
            if ($additional_data['escape']) {
                foreach ($params as $key => $value) {
                    $params[$key] = escapeshellcmd($value);
                }
            }
            $command_cake_shell .= ' ' . implode(' ', $params);
        }

        $this->out('Executing cakephp shell: `' . $command_cake_shell . '`');
        if ($additional_data['redirect']) {
            $command_cake_shell .= ' 2>&1';
        }

        exec($command_cake_shell, $output, $exitCode);
        $this->nl();
        $this->out($output);

        if ($additional_data['log']) {
            $this->loadModel('Queue.QueueTasks');
            $this->loadModel('Queue.QueueWorkers');
            $server = $this->QueueWorkers->buildServerString();
            $this->log($server . ': `' . $command_cake_shell . '` exits with `' . $exitCode . '` and returns `' . print_r($output, true) . '`' . PHP_EOL . 'Data : ' . print_r($additional_data, true), 'info');

            $queueWorkerId = "";
            $queueTask = $this->QueueTasks->getQueueTask($queueTaskId);
            if (!empty($queueTask)) {
                if (isset($queueTask['queue_worker_id']) && $queueTask['queue_worker_id'] != null) {
                    $queueWorkerId = $queueTask['queue_worker_id'];
                }
            }
            $this->QueueLogs->addLog($queueTaskId, $queueWorkerId, __('Result of cakephp shell: "') . $command_cake_shell . '"', intval($exitCode), serialize($output));
        }

        $acceptedReturnCodes = $additional_data['accepted'];
        $success = !$acceptedReturnCodes || in_array($exitCode, $acceptedReturnCodes, true);
        if (!$success) {
            $this->err('Error (code ' . $exitCode . ')', static::VERBOSE);
        } else {
            $this->success('Success (code ' . $exitCode . ')', static::VERBOSE);
        }

        if (!$success) {
            throw new QueueException('Failed with error code ' . $exitCode . ': `' . $command_cake_shell . '`');
        }
    }

}
