<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	public $uses = array(
		'Agency',
		'AgencyLegalDocument'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			'logout',
			'checkEmail'
		);
	}

	public function index () {

		header("Pragma-directive: no-cache");
		header("Cache-directive: no-cache");
		header("Cache-control: no-cache");
		header("Pragma: no-cache");
		header("Expires: 0");

		$agency = $this->Agency->find('first', array(
			'conditions' => array('Agency.id' => $this->Auth->user('id'))
		));

		// myTools::display($this->Auth->user('id'));
		// var_dump($agency);
		// exit;

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
			// 'fields' => array('Agency.business_permit_url'),
			'conditions' => array('AgencyLegalDocument.agency_id' => $this->Auth->user('id'))
		));
		// myTools::display($agency);exit;
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
			return true;
		}
	}

	public function logout() {
		$this->Session->destroy();
		$this->redirect($this->Auth->logout());
	}

}
