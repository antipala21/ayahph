<?php
App::uses('AppController', 'Controller');
class UserController extends AppController {

	public $uses = array(
		'User'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index() {
		$id = isset($this->params['id']) ? $this->params['id'] : null;

		$this->User->virtualFields['total_transaction'] = "SELECT COUNT(*) FROM `transactions` WHERE `user_id` = `User`.`id`";
		$user = $this->User->find('first', array(
			'conditions' => array('User.id' => $id)
		));
		if (!$user) {
			return $this->redirect('/');
		}
		$this->set('user', $user['User']);
	}

}
