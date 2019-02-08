<?php
App::uses('AppController', 'Controller');
class TransactionController extends AppController {

	public $uses = array(
		'Agency',
		'NurseMaid',
		'Transaction',
		'HireRequest'
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

			$transaction_time = explode(' - ', $data['transaction_time']);

			$transaction_start = $transaction_time[0];

			$transaction_end = $transaction_time[1];

			$data['transaction_start'] = $transaction_start;
			$data['transaction_end'] = $transaction_end;

			// check duplicate transactions
			$check = $this->Transaction->find('count', array(
				'conditions' => array(
					'Transaction.user_id' => $this->Auth->user('id'),
					'Transaction.nurse_maid_id' => $data['nurse_maid_id'],
					'Transaction.status' => 0
				)
			));

			if (!$check) {
				$this->Transaction->create();
				$this->Transaction->set($data);
				if ($this->Transaction->save()) {
					$result['sucess'] = true;
				}
			}

			return json_encode($result);

		}
		
	}

}
