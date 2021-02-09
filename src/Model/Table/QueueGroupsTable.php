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
 * QueueGroups Model
 *
 * @property \Queue\Model\Table\QueueJobsTable&\Cake\ORM\Association\HasMany $QueueJobs
 *
 * @method \Queue\Model\Entity\QueueGroup newEmptyEntity()
 * @method \Queue\Model\Entity\QueueGroup newEntity(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueGroup[] newEntities(array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueGroup get($primaryKey, $options = [])
 * @method \Queue\Model\Entity\QueueGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Queue\Model\Entity\QueueGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Queue\Model\Entity\QueueGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Queue\Model\Entity\QueueGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Queue\Model\Entity\QueueGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class QueueGroupsTable extends Table {

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

        $this->setTable('queue_groups');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('QueueJobs', [
            'foreignKey' => 'queue_group_id',
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
                ->scalar('name')
                ->maxLength('name', 150)
                ->requirePresence('name', 'create')
                ->notEmptyString('name');

        $validator
                ->scalar('slug')
                ->maxLength('slug', 50)
                ->requirePresence('slug', 'create')
                ->notEmptyString('slug')
                ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
                ->integer('queue_task_count')
                ->notEmptyString('queue_task_count');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }

    /**
     * Return a list of all job groups in the Queue.
     *
     * @return \Cake\ORM\Query
     */
    public function getGroups($status = null) {

//        return $this->find('list')
//                        ->select([
//                            'id',
//                            'name',
//                        ])
//                        ->enableHydration(false)
//                        ->orderDesc('modified')
//                        ->all()
//                        ->toArray();
//        
        $findOptions = [
            'fields' => [
                'id',
                'name',
            ],
            'keyField' => 'id',
            'valueField' => 'name',
        ];
        return $this->find('list', $findOptions);
    }

}
