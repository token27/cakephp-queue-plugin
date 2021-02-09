<?php

namespace Queue\Listener;

use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class QueueEventsListener implements EventListenerInterface {

    /**
     * Returns a list of events this object is implementing.
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents(): array {
        return [
            'Task.finish' => 'sendReport'
        ];
    }

    /**
     * Sends a welcome email to new user
     *
     * eventOptions
     *  - userId: User ID
     *
     * @param \Cake\Event\Event $event Event
     * @param array $eventOptions Event options
     * @return void
     */
    public function sendReport($event, $eventOptions) {
        $queueTasksTable = TableRegistry::get('Queue.QueueJobs');
        $user = $queueTasksTable->get($eventOptions['id']);
        // Add your logic here
    }

}
