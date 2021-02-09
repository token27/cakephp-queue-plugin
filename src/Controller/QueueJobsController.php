<?php

declare(strict_types=1);

namespace Queue\Controller;

use Queue\Controller\QueueController;

/**
 * QueueJobs Controller
 *
 * @property \Queue\Model\Table\QueueJobsTable $QueueJobs
 * @method \Queue\Model\Entity\QueueJob[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class QueueJobsController extends QueueController {

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {
        $this->paginate = [
            'contain' => ['QueueWorkers'],
        ];
        $queueJobs = $this->paginate($this->QueueJobs);

        $this->set(compact('queueJobs'));
    }

    /**
     * View method
     *
     * @param string|null $id Queue Job id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {
        $queueJob = $this->QueueJobs->get($id, [
            'contain' => ['QueueWorkers', 'QueueLogs'],
        ]);

        $this->set(compact('queueJob'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add() {
        $queueJob = $this->QueueJobs->newEmptyEntity();
        if ($this->request->is('post')) {
            $queueJob = $this->QueueJobs->patchEntity($queueJob, $this->request->getData());
            if ($this->QueueJobs->save($queueJob)) {
                $this->Flash->success(__('The queue task has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue task could not be saved. Please, try again.'));
        }
        $queueWorkers = $this->QueueJobs->QueueWorkers->find('list', ['limit' => 200]);
        $this->set(compact('queueJob', 'queueWorkers'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Queue Job id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null) {
        $queueJob = $this->QueueJobs->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $queueJob = $this->QueueJobs->patchEntity($queueJob, $this->request->getData());
            if ($this->QueueJobs->save($queueJob)) {
                $this->Flash->success(__('The queue task has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue task could not be saved. Please, try again.'));
        }
        $queueWorkers = $this->QueueJobs->QueueWorkers->find('list', ['limit' => 200]);
        $this->set(compact('queueJob', 'queueWorkers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Queue Job id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $queueJob = $this->QueueJobs->get($id);
        if ($this->QueueJobs->delete($queueJob)) {
            $this->Flash->success(__('The queue task has been deleted.'));
        } else {
            $this->Flash->error(__('The queue task could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}
