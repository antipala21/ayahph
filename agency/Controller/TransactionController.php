<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
class TransactionController extends AppController {

	public $uses = array(
		'NurseMaid',
		'Transaction',
		'Agency',
		'User'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('token'));
	}

	public function index() {
		$transactions = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'User.id',
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
			$this->Transaction->read(null, $data['Transaction']['id']);
			$this->Transaction->set(array('status' => $status));
			$save = $this->Transaction->save();
			if ($save) {

				if ($status == 1) {
					$user_detail = $this->User->find('first', array(
						'fields' => array(
							'User.display_name',
							'User.email'
						),
						'conditions' => array('User.id' => $save['Transaction']['user_id'])
					));

					$nurse_maid_detail = $this->NurseMaid->find('first', array(
						'fields' => array(
							'NurseMaid.first_name',
							'NurseMaid.phone_number'
						),
						'conditions' => array('NurseMaid.id' => $save['Transaction']['nurse_maid_id'])
					));

					$Email = new CakeEmail();
					$Email->template('email_accepted_hire', 'email_accepted_hire')
						->emailFormat('html')
						->to($user_detail['User']['email'])
						->subject('Request Hire Accepted')
						->viewVars(
							array(
								'agency_name' => $this->Auth->user('name'),
								'agency_address' => $this->Auth->user('address'),
								'agency_phone' => $this->Auth->user('phone_number'),
								'client_name' => $user_detail['User']['display_name'],
								'nurse_maid_name' => $nurse_maid_detail['NurseMaid']['first_name'],
								'nurse_maid_phone' => $nurse_maid_detail['NurseMaid']['phone_number'],
								'transaction_start' => date("F j, Y, g:i a", strtotime($save['Transaction']['transaction_start'])),
								'transaction_end' => date("F j, Y, g:i a", strtotime($save['Transaction']['transaction_end'])),
								'email' => $user_detail['User']['email'],
								'base_url' => 'ayahph.localhost/user/',
								'transaction_id' => $save['Transaction']['id']
							)
						);
					$Email->send();
				}

				return $this->redirect('/schedules');
			}
		}
	}

	public function token() {
		$this->autoRender = false;
		echo json_encode(Braintree_ClientToken::generate());
	}


}
