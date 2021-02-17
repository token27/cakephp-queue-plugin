<?php

namespace Queue\Shell;

# CAKEPHP

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use Cake\I18n\Number;
use Cake\Log\Log;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use RuntimeException;
use Throwable;

# PLUGIN 
use Queue\TaskFinder;
use Queue\Shell\Task\AddInterface;

declare(ticks=1);

class QueuedShell extends Shell {

    /**
     *
     * @var Queue\Queue\TaskFinder; 
     */
    public $taskFinder = null;

    /**
     * Overwrite shell initialize to dynamically load all Queue Related Tasks.
     *
     * @return void
     */
    public function initialize(): void {
        exit();
        $this->taskFinder = new TaskFinder();
        $this->tasks = $this->taskFinder->getAllTasks();
        parent::initialize();
        $this->loadModel('Queue.QueueTasks');
        $this->loadModel('Queue.QueueWorkers');
    }

    /**
     * Get option parser method to parse commandline options
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser {

        $parser = parent::getOptionParser();

        $parser->addSubcommand('list', [
            'help' => __('Show all the tasks list.'),
            'parser' => [
                'description' => [
                    __('Use this command to SHOW all the tasks list.'),
                ],
                'arguments' => [
                    'type' => [
                        'help' => __('The type of task to show.'),
                        'required' => false,
                        'choices' => [
                            'all',
                            'app',
                            'plugins'
                        ]
                    ],
                ],
            ]
        ]);

        $options_add = [];
        $options_add['log'] = [
            'long' => 'log',
            'help' => 'The log status.',
            'default' => false,
        ];
        $options_add['plugin'] = [
            'long' => 'plugin',
            'help' => 'The plugin name.',
            'default' => null,
        ];
        $options_add['command'] = [
            'long' => 'command',
            'help' => 'The command to execute. (This is only for ExecuteShell task)',
            'default' => null,
        ];
        $options_add['command-params'] = [
            'long' => 'command-params',
            'help' => 'The params to add in the command to execute. (This is only for ExecuteCommand task)',
            'default' => null,
        ];
        $parser->addSubcommand('add', [
            'help' => __('Add task job.'),
            'parser' => [
                'description' => [
                    __('Use this command to ADD task job in the queue list. Name syntax: If the file is QueueBackupTask.php, the name of the task is "Backup".'),
                ],
                'options' => $options_add,
//                'arguments' => [
//                    'taskname' => [
//                        'help' => __('The name of the task to add.'),
//                        'required' => true
//                    ],
////                    'pluginname' => [
////                        'help' => __('The name of the plugin where is the task to add.'),
////                        'required' => false
////                    ],
//                    'commands' => [
//                        'help' => __('.'),
//                        'required' => false
//                    ],
//                ],
            ]
        ]);



        $parser->addSubcommand('view', [
            'help' => __('View the queue task job details.'),
            'parser' => [
                'description' => [
                    __('Use this command to view the queue job details.'),
                ],
                'arguments' => [
                    'queue-task-id' => [
                        'help' => __('The queue task id from database.'),
                        'required' => true
                    ],
                ],
            ]
        ]);

        $parser->addSubcommand('stats', [
            'help' => __('View queue jobs stats.'),
            'parser' => [
                'description' => [
                    __('Use this command to view queue jobs stats.'),
                ],
                'arguments' => [
                    'type' => [
                        'help' => __('The type of queue jobs to show.'),
                        'required' => false,
                        'choices' => [
                            'all',
                            'app',
                            'plugins'
                        ]
                    ],
                ],
            ]
        ]);

        $parser->addSubcommand('clean', [
            'help' => __('Use this command to CLEAN all/olds/locked queue task jobs.'),
            'parser' => [
                'description' => [
                    __('Use this command to CLEAN all/olds/locked queue task jobs.'),
                ],
                'arguments' => [
                    'type' => [
                        'help' => __('The queue task type to clean.'),
                        'required' => false,
                        'choices' => [
                            'all',
                            'olds',
                            'locked'
                        ]
                    ],
                ],
            ]
        ]);

        return $parser;
    }

    /**
     * Main
     *
     * @access public
     */
    public function main() {
        $this->out($this->OptionParser->help());
        return true;
    }

    /**
     * View
     *
     * @access public
     */
    public function list() {
        $type = "all";
        if (isset($this->args) && !empty($this->args)) {
            $type = $this->args[0];
        }
        switch ($type) {
            case "app":
                $this->hr();
                $this->out(' ');
                $this->_showAppTasks();
                $this->hr();
                $this->out(' ');
                break;
            case "plugins":
                $this->hr();
                $this->out(' ');
                $this->_showPluginsTasks();
                $this->hr();
                $this->out(' ');
                break;
            case "all":
            default:
                $this->hr();
                $this->out(' ');
                $this->_showAppTasks();
                $this->hr();
                $this->out(' ');
                $this->_showPluginsTasks();
                $this->hr();
                $this->out(' ');
                break;
        }
    }

