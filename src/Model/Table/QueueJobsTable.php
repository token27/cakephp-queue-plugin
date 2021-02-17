<?php

declare(strict_types=1);

namespace Queue\Model\Table;

# CAKEPHP

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
#
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotImplementedException;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\Model\Table\EntityInterface;

# PLUGIN
use Queue\Utility\Config;
use Queue\Model\Entity\QueueJob;
//use Queue\Model\Table\Event;
#  OTHERS
use ArrayObject;
use InvalidArgumentException;

// PHP 7.1+ has this defined
if (!defined('SIGTERM')) {
    define('SIGTERM', 15);
}

/**
 * QueueJobs Model
 *
 * @property \Queue\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \Queue\Model\Table\QueueGroupsTable&\Cake\ORM\Association\BelongsTo $QueueGroups
 * @property \Queue\Model\Table\QueueWorkersTable&\Cake\ORM\Association\BelongsTo $QueueWorkers
 * @property \Queue\Model\Table\QueueLogsTable&\Cake\ORM\Association\HasMany $QueueLogs
 *
 * @method \Queue\Model\Entity\QueueJob newEmptyEntity()
 * @method \Queue\Model\Entity\QueueJob newEntity(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueJob[] newEntities(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueJob get($primaryKey, $options = [])
 * @method \Queue\Model\Entity\QueueJob findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Queue\Model\Entity\QueueJob patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueJob[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueJob|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueJob saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueJob[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueJob[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueJob[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueJob[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class QueueJobsTable extends Table {

    public const DRIVER_MYSQL = 'Mysql';
    public const DRIVER_POSTGRES = 'Postgres';
    public const DRIVER_SQLSERVER = 'Sqlserver';
    public const STATS_LIMIT = 100000;

    /**
     * @var array
     */
    public $rateHistory = [];

    /**
     *
     * @var type 
     */
    protected $worker;

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
     * @param \Cake\Event\EventInterface $event
     * @param \ArrayObject $data
     * @param \ArrayObject $options
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {

        if (isset($data['additional_data'])) {
            if ($data['additional_data'] !== '') {
                if (is_array($data['additional_data'])) {
                    $data['additional_data'] = serialize($data['additional_data']);
                }
            } else {
                $data['additional_data'] = null;
            }
        }
    }

    /**
     * 
     * @param ArrayObject $data
     * @param type $primary
     */
