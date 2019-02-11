<?php
App::uses('AppController', 'Controller');
class PaymentController extends AppController {

	public $uses = array(
		'Payment'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){

		$payments = $this->Payment->find('all', array(
			'fields' => array(
				'Payment.*',
				'Agency.id',
				'Agency.name'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = Payment.agency_id'
				)
			)
		));
		$this->set('payments', $payments);
	}
}
