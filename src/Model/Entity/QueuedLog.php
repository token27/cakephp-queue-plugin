<?php

declare(strict_types=1);

namespace Token27\Queue\Model\Entity;

use Cake\ORM\Entity;

/**
 * QueueLog Entity
 *
 * @property string $id
 * @property string $queue_task_id
 * @property string $queue_worker_id
 * @property string|null $message
 * @property string $data_result
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $status
 *
 * @property \Queue\Model\Entity\QueueJob $queue_job
 * @property \Queue\Model\Entity\QueueWorker $queue_worker
 */
class QueueLog extends Entity {

    /**
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

}
