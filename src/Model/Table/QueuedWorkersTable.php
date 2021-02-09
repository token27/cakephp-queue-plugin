<?php

declare(strict_types=1);

namespace Token27\Queue\Model\Table;

# CAKEPHP

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

# PLUGIN
use Token27\Queue\Config as QueueConfig;
use Token27\Queue\WorkersNames;
use Token27\Queue\Model\ProcessEndingException;

/**
 * QueueWorkers Model
 *
 * @property \Queue\Model\Table\QueueLogsTable&\Cake\ORM\Association\HasMany $QueueLogs
 * @property \Queue\Model\Table\QueueJobsTable&\Cake\ORM\Association\HasMany $QueueJobs
 *
 * @method \Queue\Model\Entity\QueueWorker newEmptyEntity()
 * @method \Queue\Model\Entity\QueueWorker newEntity(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueWorker[] newEntities(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueWorker get($primaryKey, $options = [])
 * @method \Queue\Model\Entity\QueueWorker findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Queue\Model\Entity\QueueWorker patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueWorker[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueWorker|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueWorker saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueWorker[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueWorker[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueWorker[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueWorker[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class QueueWorkersTable extends Table {

    /**
     * @var string|null
     */
    protected $_key;

    /**
     * Sets connection name
     *
     * @return string
     */
    public static function defaultConnectionName(): string {
        $connection = QueueConfig::defaultDatabaseConnection();
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

        $this->setTable('queue_workers');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');



        $this->hasMany('QueueLogs', [
            'foreignKey' => 'queue_worker_id',
            'className' => 'Queue.QueueLogs',
        ]);
        $this->hasMany('QueueJobs', [
            'foreignKey' => 'queue_worker_id',
            'className' => 'Queue.QueueJobs',
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
                ->scalar('server')
                ->maxLength('server', 90)
                ->allowEmptyString('server');

        $validator
                ->scalar('name')
                ->maxLength('name', 45)
                ->requirePresence('name', 'create')
                ->notEmptyString('name')
                ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
                ->scalar('pid')
                ->maxLength('pid', 40)
                ->requirePresence('pid', 'create')
                ->notEmptyString('pid');

        $validator
                ->dateTime('terminated_at')
                ->allowEmptyDateTime('terminated_at');

        $validator
                ->integer('status')
                ->notEmptyString('status');

        $validator
                ->integer('queue_job_count')
                ->notEmptyString('queue_job_count');

        $validator
                ->integer('queue_log_count')
                ->notEmptyString('queue_log_count');

        $validator
                ->add('server', 'validateCount', [
                    'rule' => 'validateCount',
                    'provider' => 'table',
                    'message' => 'Too many workers running. Check your `Queue.max_workers` config.',
        ]);


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
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);

        return $rules;
    }

    /**
     * @param string $value
     * @param array $context
     *
     * @return bool
     */
    public function validateCount($value, array $context) {
        $maxWorkers = QueueConfig::workersMax();
        if (!$value || !$maxWorkers) {
            return true;
        }

        $currentWorkersRunning = $this->find()->where([
                    'server_name' => $value,
                    'status !=' => 3,
                ])->count();
        if ($currentWorkersRunning >= $maxWorkers) {
            return false;
        }

        return true;
    }

    /**
     * @return \Cake\ORM\Query
     */
    public function findActive() {
        $timeout = QueueConfig::defaultWorkerTimeout();
        $thresholdTime = (new FrozenTime())->subSeconds($timeout);

        return $this->find()->where([
                    'modified > ' => $thresholdTime
        ]);
    }

    /**
     * @param string $name
     * @param string $pid
     *
     * @return int
     */
    public function add($pid, string $name = "") {
        $data = [
            'server_name' => $this->buildServerString(),
            'pid' => $pid,
        ];
        /**
         * @TODO Replace for machine name
         */
        if ($name === "") {
            $name = WorkersNames::getRamdomName();
        }
        $data['name'] = $name;
        $queueWorker = $this->newEntity($data);
        $this->saveOrFail($queueWorker);

        return $queueWorker;
    }

    /**
     * Generates a unique Identifier for the current worker thread.
     *
     * Useful to identify the currently running processes for this thread.
     *
     * @return string Identifier
     */
    public function key() {
        if ($this->_key !== null) {
            return $this->_key;
        }
        $this->_key = sha1((microtime() + mt_rand(200, 700)));
        if (!$this->_key) {
            throw new RuntimeException('Invalid key generated');
        }

        return $this->_key;
    }

    /**
     * Resets worker Identifier
     *
     * @return void
     */
    public function clearKey() {
        $this->_key = null;
    }

    /**
     * Use ENV to control the server name of the servers run workers with.
     *
     * export SERVER_NAME=myserver1
     *
     * This way you can deploy separately and only end the processes of that server.
     *
     * @return string|null
     */
    public function buildServerString() {
        $serverName = (string) env('SERVER_NAME') ?: gethostname();
        if (!$serverName) {
            $user = env('USER');
            $logName = env('LOGNAME');
            if ($user || $logName) {
                $serverName = $user . '@' . $logName;
            }
        }

        return $serverName ?: null;
    }

    /**
     * @param string $pid
     * @throws \Queue\Model\ProcessEndingException
     * @return void
     */
    public function update($pid) {
        $conditions = [
            'pid' => $pid,
            'server_name IS' => $this->buildServerString(),
        ];

        $queueWorker = $this->find()->where($conditions)->firstOrFail();
        if ($queueWorker->status == 3) {
            $this->remove($pid);
            throw new ProcessEndingException('PID terminated: ' . $pid);
        }

        $queueWorker->modified = new FrozenTime();
        $this->saveOrFail($queueWorker);
    }

    /**
     * @param string $pid
     * @throws \Queue\Model\ProcessEndingException
     * @return void
     */
    public function waiting($pid) {
        $conditions = [
            'pid' => $pid,
            'server_name IS' => $this->buildServerString(),
        ];

        $queueWorker = $this->find()->where($conditions)->firstOrFail();
        if ($queueWorker->status == 3) {
            $this->remove($pid);
            throw new ProcessEndingException('PID terminated: ' . $pid);
        }
        $queueWorker->status = 1;
        $this->saveOrFail($queueWorker);
    }

    /**
     * @param string $pid
     * @throws \Queue\Model\ProcessEndingException
     * @return void
     */
    public function working($pid) {
        $conditions = [
            'pid' => $pid,
            'server_name IS' => $this->buildServerString(),
        ];

        $queueWorker = $this->find()->where($conditions)->firstOrFail();
        if ($queueWorker->status == 3) {
            $this->remove($pid);
            throw new ProcessEndingException('PID terminated: ' . $pid);
        }

        $queueWorker->status = 2;
        $this->saveOrFail($queueWorker);
    }

    /**
     * @param string $pid
     * @throws \Queue\Model\ProcessEndingException
     * @return void
     */
    public function terminate($pid) {
        $conditions = [
            'pid' => $pid,
            'server_name IS' => $this->buildServerString(),
        ];

        $queueWorker = $this->find()->where($conditions)->firstOrFail();
//        if ($queueWorker->status == 3) {
//            $this->remove($pid);
//            throw new ProcessEndingException('PID terminated: ' . $pid);
//        }

        if ($queueWorker) {
            $queueWorker->status = 3;
            $queueWorker->terminated_at = date('Y-m-d H:i:s');
            $this->saveOrFail($queueWorker);
        }
    }

    /**
     * @param string $pid
     *
     * @return void
     */
    public function remove($pid) {
        $conditions = [
            'pid' => $pid,
            'server_name IS' => $this->buildServerString(),
        ];

        $this->deleteAll($conditions);
    }

    /**
     * @return int
     */
    public function cleanTimeouts() {
        $timeout = QueueConfig::defaultWorkerTimeout();
        if ($timeout > 0) {
            $timeout += mt_rand(30, 60);
            return $this->cleanEnded($timeout);
        }
        return false;
    }

    /**
     * @return int
     */
    public function cleanEnded(int $timeout = 0) {
        if ($timeout == 0) {
            $timeout = QueueConfig::defaultWorkerTimeout();
        }
        $thresholdTime = (new FrozenTime())->subSeconds($timeout);
        $conditions = [
            'modified <' => $thresholdTime
        ];
        return $this->deleteAll($conditions);
    }

    /**
     * If pid logging is enabled, will return an array with
     * - time: Timestamp as FrozenTime object
     * - workers: int Count of currently running workers
     *
     * @return array
     */
    public function getCurrentlyRunningStats() {
        $timeout = QueueConfig::defaultWorkerTimeout();
        $thresholdTime = (new FrozenTime())->subSeconds($timeout);

        $results = $this->find()
                ->where(['modified >' => $thresholdTime])
                ->orderDesc('modified')
                ->enableHydration(false)
                ->all()
                ->toArray();

        if (!$results) {
            return [];
        }

        $count = count($results);
        $record = array_shift($results);
        /** @var \Cake\I18n\FrozenTime $time */
        $time = $record['modified'];

        return [
            'time' => $time,
            'workers' => $count,
        ];
    }

    /**
     * If pid logging is enabled, will return an array with
     * - time: Timestamp as FrozenTime object
     * - workers: int Count of currently running workers
     *
     * @return array
     */
    public function getRunning() {

        $timeout = QueueConfig::defaultWorkerTimeout();
        $thresholdTime = (new FrozenTime())->subSeconds($timeout);

        return $this->find()
                        ->where(['modified >' => $thresholdTime])
                        ->orderDesc('modified')
                        ->enableHydration(false)
                        ->all()
                        ->toArray();
    }

    /**
     * If pid logging is enabled, will return an array with
     * - time: Timestamp as FrozenTime object
     * - workers: int Count of currently running workers
     *
     * @return array
     */
    public function getTimeouts(int $timeout = 0) {
        if ($timeout == 0) {
            $timeout = QueueConfig::defaultWorkerTimeout();
        }
        $thresholdTime = (new FrozenTime())->subSeconds($timeout);

        return $this->find()
                        ->where(['modified <' => $thresholdTime])
                        ->orderDesc('modified')
                        ->enableHydration(false)
                        ->all()
                        ->toArray();
    }

    /**
     * 
     * @param string $workerId
     * @param string $server_name
     * @return type
     */
    public function getWorker(string $workerId, string $server_name = "") {

        $conditions = [
            'OR' => [
                'id' => $workerId,
                'name' => $workerId,
                'pid' => $workerId,
            ]
        ];

        if ($server_name != "") {
            $conditions['server_name'] = $server_name;
        }

        return $this->find()
                        ->where($conditions)
                        ->orderDesc('modified')
                        ->enableHydration(false)
                        ->first();
    }

    /**
     * Gets all active workers.
     *
     * $forThisServer only works for DB approach.
     *
     * @param bool $forThisServer
     * @return array
     */
    public function getWorkers($forThisServer = false) {
        $query = $this->findActive()
                ->where(['status !=' => 3]);
        if ($forThisServer) {
            $query = $query->where(['server_name' => $this->buildServerString()]);
        }

        $workers = $query
                ->enableHydration(false)
                ->find('list', ['keyField' => 'pid', 'valueField' => 'modified'])
                ->all()
                ->toArray();

        return $workers;
    }

    /**
     * Soft ending of a running job, e.g. when migration is starting
     *
     * @param int|null $pid
     * @return void
     */
    public function endWorker($pid) {
        if (!$pid) {
            return;
        }
        $queueWorker = $this->find()->where(['pid' => $pid])->firstOrFail();
        $queueWorker->status = 3;
        $this->saveOrFail($queueWorker);
    }

    /**
     * Note this does not work from the web backend to kill CLI workers.
     * We might need to run some exec() kill command here instead.
     *
     * @param int $pid
     * @param int $sig Signal (defaults to graceful SIGTERM = 15)
     * @return void
     */
    public function killWorkerProcess($pid, $sig = SIGTERM) {
        if (!$pid) {
            return;
        }

        $killed = false;
        if (function_exists('posix_kill')) {
            $killed = posix_kill($pid, $sig);
        }
        /**
         * @TODO Valiate OS
         *       Windows: 
         *               By Pid: taskkill /F /PID [pid_number] 
         *               By Name: taskkill /IM "process name" /F 
         *       Linux: kill -[SIG] [pid_number]
         */
        if (!$killed) {
            @exec('kill -' . $sig . ' ' . $pid);
            @exec('taskkill /F /PID ' . $pid);
        }
        sleep(mt_rand(3, 5));

        $this->deleteAll(['pid' => $pid]);
    }

}
