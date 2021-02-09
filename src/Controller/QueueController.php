<?php

declare(strict_types=1);

namespace Token27\Queue\Controller;

# CAKEPHP

use App\Controller\AppController;
use Cake\Core\App;
use Cake\Http\Exception\NotFoundException;

# PLUGIN 
use Token27\Queue\TaskFinder;

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
     * @var Queue\Queue\TaskFinder; 
     */
    public $taskFinder = null;

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
        $this->taskFinder = new TaskFinder();
        $this->loadModel('Queue.QueueJobs');
        $this->loadModel('Queue.QueueWorkers');
        $this->loadModel('Queue.QueueGroups');
        $this->loadModel('Queue.QueueLogs');
        $this->viewBuilder()->setHelpers(['Tools.Time', 'Tools.Format', 'Tools.Text', 'Shim.Configure', 'Html', 'Form']);
    }

}
