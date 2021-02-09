<?php

namespace Queue\View\Helper;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ModelAwareTrait;
use Cake\I18n\FrozenTime;
use Cake\I18n\Number;
use Cake\View\Helper;
#
use Token27\Queue\Model\Entity\QueueJob;

/**
 * @property \Tools\View\Helper\ProgressHelper $Progress
 * @property \Queue\Model\Table\QueueJobsTable $QueueJobs
 */
class QueueProgressHelper extends Helper {

    use ModelAwareTrait;

    /**
     * @var array
     */
    protected $helpers = [
        'Tools.Progress',
    ];

    /**
     * @var array|null
     */
    protected $statistics;

    /**
     * Returns percentage as formatted value.
     *
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @return string|null
     */
    public function progress(QueueJob $queueJob) {
        if ($queueJob->completed_at) {
            return null;
        }

        if ($queueJob->progress === null && $queueJob->executed_at) {
            $queueJob->progress = $this->calculateJobProgress($queueJob->name, $queueJob->executed_at);
        }

        if ($queueJob->progress === null) {
            return null;
        }

        $progress = $this->Progress->roundPercentage($queueJob->progress);

        return Number::toPercentage($progress, 0, ['multiply' => true]);
    }

    /**
     * Returns percentage as visual progress bar.
     *
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @param int $length
     * @return string|null
     */
    public function progressBar(QueueJob $queueJob, $length) {
        if ($queueJob->completed_at) {
            return null;
        }

        if ($queueJob->progress === null && $queueJob->executed_at) {
            $queueJob->progress = $this->calculateJobProgress($queueJob->name, $queueJob->executed_at);
        }

        if ($queueJob->progress === null) {
            return null;
        }

        return $this->Progress->progressBar($queueJob->progress, $length);
    }

    /**
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @param string|null $fallbackHtml
     *
     * @return string|null
     */
    public function htmlProgressBar(QueueJob $queueJob, $fallbackHtml = null) {
        if ($queueJob->completed_at) {
            return null;
        }

        if ($queueJob->progress === null && $queueJob->executed_at) {
            $queueJob->progress = $this->calculateJobProgress($queueJob->name, $queueJob->executed_at);
        }

        if ($queueJob->progress === null) {
            return null;
        }

        $progress = $this->Progress->roundPercentage($queueJob->progress);
        $title = Number::toPercentage($progress, 0, ['multiply' => true]);

        return '<progress value="' . number_format($progress * 100, 0) . '" max="100" title="' . $title . '">' . $fallbackHtml . '</progress>';
    }

    /**
     * Returns percentage as visual progress bar.
     *
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @param int $length
     * @return string|null
     */
    public function timeoutProgressBar(QueueJob $queueJob, $length) {
        $progress = $this->calculateTimeoutProgress($queueJob);
        if ($progress === null) {
            return null;
        }

        return $this->Progress->progressBar($progress, $length);
    }

    /**
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @param string|null $fallbackHtml
     *
     * @return string|null
     */
    public function htmlTimeoutProgressBar(QueueJob $queueJob, $fallbackHtml = null) {
        $progress = $this->calculateTimeoutProgress($queueJob);
        if ($progress === null) {
            return null;
        }

        $progress = $this->Progress->roundPercentage($progress);
        $title = Number::toPercentage($progress, 0, ['multiply' => true]);

        return '<progress value="' . number_format($progress * 100, 0) . '" max="100" title="' . $title . '">' . $fallbackHtml . '</progress>';
    }

    /**
     * Calculates the timeout progress rate.
     *
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @return float|null
     */
    protected function calculateTimeoutProgress(QueueJob $queueJob) {
        if ($queueJob->completed_at || $queueJob->executed_at || !$queueJob->notbefore) {
            return null;
        }

        $created = $queueJob->created->getTimestamp();
        $planned = $queueJob->notbefore->getTimestamp();
        $now = (new FrozenTime())->getTimestamp();

        $progressed = $now - $created;
        $total = $planned - $created;

        if ($total <= 0) {
            return null;
        }

        if ($progressed < 0) {
            $progressed = 0;
        }

        $progress = min($progressed / $total, 1.0);

        return (float) $progress;
    }

    /**
     * @param string $jobType
     * @param \Cake\I18n\FrozenTime|\Cake\I18n\Time $executed_at
     * @return float|null
     */
    protected function calculateJobProgress($jobType, $executed_at) {
        $stats = $this->getJobStatistics($jobType);
        if (!$stats) {
            return null;
        }
        $sum = array_sum($stats);
        if ($sum <= 0) {
            return null;
        }
        $average = $sum / count($stats);

        $running = $executed_at->diffInSeconds();
        $progress = min($running / $average, 0.9999);

        return (float) $progress;
    }

    /**
     * @param string $jobType
     * @return array
     */
    protected function getJobStatistics($jobType) {
        $statistics = $this->readStatistics();
        if (!isset($statistics[$jobType])) {
            return [];
        }

        return $statistics[$jobType];
    }

    public const KEY = 'queue_queue-job-statistics';
    public const CONFIG = 'default';

    /**
     * @return array
     */
    protected function readStatistics() {
        if ($this->statistics !== null) {
            return $this->statistics;
        }

        $queueJobStatistics = false;
        if (!Configure::read('debug')) {
            $queueJobStatistics = Cache::read(static::KEY, static::CONFIG);
        }
        if ($queueJobStatistics === false) {
            $this->loadModel('Queue.QueueJobs');
            $queueJobStatistics = $this->QueueJobs->getStats()->disableHydration()->toArray();
            Cache::write(static::KEY, $queueJobStatistics, static::CONFIG);
        }

        $statistics = [];
        foreach ((array) $queueJobStatistics as $statistic) {
            $statistics[$statistic['name']][] = $statistic['runtime'];
        }

        $this->statistics = $statistics;

        return $this->statistics;
    }

}
