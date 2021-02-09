<?php

namespace Queue\View\Helper;

use Cake\Datasource\ModelAwareTrait;
use Cake\View\Helper;
#
use Queue\Model\Entity\QueueJob;
use Queue\Config;
use Queue\TaskFinder;

/**
 * @property \Queue\Model\Table\QueueJobsTable $QueueJobs
 */
class QueueHelper extends Helper {

    use ModelAwareTrait;

    /**
     * @var array|null
     */
    protected $taskConfig;

    /**
     * @param \Queue\Model\Entity\QueueJob $queueJob
     *
     * @return bool
     */
    public function hasFailed(QueueJob $queueJob): bool {
        if ($queueJob->completed_at || !$queueJob->executed_at || !$queueJob->failed) {
            return false;
        }

        // Restarted
        if (!$queueJob->failure_message) {
            return false;
        }

        // Requeue
        $taskConfig = $this->taskConfig($queueJob->name);
        if ($taskConfig && $queueJob->failed <= $taskConfig['retries']) {
            return false;
        }

        return true;
    }

    /**
     * @param \Queue\Model\Entity\QueueJob $queueJob
     *
     * @return string|null
     */
    public function fails(QueueJob $queueJob): ?string {
        if (!$queueJob->failed) {
            return '0x';
        }

        $taskConfig = $this->taskConfig($queueJob->name);
        if ($taskConfig) {
            $allowedFails = $taskConfig['retries'] + 1;

            return $queueJob->failed . '/' . $allowedFails;
        }

        return $queueJob->failed . 'x';
    }

    /**
     * Returns failure status (message) if applicable.
     *
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @return string|null
     */
    public function failureStatus(QueueJob $queueJob): ?string {
        if ($queueJob->completed_at || !$queueJob->executed_at || !$queueJob->failed) {
            return null;
        }

        if (!$queueJob->failure_message) {
            return __d('queue', 'Restarted');
        }

        $taskConfig = $this->taskConfig($queueJob->name);
        if ($taskConfig && $queueJob->failed <= $taskConfig['retries']) {
            return __d('queue', 'Requeue');
        }

        return __d('queue', 'Aborted');
    }

    /**
     * @param string $jobType
     *
     * @return array
     */
    protected function taskConfig(string $jobType): array {
        if (!$this->taskConfig) {
            $tasks = (new TaskFinder())->getAllShellTasks();
            $this->taskConfig = Config::taskConfig($tasks);
        }

        $name = 'Queue' . $jobType;

        return $this->taskConfig[$name] ?? [];
    }

}
