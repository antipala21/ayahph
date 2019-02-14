<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	// https://www.formget.com/upload-multiple-images-using-php-and-jquery/

	public $uses = array('User');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow(
			'index'
		);
	}

	public function index () {
		$this->autoRender = false;
		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$this->User->virtualFields['total_transaction'] = "SELECT COUNT(*) FROM `transactions` WHERE `user_id` = `User`.`id`";
		$user = $this->User->find('first', array(
			'conditions' => array('User.id' => $data['user_id'])
		));

		$response = array();

		if ($user) {
			$response = $user['User'];
		}
		return json_encode($response);
	}

	public function updateAccount () {
		$this->autoRender = false;
		if (!isset($this->request->data['params'])) {
			$response['success'] = false;
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$user_id = isset($data['user_id']) ? $data['user_id'] : NULL;
		if (!$user_id) {
			$response['success'] = false
			return json_encode($response);
		}

		$this->User->clear();
		$this->User->read(null, $user_id);
		$this->User->set($data);
		if ($this->User->save()) {
			$response['success'] = true;
		}
		return json_encode($response);
	}

}
