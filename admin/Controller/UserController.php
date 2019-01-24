<?php
App::uses('AppController', 'Controller');
class UserController extends AppController {

	public $uses = array(
		'Agency',
		'User',
		'Admin'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){

		$users = $this->User->find('all');
		$this->set('users', $users);

	}

	public function detail () {
		$id = isset($this->params['id']) ? $this->params['id'] : null;

		$user = $this->User->find('first', array(
			'conditions' => array('User.id' => $id)
		));

		// myTools::display($user);exit;
		$this->set('user', $user);
	}

}
