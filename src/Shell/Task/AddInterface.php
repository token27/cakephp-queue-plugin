<?php

namespace Queue\Shell\Task;

interface AddInterface {

    /**
     * Allows adding a task to the queue.
     *
     * Will create one example job in the queue, which later will be executed using run().
     *
     * @return void
     */
    public function add();
}
