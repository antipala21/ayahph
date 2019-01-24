<?php
App::uses('AppController', 'Controller');
class TransactionController extends AppController {

	public $uses = array(
		'Agency',
		'NurseMaid',
		'Transaction'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('saveRequest');
	}

	public function index () {

	
	}

	public function saveRequest () {
		$this->autoRender = false;

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$data['user_id'] = $this->Auth->user('id');

			$result['sucess'] = false;

			$this->Transaction->set($data);
			if ($this->Transaction->save()) {
				$result['sucess'] = true;
			}

			return json_encode($result);

		}
		
	}

}
