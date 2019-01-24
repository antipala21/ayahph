<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array('NurseMaid');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('index');
	}

	public function index(){

// 		$then = DateTime::createFromFormat("Y/m/d", "1983/12/16");
// $diff = $then->diff(new DateTime());
// echo $diff->format("%y year %m month %d day\n");
// exit;

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

}
