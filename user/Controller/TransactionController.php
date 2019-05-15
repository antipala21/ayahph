<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
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
				$save = $this->Transaction->save();
				if ($save) {
					$result['sucess'] = true;

					// make nursemaid not available
					$this->NurseMaid->clear();
					$this->NurseMaid->read(array('status'), $data['nurse_maid_id']);
					$this->NurseMaid->set(array('status' => 0));
					$this->NurseMaid->save();

					$agency_detail = $this->Agency->find('first', array(
						'fields' => array(
							'Agency.name',
							'Agency.email'
						),
						'conditions' => array('Agency.id' => $data['agency_id'])
					));

					$nurse_maid_detail = $this->NurseMaid->find('first', array(
						'fields' => array('NurseMaid.first_name'),
						'conditions' => array('NurseMaid.id' => $data['nurse_maid_id'])
					));

					$Email = new CakeEmail();
					$Email->template('email_request_hire', 'email_request_hire')
						->emailFormat('html')
						->to($agency_detail['Agency']['email'])
						->subject('Request Hire')
						->viewVars(
							array(
								'agency_name' => $agency_detail['Agency']['name'],
								'client_name' => $this->Auth->user('display_name'),
								'nurse_maid' => $nurse_maid_detail['NurseMaid']['first_name'],
								'address' => $data['user_address'],
								'phone_number' => $data['user_phone_number'],
								'transaction_start' => date("F j, Y, g:i a", strtotime($data['transaction_start'])),
								'transaction_end' => date("F j, Y, g:i a", strtotime($data['transaction_end'])),
								'email' => $agency_detail['Agency']['email'],
								'base_url' => 'ayahph.localhost/',
								'transaction_id' => $save['Transaction']['id']
							)
						);
					$Email->send();
				}
			}

			return json_encode($result);

		}
		
	}

}
