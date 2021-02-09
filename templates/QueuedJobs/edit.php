<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $queueJob
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $queueJob->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $queueJob->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Queue Jobs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueJobs form content">
            <?= $this->Form->create($queueJob) ?>
            <fieldset>
                <legend><?= __('Edit Queue Job') ?></legend>
                <?php
                    echo $this->Form->control('user_id');
                    echo $this->Form->control('queue_worker_id', ['options' => $queueWorkers, 'empty' => true]);
                    echo $this->Form->control('type');
                    echo $this->Form->control('name');
                    echo $this->Form->control('progress');
                    echo $this->Form->control('additional_data');
                    echo $this->Form->control('start_at', ['empty' => true]);
                    echo $this->Form->control('executed_at', ['empty' => true]);
                    echo $this->Form->control('completed_at', ['empty' => true]);
                    echo $this->Form->control('failed');
                    echo $this->Form->control('failure_message');
                    echo $this->Form->control('priority');
                    echo $this->Form->control('status');
                    echo $this->Form->control('queue_log_count');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
