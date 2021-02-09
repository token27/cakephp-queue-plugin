<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $queueGroups
 */
?>
<div class="queueGroups index content">
    <?= $this->Html->link(__('New Queue Group'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Queue Groups') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('queue_task_count') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queueGroups as $queueGroup): ?>
                <tr>
                    <td><?= h($queueGroup->id) ?></td>
                    <td><?= h($queueGroup->name) ?></td>
                    <td><?= h($queueGroup->slug) ?></td>
                    <td><?= h($queueGroup->created) ?></td>
                    <td><?= h($queueGroup->modified) ?></td>
                    <td><?= $this->Number->format($queueGroup->queue_task_count) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $queueGroup->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $queueGroup->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $queueGroup->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueGroup->id)]) ?>
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
