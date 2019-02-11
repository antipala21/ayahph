<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	public $uses = array(
		'Agency',
		'AgencyLegalDocument',
		'Payment'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			'logout',
			'checkEmail'
		);
	}

	public function index () {

		$agency = $this->Agency->find('first', array(
			'conditions' => array('Agency.id' => $this->Auth->user('id'))
		));

		if (!$agency) {
			return $this->redirect('/');
		}
		$this->set('agency', $agency['Agency']);
	}

	public function edit () {

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->Agency->clear();
			$this->Agency->read(array(
				'name',
				'description',
				'phone_number',
				'address'
			), $this->Auth->user('id'));
			$this->Agency->set($data['Agency']);
			if (!$this->Agency->save()) {
				$this->Session->setFlash('Update Fail', 'default', array(), 'updateFail');
			}
			$this->Session->setFlash('Update Success', 'default', array(), 'updateSuccess');
			return $this->redirect('/account');

		}

		$agency = $this->Agency->find('first', array(
			'conditions' => array('Agency.id' => $this->Auth->user('id'))
		));

		if (!$agency) {
			return $this->redirect('/');
		}
		$this->set('agency', $agency['Agency']);

	}

	public function updateRequirements () {
		$documents = $this->AgencyLegalDocument->find('all', array(
			'conditions' => array('AgencyLegalDocument.agency_id' => $this->Auth->user('id'))
		));
		$this->set('documents', $documents);
	}

	private function uploadAgreement ($permit = array(), $filename) {
		move_uploaded_file(
			$permit['tmp_name'], 
			'img/agency_permit/'. $filename
		);
	}

	public function legalDocumentsDelete () {
		$this->autoRender = false;
		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->AgencyLegalDocument->delete($data['id']);
			$filename = 'img/agency_permit/' . $data['filename'];
			unlink($filename);
			return true;
		}
	}

	public function checkEmail () {
		$this->autoRender = false;
		if ($this->request->is('ajax')) {
			$data = $this->request->data;
			$result['result'] = false;

			$check = $this->Agency->find('count', array(
				'conditions' => array('Agency.email' => $data['email'])
			));
			if ($check) {
				$result['result'] = true;
			}
			return json_encode($result);
		}
	}

	public function ajax_image_upload () {
		$this->layout = false;
		$this->autoRender = false;

		if ($this->request->is('ajax')) {

			$data = $this->request->data['profile-image'];

			list($type, $data) = explode(';', $data);
			list(, $data)      = explode(',', $data);
			$data = base64_decode($data);

			$fileName = $this->Auth->user('id') . '_' . 'profile' . '.jpg';

			file_put_contents('images/'. $fileName, $data);

			$this->Agency->clear();
			$this->Agency->read(null, $this->Auth->user('id'));
			$this->Agency->saveField('image_url', $fileName);
			$this->Session->write('Auth.User.image_url', $fileName);
			return true;
		}
	}

	public function payment () {
		$member = $this->Agency->find('count', array(
			'conditions' => array(
				'Agency.id' => $this->Auth->user('id'),
				'Agency.status' => 1
			)
		));
		if ($member) {
			return $this->redirect('/account');
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;

			// process payment to each supplier
			$result = Braintree_Transaction::sale([
				'amount' => $data['amount'],
				'orderId' => '123',
				'merchantAccountId' => 'ayahph',
				'paymentMethodNonce' => $data['payment_method_nonce'],
				'customer' => [
					'firstName' => 'Test name',
					'lastName' => 'Testlastname',
				],
				'options' => [
					'submitForSettlement' => true
				]
			]);

			// success payment
			if ($result->success == true) {

				$this->Agency->clear();
				$this->Agency->read(array('status'), $this->Auth->user('id'));
				$this->Agency->set(array('status'=> 1));
				$this->Agency->save();

				$pay_data = array(
					'Payment' => array(
						'payment_id' => $result->transaction->id,
						'agency_id' => $this->Auth->user('id'),
						// 'transaction_date' => $result->transaction->createdAt->date,
						'transaction_date' => date('Y-m-d H:i:s', strtotime("now")),
						'type' => $result->transaction->type,
						'customer_name' => $data['firstname'],
						'card_no' => $result->transaction->creditCard['last4'],
						'card_type' => $result->transaction->creditCard['cardType'],
						'amount' => $result->transaction->amount
					)
				);

				$this->Payment->create();
				$this->Payment->set($pay_data);
				$this->Payment->save();
			}
			return $this->redirect('/account');
		}

	}

	public function success_card () {
		$this->layout = false;
		$this->autoRender = false;
		if ($this->request->is('ajax')) {
			$this->Agency->clear();
			$this->Agency->read(array('status'), $this->Auth->user('id'));
			$this->Agency->set(array('status'=> 1));
			$this->Agency->save();
		}
		return true;
	}

	public function logout() {
		$this->Session->destroy();
		$this->redirect($this->Auth->logout());
	}

}
