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
