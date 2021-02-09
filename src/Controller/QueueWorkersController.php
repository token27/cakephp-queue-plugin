<?php

declare(strict_types=1);

namespace Token27\Queue\Controller;

use Token27\Queue\Controller\QueueController;

/**
 * QueueWorkers Controller
 *
 * @property \Queue\Model\Table\QueueWorkersTable $QueueWorkers
 * @method \Queue\Model\Entity\QueueWorker[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class QueueWorkersController extends QueueController {

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {
        $queueWorkers = $this->paginate($this->QueueWorkers);

        $this->set(compact('queueWorkers'));
    }

    /**
     * View method
     *
     * @param string|null $id Queue Worker id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {
        $queueWorker = $this->QueueWorkers->get($id, [
            'contain' => ['QueueLogs', 'QueueJobs'],
        ]);

        $this->set(compact('queueWorker'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add() {
        $queueWorker = $this->QueueWorkers->newEmptyEntity();
        if ($this->request->is('post')) {
            $queueWorker = $this->QueueWorkers->patchEntity($queueWorker, $this->request->getData());
            if ($this->QueueWorkers->save($queueWorker)) {
                $this->Flash->success(__('The queue worker has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue worker could not be saved. Please, try again.'));
        }
        $this->set(compact('queueWorker'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Queue Worker id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null) {
        $queueWorker = $this->QueueWorkers->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $queueWorker = $this->QueueWorkers->patchEntity($queueWorker, $this->request->getData());
            if ($this->QueueWorkers->save($queueWorker)) {
                $this->Flash->success(__('The queue worker has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue worker could not be saved. Please, try again.'));
        }
        $this->set(compact('queueWorker'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Queue Worker id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $queueWorker = $this->QueueWorkers->get($id);
        if ($this->QueueWorkers->delete($queueWorker)) {
            $this->Flash->success(__('The queue worker has been deleted.'));
        } else {
            $this->Flash->error(__('The queue worker could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}
