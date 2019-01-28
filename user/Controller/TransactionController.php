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
			$this->log('[transaction_time] ' . json_encode($transaction_time), 'debug');

			$transaction_start = $transaction_time[0];

			$transaction_end = $transaction_time[1];

			$data['transaction_start'] = $transaction_start;
			$data['transaction_end'] = $transaction_end;

			$this->log('[data] ' . json_encode($data), 'debug');

			$this->Transaction->set($data);
			if ($this->Transaction->save()) {
				$result['sucess'] = true;
			}

			return json_encode($result);

		}
		
	}

}
