<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $queueLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $queueLog->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $queueLog->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Queue Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueLogs form content">
            <?= $this->Form->create($queueLog) ?>
            <fieldset>
                <legend><?= __('Edit Queue Log') ?></legend>
                <?php
                    echo $this->Form->control('queue_task_id', ['options' => $queueTasks]);
                    echo $this->Form->control('queue_worker_id', ['options' => $queueWorkers]);
                    echo $this->Form->control('message');
                    echo $this->Form->control('data_result');
                    echo $this->Form->control('status');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
