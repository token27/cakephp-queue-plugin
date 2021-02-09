<?php

namespace Token27\Queue\Queue;

use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;

class TaskFinder {

    /**
     * @var array
     */
    private $_ignoredPlugins = [
        'Bake',
    ];

    public function getAllTasks(bool $add_plugin_name_to_task = true) {
        $tasks = [];
        $tasks = array_merge($tasks, $this->getAllAppTasks());
        $tasks = array_merge($tasks, $this->getAllPluginsTasks($add_plugin_name_to_task));
        return $tasks;
    }

    public function getAllAppTasks() {
        $tasks = [];
        $app_folders = [];
        $app_folders = array_merge($app_folders, App::classPath('QueueJobs'));
        $app_folders = array_merge($app_folders, App::classPath('Shell' . DS . 'Task'));

        if (!empty($app_folders)) {
            foreach ($app_folders as $app_folder) {
                $app_folder_to_search = new Folder($app_folder);
                $tasks = array_merge($tasks, $this->findTasksNamesInFolder($app_folder_to_search));
            }
        }
        return $tasks;
    }

    public function getAllPluginsTasks(bool $add_plugin_name_to_task = true) {
        $tasks = [];
        $plugin_folders = [];
        $plugins_loaded = Plugin::loaded();
        if (!empty($plugins_loaded)) {
            foreach ($plugins_loaded as $plugin_loaded) {
                if (in_array($plugin_loaded, $this->_ignoredPlugins)) {
                    continue;
                }
                $tasks = array_merge($tasks, $this->getAllPluginTasks($plugin_loaded, $add_plugin_name_to_task));
            }
        }

        return $tasks;
    }

    public function getAllPluginTasks(string $plugin_name, bool $add_plugin_name_to_task = true) {
        $tasks = [];
        $plugin_tasks_names = [];
        $plugin_tasks_folders = [];
        /**
         * @TODO 
         */
//        $plugin_tasks_folders = array_merge($plugin_tasks_folders, App::classPath('QueueJobs', $plugin_name));
        $plugin_tasks_folders = array_merge($plugin_tasks_folders, App::classPath('Shell' . DS . 'Task', $plugin_name));
        if (!empty($plugin_tasks_folders)) {
            foreach ($plugin_tasks_folders as $plugin_tasks_folder) {
                if (is_dir($plugin_tasks_folder)) {
                    $folder_to_search = new Folder($plugin_tasks_folder);
                    $plugin_tasks_names = array_merge($plugin_tasks_names, $this->findTasksNamesInFolder($folder_to_search));
                }
            }
        }

        if (!empty($plugin_tasks_names)) {
            foreach ($plugin_tasks_names as $plugin_task_name) {
                if ($add_plugin_name_to_task) {
                    $plugin_task_name = $plugin_name . '.' . $plugin_task_name;
                }
                $tasks[] = $plugin_task_name;
            }
        }
        return $tasks;
    }

    public function findTasksNamesInFolder(Folder $Folder) {
        $tasks_names = [];
        $files = $Folder->find('Queue.+Task\.php');
        if (!empty($files)) {
            foreach ($files as $file) {
                $file = basename($file, 'Task.php');
                $tasks_names[] = $file;
            }
        }
        return $tasks_names;
    }

    /**
     * 
     * @return int
     */
    public function countAppTasks() {
        return count($this->getAllAppTasks());
    }

    /**
     * 
     * @return int
     */
    public function isAppTasks() {
        return $this->countAppTasks() > 0 ? true : false;
    }

    /**
     * 
     * @return int
     */
    public function countPluginTasks() {
        return count($this->getAllPluginsTasks());
    }

    /**
     * 
     * @return int
     */
    public function isPluginTasks() {
        return $this->countPluginTasks() > 0 ? true : false;
    }

    /**
     * 
     * @param string $taskName
     * @param string $pluginName
     * @return boolean
     */
    public function isValidTask(string $taskName, string $pluginName = "") {
        $isValid = false;
        $tasks = [];

        if ($pluginName === "") {
            $tasks = $this->getAllAppTasks();
            if (!empty($tasks)) {
                if (in_array($taskName, $tasks)) {
                    $isValid = true;
                }
            }
        } else {
            $tasks = $this->getAllPluginsTasks();
            if (!empty($tasks)) {
                $task_name_with_plugin = $pluginName . '.' . $taskName;
                if (in_array($task_name_with_plugin, $tasks)) {
                    $isValid = true;
                }
            }
        }

        return $isValid;
    }

    /**
     * 
     * @param array $tasks
     * @return type
     */
    private function _parseTasksByPluginName(array $tasks = []) {
        if (!empty($tasks)) {
            $tmp = [];
            foreach ($tasks as $task) {
                $explode = explode('.', $task);
                $plugin_name = $explode[0];
                $task_name = $explode[1];
                if (substr($task_name, 0, 6) === 'Queue') {
                    $task_name = substr($task_name, 6, strlen($task_name));
                }
                $tmp[$plugin_name][] = $task_name;
            }
            $tasks = $tmp;
        }
        return $tasks;
    }

    /**
     * 
     * @param type $root
     * @return type
     * @throws \Exception
     */
    private function _findRoot($root) {
        do {
            $lastRoot = $root;
            $root = dirname($root);
            if (is_dir($root . '/vendor/cakephp/cakephp')) {
                return $root;
            }
        } while ($root !== $lastRoot);

        throw new \Exception('Cannot find the root of the application, unable to run tests');
    }

}
