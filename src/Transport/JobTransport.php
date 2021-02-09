<?php

declare(strict_types=1);

namespace Queue\Transport;

# CAKEPHP

use Cake\Core\Configure;
use Cake\I18n\I18n;

# PLUGIN
use Queue\Transport\Transport;
use Queue\Transport\TransportInterface;
use Queue\Transport\JobInterface;
use Queue\TaskJob;
use Queue\Shell\Task\QueueJobTask;

class JobTransport extends Transport implements TransportInterface {

    /**
     * Run function
     *
     * @param \Queue\Transport\JobInterface $queueTask QueueJob object
     * @param string|array|null $content String with message or array with messages
     * @return \Queue\Transport\JobInterface
     */
    public static function runJob(JobInterface $queueTask, $task): JobInterface {
        $beforeSendCallback = $queueTask->getBeforeSendCallback();

        self::_performCallback($beforeSendCallback, $queueTask);

        if ($queueTask->getLocale() !== null) {
            I18n::setLocale($queueTask->getLocale());
        } else {
            I18n::setLocale(Configure::read('Queue.defaultLocale'));
        }
        var_dump($queueTask);
//        var_dump($queueTask->queueJobTask());
        var_dump($content);
        /**
         * @TODO          
         */
//        $queueTask->queueTask();

        var_dump($queueTask);
        var_dump($task);
        echo "Running the job" . PHP_EOL;
        sleep(mt_rand(7, 10));
//        $task->run();

        $afterSendCallback = $queueTask->getAfterSendCallback();
        self::_performCallback($afterSendCallback);

        return $queueTask;
    }

    /**
     * Process the job coming from the queue
     *
     * @param \Notifications\Notification\QueueInterface $queue Notification object
     * @param string|array|null $content String with message or array with messages
     * @return \Notifications\Notification\QueueInterface
     */
    public static function pushJob(JobInterface $queue, $content = null): bool {
        $workerJob = new TaskJob();

        $beforeSendCallback = $queue->getBeforeSendCallback();
        self::_performCallback($beforeSendCallback, $queue);

        if ($queue->getLocale() !== null) {
            I18n::setLocale($queue->getLocale());
        } else {
            I18n::setLocale(Configure::read('Queue.defaultLocale'));
        }

        /**
         * @TODO          
         * Save in the database
         * and return true or false 
         * if is saved 
         */
        //$queue->schedule($content);
        echo "saving job in the database" . PHP_EOL;

        $afterSendCallback = $queue->getAfterSendCallback();
        self::_performCallback($afterSendCallback);

        return true;
    }

}
