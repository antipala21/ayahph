<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	// https://www.formget.com/upload-multiple-images-using-php-and-jquery/

	public $uses = array('User');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index', 'updateRequirements');
	}

	public function index () {

		$this->User->virtualFields['total_transaction'] = "SELECT COUNT(*) FROM `transactions` WHERE `user_id` = `User`.`id`";
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

		if ($this->Auth->user('id')) {
			$user_id = $this->Auth->user('id');
			$display_name = $this->Auth->user('display_name');
		} elseif($this->Session->read('user_id')) {
			$user_id = $this->Session->read('user_id');

			$data = $this->User->find('first',array('conditions'=>array('User.id' => $user_id)));

			$user = $data['User'];
			$user['type'] = 'user';

			$this->Auth->login($user);

		} else {
			return $this->redirect('/logout');
		}
		$this->set('user_id', $user_id);

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$filename = $this->Auth->user('id') . '_' . strtotime('now') . str_replace(' ', '_', $this->Auth->user('display_name')) . '.jpg';

			$this->uploadAgreement($data['User']['valid_id_url'], $filename);

			$this->User->clear();
			$this->User->read(array('valid_id_url'), $this->Auth->user('id'));
			$this->User->set(array('valid_id_url' => $filename));
			$this->User->save();
		}

		$user = $this->User->find('first', array(
			'fields' => array('User.valid_id_url'),
			'conditions' => array('User.id' => $this->Auth->user('id'))
		));
		// myTools::display($user);exit;
		$this->set('user', $user);

	}

	private function uploadAgreement ($permit = array(), $filename) {
		move_uploaded_file(
			$permit['tmp_name'], 
			'img/user_ids/'. $filename
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
