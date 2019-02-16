<?php
App::uses('AppController', 'Controller');
class NotificationController extends AppController {

	public $uses = array(
		'Transaction'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index () {
		$this->autoRender = false;

		if (!isset($this->request->data['params'])) {
			$response['success'] = false;
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$this->log('[NOTIF DATA] ' . json_encode($data), 'debug');

		$hire_accept = $this->Transaction->find('count', array(
			'conditions' => array(
				'Transaction.status' => 1,
				'Transaction.user_id' => $data['user_id']
			)
		));
		return json_encode(array('count' => $hire_accept));
	}
}
