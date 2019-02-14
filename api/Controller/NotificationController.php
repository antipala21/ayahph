<?php
App::uses('AppController', 'Controller');
App::uses('User', 'Model/Base');
class NotificationController extends AppController {
	public $uses = array('Transaction');

	public function beforeFilter() {

		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
		$this->autoRender = false;
		$response = array();

		if (!isset($this->request->data['params'])) {
			$response['success'] = false
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$user_id = isset($data['user_id']) ? $data['user_id'] : null;

		if (!$user_id) {
			$response['success'] = false
			return json_encode($response);
		}

		$hire_accept = $this->Transaction->find('count', array(
			'conditions' => array(
				'Transaction.status' => 1,
				'Transaction.user_id' => $user_id
			)
		));
		$response['count'] = $hire_accept;
		return json_encode($response);
	}

}