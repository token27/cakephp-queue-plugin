<?php

namespace Queue\Shell\Task;

use Queue\Shell\Task\QueueTask;
use Queue\Shell\Task\AddInterface;

/**
 * A Simple QueueTask example.
 */
class QueueExampleTask extends QueueTask implements AddInterface {

    /**
     * Timeout for run, after which the Task is reassigned to a new worker.
     *
     * @var int
     */
    public $timeout = 10;

    /**
     * Example add functionality.
     * Will create one example task in the queue, which later will be executed using run();
     *
     * To invoke from CLI execute:
     * - bin/cake queue add Example
     *
     * @return void
     */
    public function add() {
        $this->out('CakePHP Queue Example task.');
        $this->hr();
        $this->out('This is a very simple example of a QueueTask.');
        $this->out('I will now add an example Task into the Queue.');
        $this->out('This task will only produce some console output on the worker that it runs on.');
        $this->out(' ');
        $this->out('To run a Worker use:');
        $this->out('    bin/cake queue_worker run');
        $this->out(' ');
        $this->out('You can find the sourcecode of this task in: ');
        $this->out(__FILE__);
        $this->out(' ');

        $this->QueueTasks->addQueueTask('Example');
        $this->success('OK, queue task created, now run the worker');
    }

    /**
     * Example run function.
     * This function is executed, when a worker is executing a task.
     * The return parameter will determine, if the task will be marked completed, or be requeue.
     *
     * @param string $queueTaskId The id of the QueueTask entity
     * @param array $additional_data The array passed to QueueTasksTable::addQueueTask()
     * @return void
     */
    public function run(string $queueTaskId, array $additional_data): void {
        $this->hr();
        $this->out('CakePHP Queue Example task.');
        $this->hr();
        $this->success(' -> Success, the Example Task was run. <-');
    }

}
