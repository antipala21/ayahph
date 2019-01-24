<?php
App::uses('AppController', 'Controller');
class LoginController extends AppController{
	public $uses = array(
		'Agency'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index() {
		// data submited
		if($this->Session->check('Auth.User')){
			// $this->redirect(array('action' => 'index'));
			return $this->redirect(Router::fullbaseUrl());
		}
		
		// if we get the post information, try to authenticate
		if ($this->request->is('post')) {

			$email = $this->request->data['Agency']['email'];
			$password = AuthComponent::password($this->request->data['Agency']['password']);
			$conditions = array(
				'email' => $email,
				'password' => $password
				// 'User.status !=' => 0
			);
			$data = $this->Agency->find('first',array('conditions'=>$conditions));

			if (isset($data['Agency']) && $data['Agency']['status'] != 9) {

				$agency = $data['Agency'];
				$agency['type'] = 'agency';

				$this->Auth->login($agency);
				// $this->setLoginLog();

				// $this->Session->setFlash(__('Welcome, '. $this->Auth->user('fname')));
				$this->redirect($this->Auth->redirectUrl());
			}
			elseif(isset($data['Agency']) && $data['Agency']['status'] == 0){
				$this->render('/Register/register_confirm');
			} else {
				$this->Session->setFlash(__('Invalid username or password'));
			}

		}
	}
}
