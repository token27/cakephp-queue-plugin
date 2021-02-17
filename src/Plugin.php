<?php

namespace Queue;

# CAKEPHP

use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Core\PluginApplicationInterface;
use Cake\Core\Configure;
use Cake\Http\Middleware;
use Cake\Http\MiddlewareQueue;
use Cake\Event\Event;
use Cake\Event\EventManager;

# PLUGIN
use Queue\Utility\Config;
use Queue\Listener\QueueEventsListener;

/**
 * Plugin for Queue
 */
class Plugin extends BasePlugin {
    /**
     * @var bool
     */
//    protected $middlewareEnabled = false;

    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void {

        $app->addPlugin('Tools');

        /**
         * @note Optionally load additional queue config defaults from local app config
         */
        Config::loadPluginConfiguration();


        EventManager::instance()->on(new QueueEventsListener());
        /**
         *  @note For IdeHelper plugin if in use.
         *        Make sure to run `bin/cake phpstorm generate` then.
         */
//        $generatorTasks = (array) Configure::read('IdeHelper.generatorTasks');
//        $generatorTasks[] = QueueJob::class;
//        Configure::write('IdeHelper.generatorTasks', $generatorTasks);
    }

    /**
     * Add routes for the plugin.
     *
     * If your plugin has many routes and you would like to isolate them into a separate file,
     * you can create `$plugin/config/routes.php` and delete this method.
     *
     * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes(RouteBuilder $routes): void {

        $routes->plugin(
                'Queue',
                ['path' => '/queue'],
                function (RouteBuilder $builder) {

            $builder->connect('/', ['controller' => 'QueueDashboard', 'actions' => 'index']);
            $builder->connect('/dashboard/', ['controller' => 'QueueDashboard', 'actions' => 'index']);
            $builder->connect('/dashboard/:action/', ['controller' => 'QueueDashboard']);

            $builder->connect('/workers/', ['controller' => 'QueueWorkers', 'actions' => 'index']);
            $builder->connect('/workers/:action/', ['controller' => 'QueueWorkers']);

            $builder->connect('/tasks/', ['controller' => 'QueueJobs', 'actions' => 'index']);
            $builder->connect('/tasks/:action/', ['controller' => 'QueueJobs']);

            $builder->connect('/groups/', ['controller' => 'QueueGroups', 'actions' => 'index']);
            $builder->connect('/groups/:action/', ['controller' => 'QueueGroups']);

            $builder->connect('/logs/', ['controller' => 'QueueLogs', 'actions' => 'index']);
            $builder->connect('/logs/:action/', ['controller' => 'QueueLogs']);

            $builder->fallbacks();
        }
        );
        parent::routes($routes);
    }

    /**
     * Add middleware for the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {


        return $middlewareQueue;
    }

}
