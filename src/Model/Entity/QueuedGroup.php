<?php

declare(strict_types=1);

namespace Token27\Queue\Model\Entity;

use Cake\ORM\Entity;

/**
 * QueueGroup Entity
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $queue_job_count
 *
 * @property \Queue\Model\Entity\QueueJob[] $queue_jobs
 */
class QueueGroup extends Entity {

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

}
