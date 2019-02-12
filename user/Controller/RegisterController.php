<?php
App::uses('AppController', 'Controller');
class RegisterController extends AppController {

	public $uses = array(
		'User',
		'Agency',
		'Lungsod'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index () {

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$data['User']['address_key'] = strtolower(str_replace(array(' ', '-', '/'), '_', $data['User']['address']));

			$this->User->validate = false;
			$this->User->create();
			$this->User->set($data);
			$save = $this->User->save();
			if ($save) {
				$this->Session->write('user_id', $save['User']['id']);
				$this->Session->write('display_name', $save['User']['display_name']);
				return $this->redirect('/account/requirements');

			}
		}

		$address = $this->Lungsod->find('list', array(
			'fields' => array(
				'Lungsod.name',
				'Lungsod.name'
			)
		));
		$this->set('address', $address);
	}

	private function uploadAgreement ($permit = array(), $filename) {
		move_uploaded_file(
			$permit['tmp_name'], 
			'img/agency_permit/'. $filename
		);
	}

}