//    public function afterFind(ArrayObject $data, $primary = false) {
//        if (isset($data['additional_data']) && $data['additional_data'] !== '') {
//            $data['additional_data'] = unserialize($data['additional_data']);
//        }
//    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('queue_jobs');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'QueueWorkers' => [
                'queue_job_count'
            ],
            'QueueGroups' => [
                'queue_job_count',
//                'queue_job_completed' => [
//                    'finder' => 'completed', // Will be using findCompleted()
////                    'conditions' => [
////                        'QueueJobs.failed' => 0,
////                        'QueueJobs.status' => 4,
////                    ]
//                ]
            ],
        ]);

        if (Configure::read('Queue.isSearchEnabled') !== false && Plugin::isLoaded('Search')) {
            $this->addBehavior('Search.Search');
        }

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'className' => 'Queue.Users',
        ]);

        $this->belongsTo('QueueGroups', [
            'foreignKey' => 'queue_group_id',
            'className' => 'Queue.QueueGroups',
        ]);

        $this->belongsTo('QueueWorkers', [
            'foreignKey' => 'queue_worker_id',
            'className' => 'Queue.QueueWorkers',
        ]);

        $this->hasMany('QueueLogs', [
            'foreignKey' => 'queue_job_id',
            'className' => 'Queue.QueueLogs',
        ]);
    }

    /**
     * @return \Search\Manager
     */
    public function searchManager() {
        $searchManager = $this->behaviors()->Search->searchManager();
        $searchManager
                ->value('type')
                ->like('search', ['fields' => ['group', 'reference'], 'before' => true, 'after' => true])
                ->add('status', 'Search.Callback', [
                    'callback' => function (Query $query, array $args, $filter) {
                        $status = $args['status'];
                        if ($status == 2) {
                            $query->where(['completed_at IS' => null]);

                            return true;
                        }
                        if ($status == 3) {
                            $query->where(['completed_at IS NOT' => null]);
                            return true;
                        }

                        throw new NotImplementedException('Invalid status type');
                    },
        ]);

        return $searchManager;
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
                ->scalar('name')
                ->maxLength('name', 150)
                ->requirePresence('name', 'create')
                ->notEmptyString('name');

        $validator
                ->numeric('progress')
                ->allowEmptyString('progress');

//        $validator
//                ->scalar('additional_data')
//                ->allowEmptyString('additional_data');

        $validator
                ->dateTime('start_at')
                ->allowEmptyDateTime('start_at');

        $validator
                ->dateTime('executed_at')
                ->allowEmptyDateTime('executed_at');

        $validator
                ->dateTime('completed_at')
                ->allowEmptyDateTime('completed_at');

        $validator
                ->integer('failed')
                ->notEmptyString('failed');

        $validator
                ->scalar('failure_message')
                ->allowEmptyString('failure_message');

        $validator
                ->integer('priority')
                ->notEmptyString('priority');

        $validator
                ->integer('status')
                ->notEmptyString('status');

        $validator
                ->integer('queue_log_count')
                ->notEmptyString('queue_log_count');

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
//        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['queue_worker_id'], 'QueueWorkers'), ['errorField' => 'queue_worker_id']);
        $rules->add($rules->existsIn(['queue_group_id'], 'QueueGroups'), ['errorField' => 'queue_group_id']);

        return $rules;
    }

    /**
     * Adds a new job to the queue.
     *
     * Config
     * - priority: 1-10, defaults to 5
     * - start_at: Optional date which must not be preceded
     * - group: Used to group similar QueueJobs
     * - reference: An optional reference string
     *
     * @param string $jobName Job name
     * @param array|null $additional_data Array of additional data
     * @param array $queue_job_config Config to save along with the job
     * @return \Queue\Model\Entity\QueueJob Saved job entity
     */
    public function addQueueJob($jobName, ?array $additional_data = null, array $queue_job_config = []) {

//        $additional_data = is_array($additional_data) ? serialize($additional_data) : null;

        $queue_job_config += [
            'user_id' => !empty($queue_job_config['user_id']) ? $queue_job_config['user_id'] : null,
            'queue_group_id' => !empty($queue_job_config['queue_group_id']) ? $queue_job_config['queue_group_id'] : null,
            'queue_worker_id' => null,
            'reference' => !empty($queue_job_config['reference']) ? $queue_job_config['reference'] : null,
            'start_at' => !empty($queue_job_config['start_at']) ? $this->getDateTime($queue_job_config['start_at']) : null,
        ];


        $queueJob = [
            'name' => $jobName,
            'additional_data' => $additional_data
                ] + $queue_job_config;

        $queueJob = $this->newEntity($queueJob);

        return $this->saveOrFail($queueJob);
    }

    /**
     * Look for a new job that can be processed with the current abilities and
     * from the specified group (or any if null).
     *
     * @param array $capabilities Available QueueWorkerJobs.
     * @param string[] $queue_groups_ids Request a queue jobs from these queue groups (or exclude certain groups), or any otherwise.
     * @param string[] $queue_jobs_names Request a queue job from these names (or exclude certain names), or any otherwise.
     * @param \Queue\Model\Entity\QueueWorker|null $queueWorker 
     * @return \Queue\Model\Entity\QueueJob|null
     */
    public function requestQueueJob(array $capabilities, array $queue_groups_ids = [], array $queue_jobs_names = [], array $queueWorker = []) {
        /**
         * @var \Cake\I18n\FrozenTime|\Cake\I18n\Time $now
         */
        $now = $this->getDateTime();
        $nowStr = $now->toDateTimeString();
        $driverName = $this->_getDriverName();
//        $worker = $this->getWorker();
        $query = $this->find();

        $age = $query->newExpr()->add('IFNULL(TIMESTAMPDIFF(SECOND, "' . $nowStr . '", start_at), 0)');
        switch ($driverName) {
            case static::DRIVER_SQLSERVER:
                $age = $query->newExpr()->add('ISNULL(DATEDIFF(SECOND, GETDATE(), start_at), 0)');

                break;
            case static::DRIVER_POSTGRES:
                $age = $query->newExpr()
                        ->add('COALESCE(EXTRACT(EPOCH FROM start_at) - (EXTRACT(EPOCH FROM now())), 0)');

                break;
        }
        $options = [
            'conditions' => [
                'completed_at IS' => null,
                'OR' => [],
            ],
            'fields' => [
                'age' => $age,
            ],
            'order' => [
                'priority' => 'ASC',
                'age' => 'ASC',
                'id' => 'ASC',
            ],
        ];

        /**
         * @note  Queue Job - Cpu Percentage Cost
         */
        $cpuPercentageCostConstraints = [];
        foreach ($capabilities as $capability) {
            if (!$capability['cpu_percentage_costs']) {
                continue;
            }

            $cpuPercentageCostConstraints[$capability['name']] = $capability['cpu_percentage_costs'];
        }

        /**
         * @note Queue Job - Unique
         */
        $uniqueConstraints = [];
        foreach ($capabilities as $capability) {
            if (!$capability['unique']) {
                continue;
            }

            $uniqueConstraints[$capability['name']] = $capability['name'];
        }

        /**
         * @note Get the running queue jobs
         * @var \Queue\Model\Entity\QueueJob[] $runningQueueJobs
         */
        $runningQueueJobs = [];
        if ($cpuPercentageCostConstraints || $uniqueConstraints) {

            $constraintJobs = array_keys($cpuPercentageCostConstraints + $uniqueConstraints);

            $timeout_max = null;
            $timeoutWorkerAt = $now->copy();

            $where = [
                'QueueJobs.name IN' => $constraintJobs,
                'QueueJobs.name IS NOT' => null,
                'QueueJobs.queue_worker_id IS NOT' => null,
//                'QueueWorkers.id !=' => $queueWorker['id'],
//                'QueueWorkers.modified >' => [worker_timeout_max],
            ];

            if (!empty($queueWorker)) {
                if (isset($queueWorker['id'])) {
                    $where['QueueJobs.queue_worker_id !='] = $queueWorker['id'];

                    if (isset($queueWorker['timeout'])) {
                        $timeout_max = intval($queueWorker['timeout']);
                    }

                    if (!$timeout_max) {
                        $timeout_max = intval(Config::defaultWorkerTimeout());
                    }

                    /**
                     * @note The queue job not in timeout.
                     */
                    if (intval($timeout_max) > 0) {
                        $timeoutWorkerAt->subSeconds(intval($timeout_max));
                        $where[] = 'QueueJobs.queue_worker_id = QueueWorkers.id';
                        $where['QueueWorkers.modified >'] = $timeoutWorkerAt->subSeconds(intval($timeout_max));
//                $timeout_max_date = $timeoutWorkerAt->format('Y-m-d H:i:s');
//                $where['QueueWorkers.modified >'] = $timeout_max_date;
                    }
                }
            }

            $runningQueueJobs = $this->find('queue')
                    ->contain(['QueueWorkers'])
                    ->where($where)
                    ->all()
                    ->toArray();
        }


        /**
         * @note $cpu_percentage_costs 0 - 100. 
         * @var int $cpu_percentage_costs 
         */
        $cpu_percentage_costs = 0;
        $server_name = $this->QueueWorkers->buildServerString();
        foreach ($runningQueueJobs as $runningQueueJob) {
            if (isset($uniqueConstraints[$runningQueueJob->name])) {
                $queue_jobs_names[] = '-' . $runningQueueJob->name;

                continue;
            }

            if ($runningQueueJob->queue_worker->server_name === $server_name && isset($cpuPercentageCostConstraints[$runningQueueJob->name])) {
                $cpu_percentage_costs += $cpuPercentageCostConstraints[$runningQueueJob->name];
            }
        }

        if ($cpu_percentage_costs) {
            $cpu_percentage_left = 100 - $cpu_percentage_costs;
            foreach ($capabilities as $capability) {
                if (!$capability['cpu_percentage_costs'] || $capability['cpu_percentage_costs'] < $cpu_percentage_left) {
                    continue;
                }

                $queue_jobs_names[] = '-' . $capability['name'];
            }
        }

        /**
         * @TODO Fix groups filter
         */
        if ($queue_groups_ids) {
//            $options['conditions'] = $this->addFilter($options['conditions'], 'group', $groups);
        }
        if ($queue_jobs_names) {
//            $options['conditions'] = $this->addFilter($options['conditions'], 'name', $names);
        }

        /**
         * @note Generate the queue job with specific conditions.
         */
        foreach ($capabilities as $capability) {

            [$plugin, $jobName] = pluginSplit($capability['name']);
            $timeoutAt = $now->copy();
            $tmp = [
                'name' => $jobName,
                'AND' => [
                    [
                        'OR' => [
                            'start_at <' => $nowStr,
                            'start_at IS' => null,
                        ],
                    ],
                    [
                        'OR' => [
                            'executed_at <' => $timeoutAt->subSeconds($capability['timeout']),
                            'executed_at IS' => null,
                        ],
                    ],
                ],
                'failed <' => ($capability['retries'] + 1),
            ];
            if (array_key_exists('rate', $capability) && $tmp['name'] && array_key_exists($tmp['name'], $this->rateHistory)) {
                switch ($driverName) {
                    case static::DRIVER_POSTGRES:
                        $tmp['EXTRACT(EPOCH FROM NOW()) >='] = $this->rateHistory[$tmp['name']] + $capability['rate'];

                        break;
                    case static::DRIVER_MYSQL:
                        $tmp['UNIX_TIMESTAMP() >='] = $this->rateHistory[$tmp['name']] + $capability['rate'];

                        break;
                    case static::DRIVER_SQLSERVER:
                        $tmp["DATEDIFF(s, '1970-01-01 00:00:00', GETDATE()) >="] = $this->rateHistory[$tmp['name']] + $capability['rate'];

                        break;
                }
            }
            $options['conditions']['OR'][] = $tmp;
        }

        /**
         * @var \Queue\Model\Entity\QueueWorker|null $queueWorker 
         */
        $queue_worker_database = null;
        if (!empty($queueWorker)) {
            $queue_worker_database = $queueWorker;
        }
        /**
         * @var \Cake\I18n\FrozenTime|\Cake\I18n\Time $now
         * @var \Queue\Model\Entity\QueueJob|null $queueJob 
         * @var \Queue\Model\Entity\QueueWorker|null $queue_worker_database 
         */
        $queueJob = $this->getConnection()->transactional(function () use ($query, $options, $now, $queue_worker_database) {
            /**
             * @var \Queue\Model\Entity\QueueJob|null $queueJob 
             */
            $queueJob = $query->find('all', $options)
                    ->enableAutoFields(true)
                    ->epilog('FOR UPDATE')
                    ->first();

            if (!$queueJob) {
                return null;
            }
            $data_queue_job = [
                'executed_at' => $now,
                'progress' => null,
                'failure_message' => null,
            ];
            if ($queue_worker_database) {
                if (isset($queue_worker_database->id)) {
                    $data_queue_job['queue_worker_id'] = $queue_worker_database->id;
                }
            }

            $queueJob = $this->patchEntity($queueJob, $data_queue_job);

            return $this->saveOrFail($queueJob);
        });

        if (!$queueJob) {
            return null;
        }

        $this->rateHistory[$queueJob->name] = $now->toUnixString();

//        if ($queueJob->additional_data !== null && $queueJob->additional_data !== '' && !is_array($queueJob->additional_data)) {
//            $queueJob->additional_data = unserialize($queueJob->additional_data);
//        }
        return $queueJob;
    }

    /**
     * @param int $id ID of job
     * @param float $progress Value from 0 to 1
     * @param string|null $status
     * @return bool Success
     */
    public function updateProgress($id, $progress, $status = null) {
        if (!$id) {
            return false;
        }

        $values = [
            'progress' => round($progress, 2),
        ];
        if ($status !== null) {
            $values['status'] = intval($status);
        }

        return (bool) $this->updateAll($values, ['id' => $id]);
    }

    /**
     * Mark a job as in Progress.
     *
     * @param \Queue\Model\Entity\QueueJob $job Job
     * @return bool Success
     */
    public function markJobInProgress(QueueJob $job, string $queue_worker_id) {
        $fields = [
            'queue_worker_id' => $queue_worker_id,
            'modified' => $this->getDateTime(),
            'status' => 2,
        ];
        $job = $this->patchEntity($job, $fields);

        return (bool) $this->save($job);
    }

    /**
     * Mark a job as Completed, removing it from the queue.
     *
     * @param \Queue\Model\Entity\QueueJob $job Job
     * @return bool Success
     */
    public function markJobDone(QueueJob $job) {
        $fields = [
            'progress' => 100,
            'completed_at' => $this->getDateTime(),
            'status' => 4,
        ];
        $job = $this->patchEntity($job, $fields);

        return (bool) $this->save($job);
    }

    /**
     * Mark a job as Failed, incrementing the failed-counter and Requeueing it.
     *
     * @param \Queue\Model\Entity\QueueJob $job Job
     * @param string|null $failureMessage Optional message to append to the failure_message field.
     * @return bool Success
     */
    public function markJobFailed(QueueJob $job, $failureMessage = null) {
        $fields = [
            'failed' => $job->failed + 1,
            'failure_message' => $failureMessage,
            'status' => 5,
        ];
        $job = $this->patchEntity($job, $fields);

        return (bool) $this->save($job);
    }

    /**
     * Returns a DateTime object from different input.
     *
     * Without argument this will be "now".
     *
     * @param int|string|\Cake\I18n\FrozenTime|\Cake\I18n\Time|null $startAt
     *
     * @return \Cake\I18n\FrozenTime|\Cake\I18n\Time
     */
    protected function getDateTime($startAt = null) {
        if (is_object($startAt)) {
            return $startAt;
        }

        return new FrozenTime($startAt);
    }

    /**
     * @param string $reference
     * @param string|null $jobName
     * @param string|null $groupId
     *
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function isQueue($reference, $jobName = null, $groupId = null) {
        if (!$reference) {
            throw new InvalidArgumentException('A reference is needed');
        }

        $conditions = [
            'reference' => $reference,
//            'completed_at IS' => null,
            'status IN' => 1,
        ];
        if ($jobName) {
            $conditions['name'] = $jobName;
        }
        if ($groupId) {
            $conditions['queue_group_id'] = $groupId;
        }

        return (bool) $this->find()->where($conditions)->select(['id'])->first();
    }

    /**
     * Return some statistics about unfinished jobs still in the Database.
     *
     * @return \Cake\ORM\Query
     */
    public function getPending() {
        $findOptions = [
            'fields' => [
                'id',
                'name',
//                'group',
                'reference',
                'start_at',
                'executed_at',
                'completed_at',
                'progress',
                'failed',
                'failure_message',
                'priority',
                'created',
                'status',
            ],
            'conditions' => [
                'completed_at IS' => null,
            ],
        ];

        return $this->find('all', $findOptions);
    }

    /**
     * Returns the number of items in the queue.
     * Either returns the number of ALL pending jobs, or the number of pending jobs of the passed type.
     *
     * @param string|null $jobName Job type to Count
     * @return int
     */
    public function getLength(array $options = []) {
        $findOptions = [
            'conditions' => [
                'completed_at IS' => null,
            ],
        ];
        if (!empty($options)) {
            foreach ($options as $optionKey => $optionValue) {
                switch ($optionKey) {
                    case "name":
                    case "user_id":
                    case "queue_group_id":
                    case "queue_worker_id":
                    case "reference":
                        if ($optionValue !== null && $optionValue !== "") {
                            $findOptions['conditions'][$optionKey] = $optionValue;
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return $this->find('all', $findOptions)->count();
    }

    /**
     * Return a list of all job name in the Queue.
     *
     * @return \Cake\ORM\Query
     */
    public function getNames($status = null) {
        $findOptions = [
            'fields' => [
                'name',
            ],
            'group' => [
                'name',
            ],
            'keyField' => 'name',
            'valueField' => 'name',
        ];
        if ($status !== null) {
            $findOptions['conditions']['status'] = intval($status);
        }
        return $this->find('list', $findOptions);
    }

    /**
     * Return a list of all job groups in the Queue.
     *
     * @return \Cake\ORM\Query
     */
    public function getRefrecences(array $options = []) {
        $findOptions = [
            'fields' => [
                'reference',
            ],
            'group' => [
                'reference',
            ],
            'keyField' => 'reference',
            'valueField' => 'reference',
        ];
        if (!empty($options)) {
            foreach ($options as $optionKey => $optionValue) {
                switch ($optionKey) {
                    case "status":
                        if ($optionValue !== null && $optionValue !== "") {
                            $findOptions['conditions'][$optionKey] = intval($optionValue);
                        }
                        break;
                    case "name":
                    case "user_id":
                    case "queue_group_id":
                    case "queue_worker_id":
                        if ($optionValue !== null && $optionValue !== "") {
                            $findOptions['conditions'][$optionKey] = $optionValue;
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $this->find('list', $findOptions);
    }

    /**
     * Return some statistics about finished jobs still in the Database.
     * TO-DO: rewrite as virtual field
     *
     * @return \Cake\ORM\Query
     */
    public function getStats() {
        $driverName = $this->_getDriverName();
        $options = [
            'fields' => function (Query $query) use ($driverName) {
                $alltime = $query->func()->avg('UNIX_TIMESTAMP(completed_at) - UNIX_TIMESTAMP(created)');
                $runtime = $query->func()->avg('UNIX_TIMESTAMP(completed_at) - UNIX_TIMESTAMP(executed_at)');
                $fetchdelay = $query->func()->avg('UNIX_TIMESTAMP(executed_at) - IF(start_at is NULL, UNIX_TIMESTAMP(created), UNIX_TIMESTAMP(start_at))');
                switch ($driverName) {
                    case static::DRIVER_SQLSERVER:
                        $alltime = $query->func()->avg("DATEDIFF(s, '1970-01-01 00:00:00', completed_at) - DATEDIFF(s, '1970-01-01 00:00:00', created)");
                        $runtime = $query->func()->avg("DATEDIFF(s, '1970-01-01 00:00:00', completed_at) - DATEDIFF(s, '1970-01-01 00:00:00', executed_at)");
                        $fetchdelay = $query->func()->avg("DATEDIFF(s, '1970-01-01 00:00:00', executed_at) - (CASE WHEN start_at IS NULL THEN DATEDIFF(s, '1970-01-01 00:00:00', created) ELSE DATEDIFF(s, '1970-01-01 00:00:00', start_at) END)");

                        break;
                    case static::DRIVER_POSTGRES:
                        $alltime = $query->func()->avg('EXTRACT(EPOCH FROM completed_at) - EXTRACT(EPOCH FROM created)');
                        $runtime = $query->func()->avg('EXTRACT(EPOCH FROM completed_at) - EXTRACT(EPOCH FROM executed_at)');
                        $fetchdelay = $query->func()->avg('EXTRACT(EPOCH FROM executed_at) - CASE WHEN start_at IS NULL then EXTRACT(EPOCH FROM created) ELSE EXTRACT(EPOCH FROM start_at) END');

                        break;
                }

                return [
                    'name',
                    'num' => $query->func()->count('*'),
                    'alltime' => $alltime,
                    'runtime' => $runtime,
                    'fetchdelay' => $fetchdelay,
                ];
            },
            'conditions' => [
                'completed_at IS NOT' => null,
//                'status IN' => [3, 5],
            ],
            'group' => [
                'name',
            ],
        ];

        return $this->find('all', $options);
    }

    /**
     * Returns [
     *   'JobType' => [
     *      'YYYY-MM-DD' => INT,
     *      ...
     *   ]
     * ]
     *
     * @param string|null $jobName
     * @return array
     */
    public function getFullStats($jobName = null) {
        $driverName = $this->_getDriverName();
        $fields = function (Query $query) use ($driverName) {
            $runtime = $query->newExpr('UNIX_TIMESTAMP(completed_at) - UNIX_TIMESTAMP(executed_at)');
            switch ($driverName) {
                case static::DRIVER_SQLSERVER:
                    $runtime = $query->newExpr("DATEDIFF(s, '1970-01-01 00:00:00', completed_at) - DATEDIFF(s, '1970-01-01 00:00:00', executed_at)");

                    break;
                case static::DRIVER_POSTGRES:
                    $runtime = $query->newExpr('EXTRACT(EPOCH FROM completed_at) - EXTRACT(EPOCH FROM executed_at)');

                    break;
            }

            return [
                'name',
                'created',
                'duration' => $runtime,
            ];
        };

        $conditions = ['completed_at IS NOT' => null];
        if ($jobName) {
            $conditions['name'] = $jobName;
        }

        $jobs = $this->find()
                ->select($fields)
                ->where($conditions)
                ->enableHydration(false)
                ->orderDesc('id')
                ->limit(static::STATS_LIMIT)
                ->all()
                ->toArray();

        $result = [];

        $days = [];

        foreach ($jobs as $job) {
            /** @var \DateTime $created */
            $created = $job['created'];
            $day = $created->format('Y-m-d');
            if (!isset($days[$day])) {
                $days[$day] = $day;
            }

            $result[$job['name']][$day][] = $job['duration'];
        }

        foreach ($result as $jobName => $jobs) {
            /**
             * @var string $day
             * @var array $durations
             */
            foreach ($jobs as $day => $durations) {
                $average = array_sum($durations) / count($durations);
                $result[$jobName][$day] = (int) $average;
            }

            foreach ($days as $day) {
                if (isset($result[$jobName][$day])) {
                    continue;
                }

                $result[$jobName][$day] = 0;
            }

            ksort($result[$jobName]);
        }

        return $result;
    }

    /**
     * get the name of the driver
     *
     * @return string
     */
    protected function _getDriverName() {
        $className = explode('\\', $this->getConnection()->config()['driver']);
        $name = end($className) ?: '';

        return $name;
    }

    /**
     * Cleanup/Delete Completed Jobs.
     *
     * @return void
     */
    public function cleanOldJobs() {
        if (!Configure::read('Queue.cleanup_timeout')) {
            return;
        }

        $this->deleteAll([
            'completed_at <' => time() - (int) Configure::read('Queue.cleanup_timeout'),
        ]);
    }

    /**
     * @return int
     */
    public function cleanTimeouts(int $timeout = 0, string $pid = "") {
        if ($timeout == 0) {
            $timeout = Config::defaultWorkerTimeout();
        }
        $thresholdTime = (new FrozenTime())->subSeconds($timeout);
        $conditions = [
            'executed_at <' => $thresholdTime,
            'completed_at IS' => null
        ];
        if ($pid !== "") {
            $conditions['pid'] = $pid;
        }
        return $this->deleteAll($conditions);
    }

    /**
     * @param \Queue\Model\Entity\QueueJob $queueJob
     * @param array $jobConfiguration
     * @return string
     */
    public function getFailedStatus($queueJob, array $jobConfiguration) {
        $failureMessageRequeue = 'requeue';

        $queueJobName = 'Queue' . $queueJob->name;
        if (empty($jobConfiguration[$queueJobName])) {
            return $failureMessageRequeue;
        }
        $retries = $jobConfiguration[$queueJobName]['retries'];
        if ($queueJob->failed <= $retries) {
            return $failureMessageRequeue;
        }

        return 'aborted';
    }

    /**
     * Custom find method, as in `find('queue', ...)`.
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to find with
     * @return \Cake\ORM\Query The query builder
     */
    public function findQueue(Query $query, array $options) {
        return $query->where(['completed_at IS' => null]);
    }

    /**
     * Custom find method, as in `find('completed', ...)`.
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to find with
     * @return \Cake\ORM\Query The query builder
     */
    public function findCompleted(Query $query, array $options) {
        return $query->where([
                    'completed_at IS NOT' => null
        ]);
//        return $query->where([
//                    'failed' => 0,
//                    'status' => 4,
//        ]);
    }

    /**
     * 
     * @param string $id
     * @return type
     * 
     */
    public function getQueueJob(string $id) {

        $conditions = [
            'id' => $id,
        ];

        return $this->find()
                        ->where($conditions)
                        ->orderDesc('modified')
                        ->enableHydration(false)
                        ->first();
    }

    /**
     * @param array $conditions
     * @param string $key
     * @param string[] $values
     * @return array
     */
    protected function addFilter(array $conditions, $key, array $values) {
        $include = [];
        $exclude = [];
        foreach ($values as $value) {
            if (substr($value, 0, 1) === '-') {
                $exclude[] = substr($value, 1);
            } else {
                $include[] = $value;
            }
        }

        if ($include) {
            $conditions[$key . ' IN'] = array_unique($include);
        }
        if ($exclude) {
            $conditions[$key . ' NOT IN'] = array_unique($exclude);
        }

        return $conditions;
    }

    /**
     * 
     * 
     * 
     * 
     * 
     * 
     */

    /**
     * Resets all failed and not yet completed jobs.
     *
     * @param int|null $id
     * @param bool $full Also currently running jobs.
     *
     * @return int Success
     */
    public function reset($id = null, $full = false) {
        $fields = [
            'completed_at' => null,
            'executed_at' => null,
            'progress' => null,
            'failed' => 0,
            'queue_worker_id' => null,
            'failure_message' => null,
        ];
        $conditions = [
            'completed_at IS' => null,
        ];
        if ($id) {
            $conditions['id'] = $id;
        }
        if (!$full) {
            $conditions['failed >'] = 0;
        }

        return $this->updateAll($fields, $conditions);
    }

    /**
     * @param string $jobName
     * @param string|null $reference
     *
     * @return int
     */
    public function rerun($jobName, $reference = null, $queue_group_id = null) {
        $fields = [
            'completed_at' => null,
            'executed_at' => null,
            'progress' => null,
            'failed' => 0,
            'queue_worker_id' => null,
            'failure_message' => null,
        ];
        $conditions = [
            'completed_at IS NOT' => null,
            'name' => $jobName,
        ];
        if ($reference) {
            $conditions['reference'] = $reference;
        }
        if ($queue_group_id) {
            $conditions['queue_group_id'] = $queue_group_id;
        }
        return $this->updateAll($fields, $conditions);
    }

    /**
     * Custom find method, as in `find('progress', ...)`.
     *
     * @deprecated Unused right now, needs fixing.
     *
     * @param string $state Current state
     * @param array $query Parameters
     * @param array $results Results
     * @return array Query/Results based on state
     */
    protected function _findProgress($state, $query = [], $results = []) {
        if ($state === 'before') {
            $query['fields'] = [
                'reference',
                'status',
                'progress',
                'failure_message',
            ];
            if (isset($query['conditions']['exclude'])) {
                $exclude = $query['conditions']['exclude'];
                unset($query['conditions']['exclude']);
                $exclude = trim($exclude, ',');
                $exclude = explode(',', $exclude);
                $query['conditions'][] = [
                    'NOT' => [
                        'reference' => $exclude,
                    ],
                ];
            }
//            if (isset($query['conditions']['group'])) {
//                $query['conditions'][]['group'] = $query['conditions']['group'];
//                unset($query['conditions']['group']);
//            }

            return $query;
        }
        // state === after
        foreach ($results as $k => $result) {
            $results[$k] = [
                'reference' => $result['reference'],
                'status' => $result['status'],
            ];
            if (!empty($result['progress'])) {
                $results[$k]['progress'] = $result['progress'];
            }
            if (!empty($result['failure_message'])) {
                $results[$k]['failure_message'] = $result['failure_message'];
            }
        }

        return $results;
    }

    /**
     * truncate()
     *
     * @return void
     */
    public function truncate() {
        /**
         * @var \Cake\Database\Schema\TableSchema $schema 
         */
        $schema = $this->getSchema();
        $sql = $schema->truncateSql($this->getConnection());
        foreach ($sql as $snippet) {
            $this->getConnection()->execute($snippet);
        }
    }

}
