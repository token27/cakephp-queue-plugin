<?php

namespace Queue\Shell\Task;

/**
 * Any task needs to at least implement run().
 * The add() is mainly only for CLI adding purposes and optional.
 *
 * Either throw an exception with an error message, or use $this->abort('My message'); to fail a job.
 *
 */
interface QueueJobTaskInterface {

    /**
     * Main execution of the task.
     *
     * @param string $queueTaskId The id of the QueueJob entity
     * @param array $additional_data The array passed to QueueJobsTable::addQueueJob()
     * @return void
     */
    public function run($queueJobTask = null): void;
}
