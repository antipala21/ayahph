<?php
App::uses('AppController', 'Controller');
class TransactionController extends AppController {

	public $uses = array(
		'NurseMaid',
		'Transaction'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		
	}

	public function index() {
		
		$transactions = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'User.display_name'
			),
			'joins' => array(
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => 'User.id = Transaction.user_id'
				)
			),
			'conditions' => array(
				'Transaction.status' => 0,
				'Transaction.agency_id' => $this->Auth->user('id')
			),
			'order' => 'Transaction.id DESC'
		));

		$this->set('transactions', $transactions);

	}

	public function detail () {
		$transaction_id = isset($this->params['transaction_id']) ? $this->params['transaction_id'] : null;
	}


}
