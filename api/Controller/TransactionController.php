<?php
App::uses('AppController', 'Controller');
App::uses('User', 'Model/Base');
App::uses('CakeEmail', 'Network/Email');
class TransactionController extends AppController {
	public $uses = array(
		'User',
		'Transaction',
		'Agency',
		'NurseMaid',
		'NurseMaidRating'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array(
			'index',
			'add_rate'
		));
	}

	public function index() {
		$this->autoRender = false;

		if (!isset($this->request->data['params'])) {
			$response['success'] = false;
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;
		$response['success'] = false;
		$response['duplicate'] = false;

		if (!$data) {
			$response['error']['message'] = 'Invalid request';
		}
		else if (!isset($data['user_id']) || empty($data['user_id'])) {
			$response['error']['message'] = 'Auth Error';
		}
		else if (!isset($data['agency_id']) || empty($data['agency_id']))  {
			$response['error']['message'] = 'Agency ID is required';
		}
		else if (!isset($data['nurse_maid_id']) || empty($data['nurse_maid_id']))  {
			$response['error']['message'] = 'Nursemaid ID is required';
		}
		else if (!isset($data['transaction_time']) || empty($data['transaction_time']))  {
			$response['error']['message'] = 'Time is required';
		}
		else if (!isset($data['user_address']) || empty($data['user_address']))  {
			$response['error']['message'] = 'Client address is required';
		}
		else if (!isset($data['user_phone_number']) || empty($data['user_phone_number']))  {
			$response['error']['message'] = 'Phone number is required';
		} else {

			// $transaction_time = explode(' - ', $data['transaction_time']);
			$transaction_start = $data['transaction_time'];
			$transaction_end = $data['transaction_time'];

			$data['transaction_start'] = $transaction_start;
			$data['transaction_end'] = $transaction_end;

			// check duplicate transactions
			$check = $this->Transaction->find('count', array(
				'conditions' => array(
					'Transaction.user_id' => $data['user_id'],
					'Transaction.nurse_maid_id' => $data['nurse_maid_id'],
					'Transaction.status' => 0
				)
			));

			if (!$check) {
				$this->Transaction->create();
				$this->Transaction->set($data);
				$save = $this->Transaction->save();
				if ($save) {
					$response['success'] = true;

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

					try {
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
					} catch (Exception $e) {
						// $this->log('[Exception Email] ' . json_encode($e), 'debug');
					}
				}
			} else {
				$response['duplicate'] = true;
			}
		}
		return json_encode($response);
	}

	public function add_rate () {

		$this->autoRender = false;

		if (!isset($this->request->data['params'])) {
			$response['success'] = false;
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$response['success'] = false;

		if (!$data) {
			$response['error']['message'] = 'Invalid request';
		}
		else if (!isset($data['user_id']) || empty($data['user_id'])) {
			$response['error']['message'] = 'Auth Error';
		}
		else if (!isset($data['transaction_id']) || empty($data['transaction_id'])) {
			$response['error']['message'] = 'Transaction ID Error';
		}
		else if (!isset($data['agency_id']) || empty($data['agency_id']))  {
			$response['error']['message'] = 'Agency ID is required';
		}
		else if (!isset($data['nurse_maid_id']) || empty($data['nurse_maid_id']))  {
			$response['error']['message'] = 'Agency ID is required';
		} else {

			$rate = isset($data['rate']) ? $data['rate'] : 5;
			$data['rate'] = $rate;
			$data['user_id'] = $data['user_id'];
			$this->NurseMaidRating->create();
			$this->NurseMaidRating->set($data);
			if ($this->NurseMaidRating->save()) {
				$this->Transaction->clear();
				$this->Transaction->read(array('status'), $data['transaction_id']);
				$this->Transaction->set(array('status' => 3));
				if ($this->Transaction->save()) {
					$response['success'] = true;
					$response['result'] = "Success";
				}
			}
		}
		return json_encode($response);
	}


}