<?php

declare(strict_types=1);

namespace Queue\Transport;

use Queue\Transport\JobInterface;

interface TransportInterface {

    /**
     * Do Job method
     *
     * @param \Queue\Transport\JobInterface $job Queue Task object
     * @param string|array|null $content String with message or array with additional data
     * @return \Queue\Transport\JobInterface
     */
    public static function runJob(
            JobInterface $job,
            $content = null
    ): JobInterface;

    /**
     * Schedule Job method
     *
     * @param \Queue\Transport\JobInterface $job  Queue Task object
     * @param string|array|null $content String with message or array with additional data
     * @return \Queue\Transport\JobInterface
     */
    public static function pushJob(
            JobInterface $job,
            $content = null
    ): bool;
}
