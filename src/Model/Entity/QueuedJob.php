<?php

declare(strict_types=1);

namespace Token27\Queue\Model\Entity;

use Cake\ORM\Entity;

/**
 * QueueJob Entity
 *
 * @property string $id
 * @property string|null $user_id
 * @property string|null $queue_worker_id
 * @property string $type
 * @property string $name
 * @property float|null $progress
 * @property string|null $additional_data
 * @property \Cake\I18n\FrozenTime|null $start_at
 * @property \Cake\I18n\FrozenTime|null $executed_at
 * @property \Cake\I18n\FrozenTime|null $completed_at
 * @property int $failed
 * @property string|null $failure_message
 * @property int $priority
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $status
 * @property int $queue_log_count
 *
 * @property \Queue\Model\Entity\User $user
 * @property \Queue\Model\Entity\QueueWorker $queue_worker
 * @property \Queue\Model\Entity\QueueLog[] $queue_logs
 */
class QueueJob extends Entity {

    /**
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

}
