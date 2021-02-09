<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $queueGroup
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Queue Group'), ['action' => 'edit', $queueGroup->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Queue Group'), ['action' => 'delete', $queueGroup->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueGroup->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Queue Groups'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Queue Group'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueGroups view content">
            <h3><?= h($queueGroup->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($queueGroup->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($queueGroup->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= h($queueGroup->slug) ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Task Count') ?></th>
                    <td><?= $this->Number->format($queueGroup->queue_task_count) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($queueGroup->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($queueGroup->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Queue Tasks') ?></h4>
                <?php if (!empty($queueGroup->queue_tasks)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('User Id') ?></th>
                            <th><?= __('Queue Group Id') ?></th>
                            <th><?= __('Queue Worker Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Reference') ?></th>
                            <th><?= __('Progress') ?></th>
                            <th><?= __('Additional Data') ?></th>
                            <th><?= __('Start At') ?></th>
                            <th><?= __('Executed At') ?></th>
                            <th><?= __('Completed At') ?></th>
                            <th><?= __('Failed') ?></th>
                            <th><?= __('Failure Message') ?></th>
                            <th><?= __('Priority') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Queue Log Count') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($queueGroup->queue_tasks as $queueTasks) : ?>
                        <tr>
                            <td><?= h($queueTasks->id) ?></td>
                            <td><?= h($queueTasks->user_id) ?></td>
                            <td><?= h($queueTasks->queue_group_id) ?></td>
                            <td><?= h($queueTasks->queue_worker_id) ?></td>
                            <td><?= h($queueTasks->name) ?></td>
                            <td><?= h($queueTasks->reference) ?></td>
                            <td><?= h($queueTasks->progress) ?></td>
                            <td><?= h($queueTasks->additional_data) ?></td>
                            <td><?= h($queueTasks->start_at) ?></td>
                            <td><?= h($queueTasks->executed_at) ?></td>
                            <td><?= h($queueTasks->completed_at) ?></td>
                            <td><?= h($queueTasks->failed) ?></td>
                            <td><?= h($queueTasks->failure_message) ?></td>
                            <td><?= h($queueTasks->priority) ?></td>
                            <td><?= h($queueTasks->created) ?></td>
                            <td><?= h($queueTasks->modified) ?></td>
                            <td><?= h($queueTasks->status) ?></td>
                            <td><?= h($queueTasks->queue_log_count) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'QueueJobs', 'action' => 'view', $queueTasks->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'QueueJobs', 'action' => 'edit', $queueTasks->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'QueueJobs', 'action' => 'delete', $queueTasks->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueTasks->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
