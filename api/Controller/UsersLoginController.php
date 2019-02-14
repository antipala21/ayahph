<?php
App::uses('AppController', 'Controller');
App::uses('User', 'Model/Base');
class UsersLoginController extends AppController {
	public $uses = array('User');

	public function beforeFilter() {

		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
		$this->autoRender = false;

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$data['users_email'] = str_replace(' ', '+', $data['users_email']);

		$this->log('[request_data] ' . json_encode($this->request->data['params']), 'debug');
		$this->log('[username] ' . $data['users_email'], 'debug');
		$this->log('[password] ' . $data['password'], 'debug');

		if (!$data) {
			$response['error']['id'] = 'Invalid request';
			$response['error']['message'] = 'Invalid request';
		} else if (!isset($data['users_email']) || empty($data['users_email'])) {
			$response['error']['id'] = 'Email is required';
			$response['error']['message'] = 'users_email is required';
		} else if (!isset($data['password']) || empty($data['password']))  {
			$response['error']['id'] = 'Password is required';
			$response['error']['message'] = 'password is required';
		} else {
			$email = $data['users_email'];
			$password =  AuthComponent::password($data['password']);
			$conditions = array(
				'AND' => array(
					'User.email' => $email,
					'User.password' => $password,
					'status !=' => 9
				)
			);
			$User = $this->User->find('first',array(
				'conditions' => $conditions,
				'fields' => array(
					'id',
					'status',
					'display_name'
				)
			));
			$this->log('[User] ' . json_encode($User), 'debug');
			if (isset($User['User'])) {
				$user_display_name = $User['User']['display_name'];
				if ($User['User']['status'] == 0) {
					$response['id'] = $User['User']['id'];
					$response['user_display_name'] = $user_display_name;
				} else {
					$response['id'] = $User['User']['id'];
					$response['user_display_name'] = $user_display_name;
				}
			} else {
				$response['error']['id'] = 'Error email and password';
				$response['error']['message'] = 'Error email and password';
			}
		}
		return json_encode($response);
	}

}