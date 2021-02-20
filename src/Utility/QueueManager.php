<?php

namespace Queue\Utility;

# CAKEPHP

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Event\EventManager;

# PLUGIN
use Queue\Job\TaskJob;

class QueueManager {

    protected static $_generalQueueManager = null;

    /**
     * instance
     *
     * The singleton class uses the instance() method to return the instance of the NotificationManager.
     *
     * @param null $manager Possible different manager. (Helpfull for testing).
     * @return NotificationManager
     */
    public static function instance($manager = null) {
        if ($manager instanceof NotificationManager) {
            static::$_generalQueueManager = $manager;
        }
        if (empty(static::$_generalQueueManager)) {
            static::$_generalQueueManager = new QueueManager();
        }
        return static::$_generalQueueManager;
    }

    /**
     * Places an event in the job queue
     *
     * @param Event $event Cake Event
     * @param array $options Options
     * @return void
     */
    public static function queue(Event $event, array $options = []) {
        Queue::push(
                '\Queue\Utility\QueueManager::dispatchEvent',
                [get_class($event), $event->getName(), $event->getData()],
                $options
        );
    }

    /**
     * Constructs and dispatches the event from a job
     *
     * ### Data array
     * - 0: event FQCN
     * - 1: event name
     * - 2: event data array
     *
     * @param Queue\Queue\TaskJob $taskJob TaskJob
     * @return void
     */
    public static function dispatchEvent($taskJob) {
        $eventClass = $taskJob->data(0);
        $eventName = $taskJob->data(1);
        $data = $taskJob->data(2, []);
        $event = new $eventClass($eventName, null, $data);
        EventManager::instance()->dispatch($event);
    }

}
