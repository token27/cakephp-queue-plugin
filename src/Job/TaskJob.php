<?php

namespace Queue\Job;

//use Cake\Mailer\Email;
//use Josegonzalez\CakeQueuesadilla\Queue\Queue;
use Queue\Job\Job;
use Queue\Job\JobInterface;
use Queue\Transport\JobTransport;
use Queue\Model\Table\QueueJobsTable;
use Queue\Model\Entity\QueueJob;
use Queue\Shell\Task\QueueJobTask;
//use Queue\Model\Task\QueueWorker;
use Queue\Model\Entity\QueueWorker;

# OTHERS
use InvalidArgumentException;

/**
 * @method unserialize(string $data)
 */
class TaskJob extends Job {

    /**
     * Transport class
     *
     * @var string
     */
    protected $_transport = '\Queue\Transport\JobTransport';

    /**
     * QueueJobTask
     *
     * @var
     */
    protected $_task;

    /**
     * Queue Task Entity object
     *
     * @var \Queue\Model\Entity\QueueJob
     */
    protected $_queueTask;

    /**
     * @var \Queue\Model\Task\QueueWorker $workerObject
     */
    protected $_queueWorker;

    /**
     * Constructor
     *
     * @param array|null $data_queue_task
     */
    public function __construct(?array $config = null) {
        parent::__construct();
//        if ($queueTask === null) {
//            throw new InvalidArgumentException("QueueJob cannot be null.");
//        }
        /**
         * @TODO Inject data from $config to $this->_queueTask
         *
         */
//        $this->_queueTask = $queueTask;
//        $this->_queueJobTask = $queueJobTask;

        $this->loadModel('Queue.QueueJobs');
//        $this->_queueTask = $queueTask;
//        $this->_queueWorker = $queueWorker !== null ? $queueWorker : null;
//        $this->_task = $task !== null ? $task : null;
        var_dump($this->_queueTask);
//        var_dump($this->_queueJobTask);
        var_dump($this->_task);
        sleep(20);
//        $this->_queueTask = $this->QueueJobs->newEntity($data);
//        exit();
//        $this->_queueTask = new QueueJob();

        /**
         * @TODO
         * Perform  callbacks from additional data
         */
    }

    /**
     * Start the Task Job
     *
     * @return \Queue\Transport\JobInterface
     */
    public function run($task = null): void {
        if ($task !== null) {
            $this->_task = $task;
        }
//        if (!$this->_queueJobTask instanceof QueueJobTask) {
//            throw new RuntimeException('Task must implement ' . QueueJobTask::class);
//        }
        JobTransport::runJob($this);
    }

    /**
     * Save the Work Job in the database
     *
     * @param string|array|null $content String with message or array with messages
     * @return \Queue\Transport\JobInterface
     */
    public function push(): bool {
        return JobTransport::scheduleJob($this);
    }

    /**
     * Send the Work Job immediately using the corresponding data
     *
     * @param string|array|null $content String with message or array with messages
     * @return \Queue\Transport\JobInterface
     */
    public function send($content = null): JobInterface {
        return JobTransport::runJob($this, $content);
    }

    /**
     * Send the Work Job immediately using the corresponding data
     *
     * @param string|array|null $content String with message or array with messages
     * @return \Queue\Transport\JobInterface
     */
//    public function run(): JobInterface {
//        if ($this->_queueJobTask !== null) {
//        return JobTransport::doJob($this);
//            $this->_queueJobTask->run()
//        }
//        return false;
//    }

    /**
     * Get the Queue Task Entity object
     *
     * @return
     */
    public function queueTask(): QueueJob {
        return $this->_queueTask;
    }

    /**
     * Get the Queue Task Entity object
     *
     * @return
     */
    public function queueJobTask(): QueueJobTask {
        return $this->_task;
    }

    /**
     * Overload Queue\Model\Entity\QueueJob functions
     *
     * @param string $name method name
     * @param array $args arguments
     * @return \Queue\Queue\TaskJob
     */
    public function __call(string $name, array $args): TaskJob {
//        call_user_func_array([$this->_queueTask, $name], $args);
//        if ($this->_queueWorker !== null) {
//            call_user_func_array([$this->_queueWorker, $name], $args);
//        }
        if ($this->_task !== null) {
            call_user_func_array([$this->_task, $name], $args);
        }
        return $this;
    }

}