    /**
     * Look for a Queue Task of hte passed name and try to call add() on it.
     * A QueueTask may provide an add function to enable the user to create new jobs via commandline.
     *
     * @return void
     */
    public function add() {
        if (count($this->args) < 1) {
            $this->out('Please call like this:');
            $this->out('    bin/cake queue add <taskname>');
            $this->_displayAvailableTasks();

            return;
        }
        $name = Inflector::camelize($this->args[0]);
        $taks_name = null;
        if (in_array($name, $this->taskNames, true)) {
            $taks_name = $name;
            $task->add();
        } else if (in_array('Queue' . $name, $this->taskNames, true)) {
            $taks_name = 'Queue' . $name;
        }
        if ($taks_name) {
            $task = $this->{$taks_name};
            if (!($task instanceof AddInterface)) {
                $this->abort('This task does not support adding via CLI call');
            }
            $task->add();
        } else {
            $this->out('Error: Task not found: ' . $name);
            $this->_displayAvailableTasks();
        }
    }

//    
//    /**
//     * Publish
//     *
//     * @access public
//     */
//    public function add() {
//        $this->out(' ');
//
//        $taskName = "";
//        $pluginName = "Queue";
//        if (isset($this->args) && !empty($this->args)) {
//            $taskName = Inflector::camelize($this->args[0]);
//            if (count($this->args) > 1) {
//                $pluginName = $this->args[1];
//            }
//        }
//
//        if ($taskName != "") {
//            $this->info(' -> Searching task...');
//            $this->out('  * Plugin: ' . $pluginName);
//            $this->out('  * Task: ' . $taskName);
//
//            $task = null;
//            if (in_array('Queue.Queue' . $taskName, $this->tasks, true)) {
//                $task = $this->{'Queue.Queue' . $taskName};
//            } else if (in_array('Queue' . $taskName, $this->tasks, true)) {
//                $task = $this->{'Queue' . $taskName};
//            }
//            if ($task != null) {
//                if (!($task instanceof AddInterface)) {
//                    $this->abort('This task does not support adding via CLI call');
//                }
//                $task->add();
//            } else {
//                $this->out('Error: Task not found: ' . $taskName);
//            }
//        } else {
//            $this->warn('  ! The task name cannot be empty. <-');
//        }
//    }

    /**
     * Show App Tasks
     *
     * @access private
     */
    private function _showAppTasks() {
        $this->info('  -> Searching APP tasks...');
        $this->out(' ');

        $appTasks = $this->taskFinder->getAllAppTasks();
        if (count($appTasks) > 0) {
            $this->success('  -> Success, APP task(s) found. <-');
            $this->out(' ');
            foreach ($appTasks as $appTask) {
                $this->out('   * ' . $appTask);
            }
        } else {
            $this->warn('  ! No one APP task found.');
        }
        $this->out(' ');
    }

    /**
     * Show Plugin Tasks
     *
     * @access private
     */
    private function _showPluginsTasks() {
        $this->info('  -> Searching PLUGINS tasks...');
        $this->out(' ');
        $pluginsTasks = $this->taskFinder->getAllPluginsTasks();
        if (count($pluginsTasks) > 0) {
            $this->success('  -> Success, PLUGIN task(s) found. <-');
            $this->out(' ');
            foreach ($pluginsTasks as $pluginTaskName) {
                $this->out('  * ' . $pluginTaskName);
//                $pluginTaskNameExplode = explode('.', $pluginTaskName);
//                $this->out('  * Task: ' . $pluginTaskNameExplode[1]);
            }
        } else {
            $this->warn('  ! No one PLUGIN task found.');
        }
        $this->out(' ');
    }

    /**
     * @return void
     */
    protected function _displayAvailableTasks() {

        $this->info('  -> Available Tasks:');
        $tasks = $this->taskNames;
        sort($tasks);
        foreach ($tasks as $loadedTask) {
            $this->out("\t" . '* ' . $this->_taskName($loadedTask));
        }
    }

    /**
     * Output the task without Queue or Task
     * example: QueueImageTask becomes Image on display
     *
     * @param string $task Task name
     * @return string Cleaned task name
     */
    protected function _taskName($task) {
        if (strpos($task, 'Queue') === 0) {
            return substr($task, 6);
        }

        return $task;
    }

}
