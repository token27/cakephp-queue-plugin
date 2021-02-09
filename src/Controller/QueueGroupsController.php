<?php

declare(strict_types=1);

namespace Queue\Controller;

use Queue\Controller\QueueController;

/**
 * QueueGroups Controller
 *
 * @property \Queue\Model\Table\QueueGroupsTable $QueueGroups
 * @method \Queue\Model\Entity\QueueGroup[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class QueueGroupsController extends QueueController {

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {
        $queueGroups = $this->paginate($this->QueueGroups);

        $this->set(compact('queueGroups'));
    }

    /**
     * View method
     *
     * @param string|null $id Queue Group id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {
        $queueGroup = $this->QueueGroups->get($id, [
            'contain' => ['QueueJobs'],
        ]);

        $this->set(compact('queueGroup'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add() {
        $queueGroup = $this->QueueGroups->newEmptyEntity();
        if ($this->request->is('post')) {
            $queueGroup = $this->QueueGroups->patchEntity($queueGroup, $this->request->getData());
            if ($this->QueueGroups->save($queueGroup)) {
                $this->Flash->success(__('The queue group has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue group could not be saved. Please, try again.'));
        }
        $this->set(compact('queueGroup'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Queue Group id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null) {
        $queueGroup = $this->QueueGroups->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $queueGroup = $this->QueueGroups->patchEntity($queueGroup, $this->request->getData());
            if ($this->QueueGroups->save($queueGroup)) {
                $this->Flash->success(__('The queue group has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue group could not be saved. Please, try again.'));
        }
        $this->set(compact('queueGroup'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Queue Group id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $queueGroup = $this->QueueGroups->get($id);
        if ($this->QueueGroups->delete($queueGroup)) {
            $this->Flash->success(__('The queue group has been deleted.'));
        } else {
            $this->Flash->error(__('The queue group could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}
