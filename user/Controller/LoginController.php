<?php
App::uses('AppController', 'Controller');
class LoginController extends AppController{
	public $uses = array(
		'Agency',
		'User'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index() {

		if($this->Session->check('Auth.User')){
			return $this->redirect(Router::fullbaseUrl());
		}

		if ($this->request->is('post')) {

			$email = $this->request->data['User']['email'];
			$password = AuthComponent::password($this->request->data['User']['password']);
			$conditions = array(
				'email' => $email,
				'password' => $password
				// 'User.status !=' => 0
			);
			$data = $this->User->find('first',array('conditions'=>$conditions));

			if (isset($data['User']) && $data['User']['status'] != 9) {

				$user = $data['User'];
				$user['type'] = 'user';

				$this->Auth->login($user);

				// $this->Session->setFlash(__('Welcome, '. $this->Auth->user('fname')));
				$this->redirect($this->Auth->redirectUrl());
			}
			elseif(isset($data['User']) && $data['User']['status'] == 0){
				$this->render('/Register/register_confirm');
			} else {
				$this->Session->setFlash(__('Invalid username or password'));
			}

		}
	}
}
