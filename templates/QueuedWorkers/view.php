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
            <?= $this->Html->link(__('Edit Queue Worker'), ['action' => 'edit', $queueWorker->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Queue Worker'), ['action' => 'delete', $queueWorker->id], ['confirm' => __('Are you sure you want to delete # {0}?', $queueWorker->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Queue Workers'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Queue Worker'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="queueWorkers view content">
            <h3><?= h($queueWorker->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($queueWorker->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Server') ?></th>
                    <td><?= h($queueWorker->server) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($queueWorker->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Pid') ?></th>
                    <td><?= h($queueWorker->pid) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= $this->Number->format($queueWorker->status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Task Count') ?></th>
                    <td><?= $this->Number->format($queueWorker->queue_task_count) ?></td>
                </tr>
                <tr>
                    <th><?= __('Queue Log Count') ?></th>
                    <td><?= $this->Number->format($queueWorker->queue_log_count) ?></td>
                </tr>
                <tr>
                    <th><?= __('Terminated At') ?></th>
                    <td><?= h($queueWorker->terminated_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($queueWorker->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($queueWorker->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Queue Logs') ?></h4>
                <?php if (!empty($queueWorker->queue_logs)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Queue Task Id') ?></th>
                            <th><?= __('Queue Worker Id') ?></th>
                            <th><?= __('Message') ?></th>
                            <th><?= __('Data Result') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Status') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($queueWorker->queue_logs as $queueLogs) : ?>
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
            <div class="related">
                <h4><?= __('Related Queue Tasks') ?></h4>
                <?php if (!empty($queueWorker->queue_tasks)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('User Id') ?></th>
                            <th><?= __('Queue Worker Id') ?></th>
                            <th><?= __('Type') ?></th>
                            <th><?= __('Name') ?></th>
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
                        <?php foreach ($queueWorker->queue_tasks as $queueTasks) : ?>
                        <tr>
                            <td><?= h($queueTasks->id) ?></td>
                            <td><?= h($queueTasks->user_id) ?></td>
                            <td><?= h($queueTasks->queue_worker_id) ?></td>
                            <td><?= h($queueTasks->type) ?></td>
                            <td><?= h($queueTasks->name) ?></td>
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
