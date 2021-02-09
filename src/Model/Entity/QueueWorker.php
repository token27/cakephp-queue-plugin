<?php

declare(strict_types=1);

namespace Queue\Model\Entity;

use Cake\ORM\Entity;

/**
 * QueueWorker Entity
 *
 * @property string $id
 * @property string|null $server
 * @property string $name
 * @property string $pid
 * @property \Cake\I18n\FrozenTime|null $terminated_at
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $status
 * @property int $queue_job_count
 * @property int $queue_log_count
 *
 * @property \Queue\Model\Entity\QueueLog[] $queue_logs
 * @property \Queue\Model\Entity\QueueJob[] $queue_tasks
 */
class QueueWorker extends Entity {

    /**
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

}
