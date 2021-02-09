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
            <?= $this->Html->link(__('Edit Queue Log'), ['action' => 'edit', $queueLog->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Queue Log'), ['action' => 'delete', $queueLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueLog->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Queue Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Queue Log'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueLogs view content">
            <h3><?= h($queueLog->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($queueLog->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Task') ?></th>
                    <td><?= $queueLog->has('queue_task') ? $this->Html->link($queueLog->queue_task->name, ['controller' => 'QueueJobs', 'action' => 'view', $queueLog->queue_task->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Worker') ?></th>
                    <td><?= $queueLog->has('queue_worker') ? $this->Html->link($queueLog->queue_worker->name, ['controller' => 'QueueWorkers', 'action' => 'view', $queueLog->queue_worker->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Message') ?></th>
                    <td><?= h($queueLog->message) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= $this->Number->format($queueLog->status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($queueLog->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($queueLog->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Data Result') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($queueLog->data_result)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
