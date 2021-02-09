<?php

/**
 * Copy this file to 
 * cakephp-app/src/Job
 */

namespace App\Job;

class TestJob {

    public function perform() {
        $args = func_get_args();
        debug($args);
    }

}

?>