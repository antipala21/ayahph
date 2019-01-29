<?php
App::uses('AppController', 'Controller');
class RegisterController extends AppController {

	public $uses = array('User','Agency');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index', 'token');
	}

	public function index () {

		// $gateway = new Braintree_Gateway([
		//   'environment' => 'sandbox',
		//   'merchantId' => 'tnnc2y3sq3ctj5cb',
		//   'publicKey' => '9pzgf9x7z4g3hmz8',
		//   'privateKey' => 'ee008d3d62a3f086dcb655424a3929d0'
		// ]);

		// echo($clientToken = $gateway->clientToken()->generate());

		// /$this->Session->setFlash('Success', 'default', array(), 'success');
		// $this->Session->setFlash(__('Account Updated Successfully'));

		// $data = $this->Agency->find('all', array(
		// 	'fields' => array('id', 'representative_name'),
		// 	'conditions' => array('representative_name'=> 'asdf')
		// ));

		// mytools::display($data);s
		// exit;


		// exit;
		if ($this->request->is('post')) {
			$data = $this->request->data;

			// mytools::display($data);

			$this->User->validate = false;
			$this->User->create();
			$this->User->set($data);
			$save = $this->User->save();
			if ($save) {

				// $user = $save['User'];
				// $user['type'] = 'user';

				// $this->Auth->login($user);

				// $this->Session->setFlash( __('Success'), 'default', array(), 'success');
				// return $this->redirect('/');

				$this->Session->write('user_id', $save['User']['id']);
				return $this->redirect('/account/requirements');

			}
		}
	}

	public function token() {
		$this->autoRender = false;
		// echo 'shit';
		// echo json_encode(Braintree_ClientToken::generate());
	}

	

	private function uploadAgreement ($permit = array(), $filename) {
		move_uploaded_file(
			$permit['tmp_name'], 
			'img/agency_permit/'. $filename
		);
	}

}
