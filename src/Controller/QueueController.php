<?php

declare(strict_types=1);

namespace Queue\Controller;

# CAKEPHP

use App\Controller\AppController;
use Cake\Core\App;
use Cake\Http\Exception\NotFoundException;

# PLUGIN 
use Queue\Utility\TasksFinder;

/**
 * QueueController Controller
 *
 * @property \Queue\Model\Table\QueueWorkersTable $QueueWorkers
 * @property \Queue\Model\Table\QueueGroupsTable $QueueGroups
 * @property \Queue\Model\Table\QueueJobsTable $QueueJobs
 * @property \Queue\Model\Table\QueueLogsTable $QueueLogs
 */
class QueueController extends AppController {

    /**
     *
     * @var Token27\Queue\Utility\TasksFinder; 
     */
    public $tasksFinder;

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
        $this->tasksFinder = new TasksFinder();
        $this->loadModel('Queue.QueueJobs');
        $this->loadModel('Queue.QueueWorkers');
        $this->loadModel('Queue.QueueGroups');
        $this->loadModel('Queue.QueueLogs');
        $this->viewBuilder()->setHelpers(['Tools.Time', 'Tools.Format', 'Tools.Text', 'Shim.Configure', 'Html', 'Form']);
    }

}
