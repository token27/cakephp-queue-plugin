<?php

declare(strict_types=1);

namespace Queue\Controller;

# CAKEPHP

use Cake\Core\Configure;
use Cake\Core\Exception;
# PLUGIN
use Queue\Config;
use Queue\TaskFinder;
use Queue\Controller\QueueController;

#
use Queue\TaskJob;

/**
 * QueueDashboard Controller
 *
 * @property \Queue\Model\Table\QueueWorkersTable $QueueWorkers
 * @property \Queue\Model\Table\QueueGroupsTable $QueueGroups
 * @property \Queue\Model\Table\QueueJobsTable $QueueJobs
 * @property \Queue\Model\Table\QueueLogsTable $QueueLogs
 */
class QueueDashboardController extends QueueController {

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void {
        parent::initialize();
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {

        $status = $this->QueueWorkers->getCurrentlyRunningStats();
        $current = $this->QueueJobs->getLength();
        $pendingDetails = $this->QueueJobs->getPending();
        $new = 0;
        foreach ($pendingDetails as $pendingDetail) {
            if ($pendingDetail['executed_at'] || $pendingDetail['failed']) {
                continue;
            }
            $new++;
        }

        $data = $this->QueueJobs->getStats();

        $tasks = $this->tasksFinder->getAllShellTasks();

        $servers = $this->QueueWorkers
                ->find()
                ->distinct(['server_name'])
                ->find('list', [
                    'keyField' => 'server_name',
                    'valueField' => 'server_name'
                ])
                ->toArray();
//            exit();
        $this->set(compact('new', 'current', 'data', 'pendingDetails', 'status', 'tasks', 'servers'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function config() {
        echo "<pre>";

        try {

            if (file_exists(ROOT . DS . 'config' . DS . 'app_queue.php')) {
                echo "Load from APP" . PHP_EOL;
                Configure::load('app_queue');
            } else {
                echo "Load from PLUGIN" . PHP_EOL;
                Configure::load('Queue.app_queue');
            }
            var_dump(Configure::read('Queue'));

            echo "Load config..." . PHP_EOL;

            Config::loadPluginConfiguration();
            var_dump(Config::defaultDatabaseConnection());
            var_dump(Config::workersMax());
            var_dump(Config::workerMaxRuntime());
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
        }
        exit();
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function tasks() {
        echo "<pre>";
        try {
            $taskFinder = new TaskFinder();
            $tasks = $taskFinder->getAllShellTasks();
            var_dump($tasks);
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
        }
        exit();
    }

    /**
     * Job method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function job() {
        echo "<pre>";

        try {
            $data = [
                'name' => 'Testing',
            ];

            $taskJob = new TaskJob($data);

            $taskJob->addBeforeSendCallback(
                    ['\Queue\Job\ProccessJob', 'perform'],
                    [
                        'command' => 'update',
                        'user_id' => '25c262ff-b8c2-4e81-9895-282950e9a9c7',
                    ]
            );

            $taskJob->addBeforeSendCallback(
                    ['\Queue\Job\ProccessJob', 'perform'],
                    [
                        'command' => 'lock',
                        'user_id' => 'de4ee54e-9212-4906-8926-52910c11cc1f',
                    ]
            );

            $taskJob->addAfterSendCallback(
                    ['\Queue\Job\ProccessJob', 'perform'],
                    [
                        'command' => 'unlock',
                        'user_id' => 'de4ee54e-9212-4906-8926-52910c11cc1f',
                    ]
            );

            $taskJob->push();
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
        }
        exit();
    }

}
