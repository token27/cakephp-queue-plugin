# CakePHP Queue Plugin Documentation


## Installation
You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install this plugin as composer package is:
```
composer require token27/cakephp-queue-plugin
```
Load the plugin in your `src/Application.php`'s bootstrap() using:
```php
$this->addPlugin('Token27/Queue');
```
If you want to also access the backend controller (not just using CLI), you need to use
```php
$this->addPlugin('Token27/Queue', ['routes' => true]);
```

Run the following command in the CakePHP console to create the tables using the Migrations plugin:
```sh
bin/cake migrations migrate -p Token27/Queue
```

It is also advised to have the `posix` PHP extension enabled.


## Configuration

### Global configuration
The plugin allows some simple runtime configuration.
You may create a file called `app_queue.php` inside your `config` folder (NOT the plugins config folder) to set the following values:


- Default timeout for php, 0 or null uses default_worker_timeout * 2

    ```php
    $config['Queue']['default_timeout_php'] = 0;    ```	

- Default timeout after which a job is requeue if the worker doesn't report back:

    ```php
	  $config['Queue']['worker_max_runtime'] = 120;    
    ```
*Warning:* Do not use 0 if you are using a cronjob to permanantly start a new worker once in a while and if you do not exit on idle.

- Seconds of running time after which the PHP process of the worker will terminate (0 = unlimited):

    ```php
    $config['Queue']['default_worker_timeout'] = 120 * 100;
    ```

    *Warning:* Do not use 0 if you are using a cronjob to permanently start a new worker once in a while and if you do not exit on idle. 
	This is the last defense of the tool to prevent flooding too many processes. So make sure this is long enough to never cut off jobs, but also not too long, so the process count stays in manageable range.
	
- Seconds to sleep() when no executable job is found:

    ```php
    $config['Queue']['worker_sleep_time'] = 10;
    ```

- Default number of retries if a job fails or times out:

    ```php
    $config['Queue']['default_worker_retries'] = 3;
    ```
	
- Probability in percent of an old job cleanup happening:

    ```php
    $config['Queue']['clean_olds_prob'] = 10;
    ```


- Should a worker process quit when there are no more tasks for it to execute (true = exit, false = keep running):

    ```php
    $config['Queue']['worker_exit_when_nothing_todo'] = false;
    ```

- Minimum number of seconds before a cleanup run will remove a completed task (set to 0 to disable):

    ```php
    $config['Queue']['cleanup_timeout'] = 2592000; // 30 days
    ```

- Max workers (per server):

    ```php
    $config['Queue']['workers_max'] = 3 // Defaults to 1 (single worker can be run per server)
    ```

- Multi-server setup:

    ```php
    $config['Queue']['multiserver'] = true // Defaults to false (single server)
    ```

    For multiple servers running either CLI/web separately, or even multiple CLI workers on top, make sure to enable this.

- Use a different connection:

    ```php
    $config['Queue']['connection'] = 'custom'; // Defaults to 'default'
    ```

# Create worker service

See [Documentation](WORKER_SERVICE.md).

# Callbacks

See [Documentation](CALLBACKS.md).