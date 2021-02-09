<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $queueWorkers
 */
?>
<div class="queueWorkers index content">
    <?= $this->Html->link(__('New Queue Worker'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Queue Workers') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('server') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('pid') ?></th>
                    <th><?= $this->Paginator->sort('terminated_at') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('queue_task_count') ?></th>
                    <th><?= $this->Paginator->sort('queue_log_count') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queueWorkers as $queueWorker): ?>
                <tr>
                    <td><?= h($queueWorker->id) ?></td>
                    <td><?= h($queueWorker->server) ?></td>
                    <td><?= h($queueWorker->name) ?></td>
                    <td><?= h($queueWorker->pid) ?></td>
                    <td><?= h($queueWorker->terminated_at) ?></td>
                    <td><?= h($queueWorker->created) ?></td>
                    <td><?= h($queueWorker->modified) ?></td>
                    <td><?= $this->Number->format($queueWorker->status) ?></td>
                    <td><?= $this->Number->format($queueWorker->queue_task_count) ?></td>
                    <td><?= $this->Number->format($queueWorker->queue_log_count) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $queueWorker->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $queueWorker->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $queueWorker->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueWorker->id)]) ?>
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
