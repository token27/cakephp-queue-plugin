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
            <?= $this->Html->link(__('Edit Queue Job'), ['action' => 'edit', $queueJob->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Queue Job'), ['action' => 'delete', $queueJob->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueJob->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Queue Jobs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Queue Job'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueJobs view content">
            <h3><?= h($queueJob->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($queueJob->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User Id') ?></th>
                    <td><?= h($queueJob->user_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Worker') ?></th>
                    <td><?= $queueJob->has('queue_worker') ? $this->Html->link($queueJob->queue_worker->name, ['controller' => 'QueueWorkers', 'action' => 'view', $queueJob->queue_worker->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= h($queueJob->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($queueJob->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Progress') ?></th>
                    <td><?= $this->Number->format($queueJob->progress) ?></td>
                </tr>
                <tr>
                    <th><?= __('Failed') ?></th>
                    <td><?= $this->Number->format($queueJob->failed) ?></td>
                </tr>
                <tr>
                    <th><?= __('Priority') ?></th>
                    <td><?= $this->Number->format($queueJob->priority) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= $this->Number->format($queueJob->status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Log Count') ?></th>
                    <td><?= $this->Number->format($queueJob->queue_log_count) ?></td>
                </tr>
                <tr>
                    <th><?= __('Start At') ?></th>
                    <td><?= h($queueJob->start_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Executed At') ?></th>
                    <td><?= h($queueJob->executed_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Completed At') ?></th>
                    <td><?= h($queueJob->completed_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($queueJob->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($queueJob->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Additional Data') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($queueJob->additional_data)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Failure Message') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($queueJob->failure_message)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Queue Logs') ?></h4>
                <?php if (!empty($queueJob->queue_logs)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('Queue Job Id') ?></th>
                                <th><?= __('Queue Worker Id') ?></th>
                                <th><?= __('Message') ?></th>
                                <th><?= __('Data Result') ?></th>
                                <th><?= __('Created') ?></th>
                                <th><?= __('Modified') ?></th>
                                <th><?= __('Status') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($queueJob->queue_logs as $queueLogs) : ?>
                                <tr>
                                    <td><?= h($queueLogs->id) ?></td>
                                    <td><?= h($queueLogs->queue_task_id) ?></td>
                                    <td><?= h($queueLogs->queue_worker_id) ?></td>
                                    <td><?= h($queueLogs->message) ?></td>
                                    <td><?= h($queueLogs->data_result) ?></td>
                                    <td><?= h($queueLogs->created) ?></td>
                                    <td><?= h($queueLogs->modified) ?></td>
                                    <td><?= h($queueLogs->status) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'QueueLogs', 'action' => 'view', $queueLogs->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'QueueLogs', 'action' => 'edit', $queueLogs->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'QueueLogs', 'action' => 'delete', $queueLogs->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueLogs->id)]) ?>
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
