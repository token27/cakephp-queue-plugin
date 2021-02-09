<?php

namespace Queue\Shell\Task;

use Queue\Shell\Task\QueueTask;
use Queue\Shell\Task\AddInterface;
use Queue\Model\QueueException;

/**
 * Execute a Local command on the server.
 *
 * @property \Queue\Model\Table\QueueProcessesTable $QueueProcesses
 */
class QueueExecuteCommandTask extends QueueTask implements AddInterface {

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
        $this->info('CakePHP Queue Execute Command task.');
        $this->hr();
        if (!isset($this->params['command-params']) || $this->params['command-params'] === "") {
            $this->out('This will run an shell command on the Server.');
            $this->out('The task is mainly intended to serve as a kind of buffer for programm calls from a CakePHP application.');
            $this->out(' ');
            $this->info('Call like this:');
            $this->out('    bin/cake queue add ExecuteCommand <command> --command-params=""');
            $this->out(' ');
            $this->info('For commands with spaces use " around it.');
            $this->out('E.g. `bin/cake queue add ExecuteCommand ping --command-params="www.google.com -n 5 -w 90" --log=true`.');
            $this->out(' ');
            return;
        }
        $params = explode(' ', $this->params['command-params']);
        $log = isset($this->params['log']) ? boolval($this->params['command-params']) : false;
        $additional_data = [
            'command' => $this->args[1],
            'params' => $params,
            'log' => $log,
        ];

        $this->QueueTasks->addQueueTask('ExecuteCommand', $additional_data);
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
            'command' => null,
            'params' => [],
            'redirect' => true,
            'escape' => true,
            'log' => false,
            'accepted' => [static::CODE_SUCCESS],
        ];

        $command = $additional_data['command'];
        if ($additional_data['escape']) {
            $command = escapeshellcmd($additional_data['command']);
        }

        if ($additional_data['params']) {
            $params = $additional_data['params'];
            if ($additional_data['escape']) {
                foreach ($params as $key => $value) {
                    $params[$key] = escapeshellcmd($value);
                }
            }
            $command .= ' ' . implode(' ', $params);
        }

        $this->out('Executing command: `' . $command . '`');

        if ($additional_data['redirect']) {
            $command .= ' 2>&1';
        }

        exec($command, $output, $exitCode);
        $this->nl();
        $this->out($output);

        if ($additional_data['log']) {

            $this->loadModel('Queue.QueueWorkers');
            $this->loadModel('Queue.QueueLogs');

            $server = $this->QueueWorkers->buildServerString();
            $this->log($server . ': `' . $command . '` exits with `' . $exitCode . '` and returns `' . print_r($output, true) . '`' . PHP_EOL . 'Data : ' . print_r($additional_data, true), 'info');

            $queueWorkerId = "";
            $queueTask = $this->QueueTasks->getQueueTask($queueTaskId);
            if (!empty($queueTask)) {
                if (isset($queueTask['queue_worker_id']) && $queueTask['queue_worker_id'] != null) {
                    $queueWorkerId = $queueTask['queue_worker_id'];
                }
            }
            $this->QueueLogs->addLog($queueTaskId, $queueWorkerId, __('Result of command: "') . $command . '"', intval($exitCode), serialize($output));
        }

        $acceptedReturnCodes = $additional_data['accepted'];
        $success = !$acceptedReturnCodes || in_array($exitCode, $acceptedReturnCodes, true);
        if (!$success) {
            $this->err('Error (code ' . $exitCode . ')', static::VERBOSE);
        } else {
            $this->success('Success (code ' . $exitCode . ')', static::VERBOSE);
        }

        if (!$success) {
            throw new QueueException('Failed with error code ' . $exitCode . ': `' . $command . '`');
        }
    }

}
