<?php

namespace Token27\Queue\Job;

class ProccessJob {

    public function perform() {
        $args = func_get_args();
        debug($args);
    }

}

?>