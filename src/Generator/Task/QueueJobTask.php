<?php

namespace Queue\Generator\Task;

use Cake\Core\App;
use IdeHelper\Generator\Directive\ExpectedArguments;
use IdeHelper\Generator\Task\TaskInterface;
use Queue\TaskFinder;

class QueueJobTask implements TaskInterface {

    /**
     * @var int[]
     */
    protected $aliases = [
        '\Queue\Model\Table\QueueJobTable::addQueueJob()' => 0,
        '\Queue\Model\Table\QueueJobTable::isQueue()' => 1,
    ];

    /**
     * @return \IdeHelper\Generator\Directive\BaseDirective[]
     */
    public function collect(): array {
        $list = [];

        $names = $this->collectQueueJobTasks();
        foreach ($names as $name => $className) {
            $list[$name] = "'$name'";
        }

        ksort($list);

        $result = [];
        foreach ($this->aliases as $alias => $position) {
            $directive = new ExpectedArguments($alias, $position, $list);
            $result[$directive->key()] = $directive;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    protected function collectQueueJobTasks(): array {
        $result = [];

        $taskFinder = new TaskFinder();
        $tasks = $taskFinder->getAllShellTasks();

        foreach ($tasks as $task) {
            $className = App::className($task, 'Shell/Task', 'Task');
            if ($className === null) {
                continue;
            }
            [, $task] = pluginSplit($task);
            $task = substr($task, 6);
            $result[$task] = $className;
        }

        return $result;
    }

}
