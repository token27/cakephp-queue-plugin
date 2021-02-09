# CakePHP Queue Plugin Callbacks


## The plugin allows callbacks.

### Example configuration
You may create a folder called `Job` inside your `cakephp-app/src/` (NOT the plugins app folder).
Then create a file called `TestJob.php` inside the folder and set the following values:

```php
<?php

namespace App\Job;

class TestJob {

    public function perform() {    
        $args = func_get_args();
        debug($args);
    }

}

?>
```

## - Example callbacks (single / multiple) 

```php
<?php

use Token27\Queue\TaskJob;

$data = [
    'name' => 'Testing',
];

$taskJob = new TaskJob($data);

$taskJob->addBeforeSendCallback(
        ['\App\Job\TestJob', 'perform'],
        [
            'command' => 'update',
            'user_id' => '25c262ff-b8c2-4e81-9895-282950e9a9c7',
        ]
);

$taskJob->addBeforeSendCallback(
        ['\App\Job\TestJob', 'perform'],
        [
            'command' => 'lock',
            'user_id' => 'de4ee54e-9212-4906-8926-52910c11cc1f',
        ]
);

$taskJob->addAfterSendCallback(
        ['\App\Job\TestJob', 'perform'],
        [
            'command' => 'unlock',
            'user_id' => 'de4ee54e-9212-4906-8926-52910c11cc1f',
        ]
);

$taskJob->send();

```