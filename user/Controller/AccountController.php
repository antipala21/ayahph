<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	// https://www.formget.com/upload-multiple-images-using-php-and-jquery/

	public $uses = array('User');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index () {

		$user = $this->User->find('first', array(
			'conditions' => array('User.id' => $this->Auth->user('id'))
		));

		if (!$user) {
			return $this->redirect('/');
		}
		$this->set('user', $user['User']);
	}

	public function edit () {

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->User->clear();
			$this->User->read(array(
				'name',
				'description',
				'phone_number',
				'address'
			), $this->Auth->user('id'));
			$this->User->set($data['User']);
			if (!$this->User->save()) {
				$this->Session->setFlash('Update Fail', 'default', array(), 'updateFail');
			}
			$this->Session->setFlash('Update Success', 'default', array(), 'updateSuccess');
			return $this->redirect('/account');

		}

		$user = $this->User->find('first', array(
			'conditions' => array('User.id' => $this->Auth->user('id'))
		));

		if (!$user) {
			exit;
			return $this->redirect('/');
		}
		$this->set('user', $user['User']);

	}

	public function updateRequirements () {

		header("Pragma-directive: no-cache");
		header("Cache-directive: no-cache");
		header("Cache-control: no-cache");
		header("Pragma: no-cache");
		header("Expires: 0");

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$filename = $this->Auth->user('id') . '_' . 'business_permit_' . str_replace(' ', '_', $this->Auth->user('display_name')) . '.jpg';

			$this->uploadAgreement($data['User']['business_permit_url'], $filename);

			$this->User->clear();
			$this->User->read(array('business_permit_url'), $this->Auth->user('id'));
			$this->User->set(array('business_permit_url' => $filename));
			$this->User->save();
		}

		$user = $this->User->find('first', array(
			'fields' => array('User.business_permit_url'),
			'conditions' => array('User.id' => $this->Auth->user('id'))
		));
		// myTools::display($user);exit;
		$this->set('user', $user);

	}

	private function uploadAgreement ($permit = array(), $filename) {
		move_uploaded_file(
			$permit['tmp_name'], 
			'img/business_permits/'. $filename
		);
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

}
