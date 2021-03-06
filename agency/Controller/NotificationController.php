<?php
App::uses('AppController', 'Controller');
class NotificationController extends AppController {

	public $uses = array(
		'Agency',
		'AgencyLegalDocument',
		'Transaction'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index () {

	}

	public function hire_request () {
		$this->autoRender = false;
		if ($this->request->is('ajax')) {
			$hire_request = $this->Transaction->find('count', array(
				'conditions' => array(
					'Transaction.status' => 0,
					'Transaction.agency_id' => $this->Auth->user('id'),
					// 'Transaction.type' => 2 // unpaid bidding winner
				)
			));
			return json_encode(array('count' => $hire_request));
		}
	}
}
