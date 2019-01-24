<?php
App::uses('AppController', 'Controller');
class LoginController extends AppController {

	public $uses = array(
		'Agency',
		'User',
		'Admin'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){
		if($this->Session->check('Auth.User')){
			return $this->redirect(Router::fullbaseUrl());
		}

		if ($this->request->is('post')) {

			$user_id = $this->request->data['Admin']['user_id'];
			$password = AuthComponent::password($this->request->data['Admin']['password']);
			$conditions = array(
				'user_id' => $user_id,
				'password' => $password
				// 'User.status !=' => 0
			);
			$data = $this->Admin->find('first',array('conditions'=>$conditions));

			if (isset($data['Admin'])) {

				$user = $data['Admin'];
				$user['type'] = 'admin';

				$this->Auth->login($user);
				// $this->setLoginLog();

				// $this->Session->setFlash(__('Welcome, '. $this->Auth->user('fname')));
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Session->setFlash(__('Invalid username or password'));
			}

		}
	}

}
