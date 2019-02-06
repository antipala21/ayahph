<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array('NurseMaid');

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){
	$nursemaids = $this->NurseMaid->find('all', array(
			'conditions' => array('NurseMaid.agency_id' => $this->Auth->user('id'))
		));

		$this->set('nursemaids', $nursemaids);
	}

	public function add () {
		if ($this->request->is('post')) {
			$data = $this->request->data;

			$data['NurseMaid']['agency_id'] = $this->Auth->user('id');

			$this->NurseMaid->set($data);

			if ($this->NurseMaid->save()) {
				$this->Session->setFlash('Adding NurseMaid Success', 'default', array(), 'nurse-maid-add');
				return $this->redirect('/nursemaid');
			}

			$this->Session->setFlash('Adding NurseMaid Fail', 'default', array(), 'nurse-maid-add-error');

		}
	}

	public function detail () {

		$nursemaid_id = isset($this->params['nursemaid_id']) ? $this->params['nursemaid_id'] : null;

		$nursemaid = $this->NurseMaid->find('first', array(
			'conditions' => array('NurseMaid.id' => $nursemaid_id)
		));

		if (!$nursemaid) {
			return $this->redirect('/nursemaid');
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;

			if (isset($data['value_transaction']) && $data['value_transaction'] == 'Delete') {
				$this->NurseMaid->delete($data['NurseMaid']['id']);
				return $this->redirect('/nursemaid');
			} else {
				$new_status = isset($data['value_transaction']) && $data['value_transaction'] == 'Active' ? 1 : 0;
				$this->log('[new_status] ' . $new_status, 'debug');

				$this->NurseMaid->clear();
				$this->NurseMaid->read(array('status'), $data['NurseMaid']['id']);
				$this->NurseMaid->set(array('status' => $new_status)); // on hire , not active
				$this->NurseMaid->save();

				return $this->redirect('/nursemaid/detail/' . $nursemaid_id);
			}
		}

		$this->set('nurse_maid', $nursemaid['NurseMaid']);
	}

	public function edit () {
		$nursemaid_id = isset($this->params['nursemaid_id']) ? $this->params['nursemaid_id'] : null;

		$nursemaid = $this->NurseMaid->find('first', array(
			'conditions' => array('NurseMaid.id' => $nursemaid_id)
		));

		if (!$nursemaid) {
			return $this->redirect('/nursemaid');
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->NurseMaid->clear();
			$this->NurseMaid->read(null, $data['NurseMaid']['id']);
			$this->NurseMaid->set($data);
			if ($this->NurseMaid->save()) {
				$this->Session->setFlash('NurseMaid Updated', 'default', array(), 'nurse-maid-edit');
			}
			return $this->redirect('/nursemaid');
		}

		$this->set('nurse_maid', $nursemaid['NurseMaid']);
	}

}
