<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up()
    {
        $this->table('queue_groups', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => false,
            ])
            ->addColumn('slug', 'char', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queue_job_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queue_job_completed', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'slug',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('queue_jobs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('queue_group_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('queue_worker_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => false,
            ])
            ->addColumn('version', 'float', [
                'default' => '1.00',
                'null' => true,
                'precision' => 5,
                'scale' => 2,
            ])
            ->addColumn('reference', 'char', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('progress', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('additional_data', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('start_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('executed_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('completed_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('failed', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('failure_message', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => '5',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('status', 'integer', [
                'comment' => '1:queued,2:inprogress,3:paused,4:finished,5:error',
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queue_log_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'queue_group_id',
                ]
            )
            ->addIndex(
                [
                    'queue_worker_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ]
            )
            ->addIndex(
                [
                    'status',
                ]
            )
            ->create();

        $this->table('queue_logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queue_job_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queue_worker_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('message', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => true,
            ])
            ->addColumn('exit_code', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('data_result', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('status', 'integer', [
                'comment' => '1: success, 2: warning, 3:error',
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'queue_job_id',
                ]
            )
            ->addIndex(
                [
                    'queue_worker_id',
                ]
            )
            ->addIndex(
                [
                    'status',
                ]
            )
            ->create();

        $this->table('queue_workers', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 45,
                'null' => false,
            ])
            ->addColumn('pid', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => false,
            ])
            ->addColumn('server_name', 'string', [
                'default' => null,
                'limit' => 90,
                'null' => true,
            ])
            ->addColumn('timeout', 'integer', [
                'default' => '900',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('terminated_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('status', 'integer', [
                'comment' => '1:waiting job, 2:doing task, 3:terminated',
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queued_job_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('queued_log_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'name',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'pid',
                    'server_name',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'status',
                ]
            )
            ->create();

        $this->table('queue_jobs')
            ->addForeignKey(
                'queue_group_id',
                'queue_groups',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'queue_worker_id',
                'queue_workers',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();

        $this->table('queue_logs')
            ->addForeignKey(
                'queue_job_id',
                'queue_jobs',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'queue_worker_id',
                'queue_workers',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down()
    {
        $this->table('queue_jobs')
            ->dropForeignKey(
                'queue_group_id'
            )
            ->dropForeignKey(
                'queue_worker_id'
            )->save();

        $this->table('queue_logs')
            ->dropForeignKey(
                'queue_job_id'
            )
            ->dropForeignKey(
                'queue_worker_id'
            )->save();

        $this->table('queue_groups')->drop()->save();
        $this->table('queue_jobs')->drop()->save();
        $this->table('queue_logs')->drop()->save();
        $this->table('queue_workers')->drop()->save();
    }
}
