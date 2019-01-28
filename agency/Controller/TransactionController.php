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

		$transaction = $this->Transaction->find('first', array(
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
				'Transaction.id' => $transaction_id,
				'Transaction.status' => 0
			)
		));

		if (!$transaction) {
			return $this->redirect('/transaction');
		}
		$this->set('transaction', $transaction);
	}

	public function transactionUpdate () {
		$this->autoRender = false;
		if ($this->request->is('post')) {
			$data = $this->request->data;
			$status = 0;
			if (isset($data['value_transaction']) && $data['value_transaction'] === 'Accept') {
				$status = 1;
			} else {
				$status = 9;
			}

			$this->Transaction->clear();
			$this->Transaction->read(array('status'), $data['Transaction']['id']);
			$this->Transaction->set(array('status' => $status));
			if ($this->Transaction->save()) {
				return $this->redirect('/schedules');
			}
		}
	}


}
