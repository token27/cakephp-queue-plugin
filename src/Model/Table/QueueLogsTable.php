<?php

declare(strict_types=1);

namespace Queue\Model\Table;

# CAKEPHP

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

# PLUGIN
use Queue\Queue\Config;
use Queue\Model\ProcessEndingException;

/**
 * QueueLogs Model
 *
 * @property \Queue\Model\Table\QueueJobsTable&\Cake\ORM\Association\BelongsTo $QueueJobs
 * @property \Queue\Model\Table\QueueWorkersTable&\Cake\ORM\Association\BelongsTo $QueueWorkers
 *
 * @method \Queue\Model\Entity\QueueLog newEmptyEntity()
 * @method \Queue\Model\Entity\QueueLog newEntity(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueLog[] newEntities(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueLog get($primaryKey, $options = [])
 * @method \Queue\Model\Entity\QueueLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Queue\Model\Entity\QueueLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class QueueLogsTable extends Table {

    /**
     * set connection name
     *
     * @return string
     */
    public static function defaultConnectionName(): string {
        $connection = Config::defaultDatabaseConnection();
        if (!empty($connection)) {
            return $connection;
        }

        return parent::defaultConnectionName();
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('queue_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'QueueJobs' => ['queue_log_count'],
            'QueueWorkers' => ['queue_log_count'],
        ]);

        $this->belongsTo('QueueJobs', [
            'foreignKey' => 'queue_job_id',
            'joinType' => 'INNER',
            'className' => 'Queue.QueueJobs',
        ]);
        $this->belongsTo('QueueWorkers', [
            'foreignKey' => 'queue_worker_id',
            'joinType' => 'INNER',
            'className' => 'Queue.QueueWorkers',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator {
        $validator
                ->uuid('id')
                ->allowEmptyString('id', null, 'create');

        $validator
                ->scalar('message')
                ->maxLength('message', 150)
                ->allowEmptyString('message');

        $validator
                ->scalar('data_result')
                ->requirePresence('data_result', 'create')
                ->notEmptyString('data_result');

        $validator
                ->integer('status')
                ->notEmptyString('status');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['queue_job_id'], 'QueueJobs'), ['errorField' => 'queue_job_id']);
        $rules->add($rules->existsIn(['queue_worker_id'], 'QueueWorkers'), ['errorField' => 'queue_worker_id']);

        return $rules;
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @param \ArrayObject $data
     * @param \ArrayObject $options
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {
        if (isset($data['data_result'])) {
            if ($data['data_result'] !== '') {
                if (is_array($data['data_result'])) {
                    $data['data_result'] = serialize($data['data_result']);
                }
            } else {
                $data['data_result'] = null;
            }
        }
    }

    /**
     * 
     * @param string $queue_job_id
     * @param string $queue_worker_id
     * @param string $message
     * @param string $data_result
     * @return type
     */
    public function addLog(string $queue_job_id, string $queue_worker_id = "", string $message = "", int $exit_code = 0, string $data_result = "") {
        $queueLog = [
            'queue_job_id' => $queue_job_id,
            'queue_worker_id' => $queue_worker_id !== "" ? $queue_worker_id : null,
            'message' => $message !== "" ? $message : null,
            'data_result' => $data_result !== "" ? $data_result : null,
        ];

        $queueLog = $this->newEntity($queueLog);

        return $this->saveOrFail($queueLog);
    }

}
