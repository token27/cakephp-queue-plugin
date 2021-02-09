<?php

namespace Token27\Queue\Shell\Task;

use Token27\Queue\Shell\Task\QueueTask;
use Token27\Queue\Shell\Task\AddInterface;

/**
 * A Costs QueueTask example.
 */
class QueueCpuPercentageCostsExampleTask extends QueueTask implements AddInterface {

    /**
     * @var int
     */
    public $cpu_percentage_costs = 55;

    /**
     * To invoke from CLI execute:
     * - bin/cake queue add CpuPercentageCostsExample
     *
     * @return void
     */
    public function add() {
        $this->out('CakePHP Queue Cpu Percentage Costs Example task.');
        $this->hr();
        $this->out('I will now add an example Cpu Percentage Costs Task into the Queue.');
        $this->out('This task cannot run more than once per server (across all its workers).');
        $this->out('This task will only produce some console output on the worker that it runs on.');
        $this->out(' ');
        $this->out('To run a Worker use:');
        $this->out('    bin/cake queue_worker run');
        $this->out(' ');
        $this->out('You can find the sourcecode of this task in: ');
        $this->out(__FILE__);
        $this->out(' ');

        $this->QueueTasks->addQueueTask('CostsExample');
        $this->success('OK, queue task created, now run the worker');
    }

    /**
     * CpuPercentageCostsExample run function.
     * This function is executed, when a worker is executing a task.
     *
     * @param string $queueTaskId The id of the QueueTask entity
     * @param array $additional_data The array passed to QueueTasksTable::addQueueTask()
     * @return void
     */
    public function run(string $queueTaskId, array $additional_data): void {
        $this->hr();
        $this->out('CakePHP Queue CpuPercentageCostsExample task.');

        sleep(10);

        $this->hr();
        $this->success(' -> Success, the "Cpu Percentage Costs Example" Task was run. <-');
    }

}
