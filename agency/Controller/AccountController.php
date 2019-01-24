<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	public $uses = array(
		'Agency',
		'AgencyLegalDocument'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('index');
	}

	public function index () {

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

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

}
