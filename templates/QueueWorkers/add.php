<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $queueWorker
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Queue Workers'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueWorkers form content">
            <?= $this->Form->create($queueWorker) ?>
            <fieldset>
                <legend><?= __('Add Queue Worker') ?></legend>
                <?php
                    echo $this->Form->control('server');
                    echo $this->Form->control('name');
                    echo $this->Form->control('pid');
                    echo $this->Form->control('terminated_at', ['empty' => true]);
                    echo $this->Form->control('status');
                    echo $this->Form->control('queue_task_count');
                    echo $this->Form->control('queue_log_count');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
