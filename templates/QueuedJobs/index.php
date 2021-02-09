<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $queueJobs
 */
?>
<div class="queueJobs index content">
    <?= $this->Html->link(__('New Queue Job'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Queue Jobs') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('queue_worker_id') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('progress') ?></th>
                    <th><?= $this->Paginator->sort('start_at') ?></th>
                    <th><?= $this->Paginator->sort('executed_at') ?></th>
                    <th><?= $this->Paginator->sort('completed_at') ?></th>
                    <th><?= $this->Paginator->sort('failed') ?></th>
                    <th><?= $this->Paginator->sort('priority') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('queue_log_count') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queueJobs as $queueJob): ?>
                <tr>
                    <td><?= h($queueJob->id) ?></td>
                    <td><?= h($queueJob->user_id) ?></td>
                    <td><?= $queueJob->has('queue_worker') ? $this->Html->link($queueJob->queue_worker->name, ['controller' => 'QueueWorkers', 'action' => 'view', $queueJob->queue_worker->id]) : '' ?></td>
                    <td><?= h($queueJob->type) ?></td>
                    <td><?= h($queueJob->name) ?></td>
                    <td><?= $this->Number->format($queueJob->progress) ?></td>
                    <td><?= h($queueJob->start_at) ?></td>
                    <td><?= h($queueJob->executed_at) ?></td>
                    <td><?= h($queueJob->completed_at) ?></td>
                    <td><?= $this->Number->format($queueJob->failed) ?></td>
                    <td><?= $this->Number->format($queueJob->priority) ?></td>
                    <td><?= h($queueJob->created) ?></td>
                    <td><?= h($queueJob->modified) ?></td>
                    <td><?= $this->Number->format($queueJob->status) ?></td>
                    <td><?= $this->Number->format($queueJob->queue_log_count) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $queueJob->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $queueJob->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $queueJob->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueJob->id)]) ?>
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
