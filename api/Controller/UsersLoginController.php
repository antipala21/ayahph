<?php
/****************************************
*																				*
*  Users Login for API                  *
*  Author: John Mart Belamide						*
*  September 2015												*
*																				*
****************************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('User', 'Model/Base');
class UsersLoginController extends AppController {
	public $uses = array('User','UsersLoginHistory');

	public function beforeFilter() {

		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
		$this->autoRender = false;

		$data = json_decode($this->request->input(),true);

		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset($data['users_email']) || empty($data['users_email'])) {
			$response['error']['id'] = Configure::read('error.users_email_is_required');
			$response['error']['message'] = __('users_email is required');
		} else if (!isset($data['users_password']) || empty($data['users_password']))  {
			$response['error']['id'] = Configure::read('error.users_password_is_required');
			$response['error']['message'] = __('users_password is required');
		} else {
			$email = $data['users_email'];
			$password =  AuthComponent::password($data['users_password']);
			$conditions = array(
				'AND' => array(
					'User.email' => $email,
					'User.password' => $password,
					'status !=' => 9
				)
			);
			$User = $this->User->useReplica()->find('first',array(
				'conditions' => $conditions,
				'fields' => array(
					'id',
					'status',
					'api_token'
					),
				'recursive' => -1
				)
			);
			if (isset($User['User'])) {
				if ($User['User']['status'] == 0) {
					$api_token = empty($User['User']['api_token']) ? $this->generateAPIToken($this->request->clientIp()) : $User['User']['api_token'];
						$this->User->read(array('api_token'), $User['User']['id']);
						$this->User->set(array('api_token' => $api_token));
						$this->User->save();
					$response['users_api_token'] = $api_token;
					$response['users_status'] = 'temporary_user';
				} else {
					$api_token = empty($User['User']['api_token']) ? $this->generateAPIToken($this->request->clientIp()) : $User['User']['api_token'];

					if ( empty($User['User']['api_token']) ) { // update if token is empty
						$this->User->read(array('api_token'), $User['User']['id']);
						$this->User->validate = false;
						$this->User->set(array('api_token' => $api_token));
						$this->User->save();
					}

					$this->setLoginLog($User['User']['id']);

					//log ip NC-1694 ADDED IP LOGS.
					IpLogTable::add($User['User']['id'],2);

					$response['users_api_token'] = $api_token;
				}
			} else {
				$accountTemporary = $this->User->find('first',array(
					'conditions'=>array(
						'email' => $email,
						'User.status =' => 0
					),
					'fields' => array(
						'User.email',
						'User.api_token'
					)
				));
				if(!empty($accountTemporary)) {
					$response['users_api_token'] = $accountTemporary['User']['api_token'];
					$response['users_status'] = 'incorrect_password_temporary_user';
				} else {
					$response['error']['id'] = Configure::read('error.email_and_password_is_incorrect');
					$response['error']['message'] = __('The combination of email and password you have entered is incorrect.');
				}
			}
		}
		return json_encode($response);
	}

	/**
	* add Log History
	* @param $id
	*/
	private function setLoginLog($id){

		$this->User->validate = array();
		$this->User->read(null,$id);
		$this->User->set('last_login_time',myTools::myDate());
		$this->User->save();

		$this->UsersLoginHistory->read(null);
		$this->UsersLoginHistory->set('user_id',$id);
		$this->UsersLoginHistory->set('login_time',myTools::myDate());
		$this->UsersLoginHistory->save();
	}

	/**
  * Generate User Token
  * @param string $ip users ip
  * @return string token
  */  
	 public function generateAPIToken($ip){
	  return md5(time() . ip2long($ip) . uniqid()); 
	 }

}