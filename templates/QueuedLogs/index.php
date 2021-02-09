<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $queueLogs
 */
?>
<div class="queueLogs index content">
    <?= $this->Html->link(__('New Queue Log'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Queue Logs') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('queue_task_id') ?></th>
                    <th><?= $this->Paginator->sort('queue_worker_id') ?></th>
                    <th><?= $this->Paginator->sort('message') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queueLogs as $queueLog): ?>
                <tr>
                    <td><?= h($queueLog->id) ?></td>
                    <td><?= $queueLog->has('queue_task') ? $this->Html->link($queueLog->queue_task->name, ['controller' => 'QueueJobs', 'action' => 'view', $queueLog->queue_task->id]) : '' ?></td>
                    <td><?= $queueLog->has('queue_worker') ? $this->Html->link($queueLog->queue_worker->name, ['controller' => 'QueueWorkers', 'action' => 'view', $queueLog->queue_worker->id]) : '' ?></td>
                    <td><?= h($queueLog->message) ?></td>
                    <td><?= h($queueLog->created) ?></td>
                    <td><?= h($queueLog->modified) ?></td>
                    <td><?= $this->Number->format($queueLog->status) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $queueLog->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $queueLog->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $queueLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueLog->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
