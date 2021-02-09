<?php

declare(strict_types=1);

namespace Token27\Queue\Controller;

use Token27\Queue\Controller\QueueController;

/**
 * QueueLogs Controller
 *
 * @property \Queue\Model\Table\QueueLogsTable $QueueLogs
 * @method \Queue\Model\Entity\QueueLog[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class QueueLogsController extends QueueController {

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {
        $this->paginate = [
            'contain' => ['QueueJobs', 'QueueWorkers'],
        ];
        $queueLogs = $this->paginate($this->QueueLogs);

        $this->set(compact('queueLogs'));
    }

    /**
     * View method
     *
     * @param string|null $id Queue Log id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {
        $queueLog = $this->QueueLogs->get($id, [
            'contain' => ['QueueJobs', 'QueueWorkers'],
        ]);

        $this->set(compact('queueLog'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add() {
        $queueLog = $this->QueueLogs->newEmptyEntity();
        if ($this->request->is('post')) {
            $queueLog = $this->QueueLogs->patchEntity($queueLog, $this->request->getData());
            if ($this->QueueLogs->save($queueLog)) {
                $this->Flash->success(__('The queue log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue log could not be saved. Please, try again.'));
        }
        $queueTasks = $this->QueueLogs->QueueJobs->find('list', ['limit' => 200]);
        $queueWorkers = $this->QueueLogs->QueueWorkers->find('list', ['limit' => 200]);
        $this->set(compact('queueLog', 'queueTasks', 'queueWorkers'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Queue Log id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null) {
        $queueLog = $this->QueueLogs->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $queueLog = $this->QueueLogs->patchEntity($queueLog, $this->request->getData());
            if ($this->QueueLogs->save($queueLog)) {
                $this->Flash->success(__('The queue log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue log could not be saved. Please, try again.'));
        }
        $queueTasks = $this->QueueLogs->QueueJobs->find('list', ['limit' => 200]);
        $queueWorkers = $this->QueueLogs->QueueWorkers->find('list', ['limit' => 200]);
        $this->set(compact('queueLog', 'queueTasks', 'queueWorkers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Queue Log id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $queueLog = $this->QueueLogs->get($id);
        if ($this->QueueLogs->delete($queueLog)) {
            $this->Flash->success(__('The queue log has been deleted.'));
        } else {
            $this->Flash->error(__('The queue log could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}
