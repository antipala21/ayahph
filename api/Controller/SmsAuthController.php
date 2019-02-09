<?php

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('VjNexmoApp','Vendor');
class SmsAuthController extends AppController {
	public $uses = array(
		'User',
		'LessonOnair',
		'DeviceToken',
		'PhoneVerifyLog',
		'PhoneVerifyCheckLog',
		'CountryCode'
	);
	public $error;
	public $isJSON;
	public $autoRender = false;
	public $autoLayout = false;
	public $apiCommon = null;
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index','check','inputPhoneNumber','isSmsAuthUser');

		// decode input
		$data = json_decode($this->request->input(),true);

		// set JSON
		$this->isJSON = false;
		$this->error = false;
		$this->apiCommon = new ApiCommonController();
		
		// if is JSON
		if (isset($data['return_json']) && $data['return_json'] == 1) {
			$this->isJSON = true;
		}
	}
		
	public function index(){
		// decode input
		$data = json_decode($this->request->input(),true);

		// set API common controller
		$userConditions = array();
		$content = "";
		$arrReturn = array(
			'return_json' => $this->isJSON,
			'error' => $this->error,
			'content' => ''
		);
		
		// check if data exists
		if (!$data) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.invalid_request');
			$arrReturn['content'] = "Error! invalid request!";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// get user conditions, if user ID
		if (isset($data['user_id']) && !empty($data['user_id'])) {
			$conditions['id'] = $data['user_id'];

		// else, use conditions
		} else if (isset($data['users_api_token'])) {
			
			// validate token
			$user = $this->apiCommon->validateToken($data['users_api_token']);

			// if has no user id
			if (isset($user['id']) === FALSE) {
				$this->error = true;
				$arrReturn['error'] = $this->error;
				$arrReturn['id'] = Configure::read('error.invalid_api_token');
				$arrReturn['content'] = "Invalid Api token";
				
				// return json || normal message
				return $this->returnJSONMessage($arrReturn);
			} else {
				// set condition
				$conditions['id'] = $user['id'];
			}
		
		// else return error
		} else {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.missing_mandatory_parameter_users_api_token_or_user_id');
			$arrReturn['content'] = "Your request is incomplete and missing the mandatory parameter: users_api_token or user_id";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// get user's phone number
		$this->User->openDBReplica();
		$usersPhoneNumber = $this->User->find('first', array(
			'conditions' => $conditions,
			'fields' => array('phone_number', 'country_code', 'id')
		));
		$this->User->closeDBReplica();
		
		// check phone number
		if (isset($usersPhoneNumber['User']['phone_number']) && !empty($usersPhoneNumber['User']['phone_number'])) {
			//    $phoneNumber = '81'.substr($usersPhoneNumber['User']['phone_number'],1,strlen($usersPhoneNumber['User']['phone_number'])-1);
			$nexmo = new VjNexmoApp();
			$result = $nexmo->requestVerifyApi($usersPhoneNumber['User']['phone_number'],$usersPhoneNumber['User']['country_code'], $usersPhoneNumber['User']['id'], 'NativeCamp.');
			
			// if result contains a string
			if (strlen(trim($result)) != 0) {
				// set error
				$this->error = true;
				$arrReturn['id'] = Configure::read('error.nexmo_error');
				$arrReturn['content'] = $result;
			} else {
				$this->error = false;
				$arrReturn['content'] = $this->isJSON ? array("smsAuth" => true, "message" => $result) : $result;
			}
		// else, return error
		} else {
			$this->error = true;
			$arrReturn['id'] = Configure::read('error.phone_number_is_empty');
			$arrReturn['content'] = 'phone number is empty!';
		}
		
		// set error
		$arrReturn['error'] = $this->error;
		
		// return json || normal message
		return $this->returnJSONMessage($arrReturn);
	}

	public function check(){
		$data = json_decode($this->request->input(),true);
		
		// $data = $this->request->query;
		$conditions = array();
		$arrReturn = array(
			'return_json' => $this->isJSON,
			'error' => $this->error,
			'content' => ''
		);
		
		// check if data exists
		if (!$data) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.invalid_request');
			$arrReturn['content'] = "Error! invalid request!";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// user_id exists
		if (isset($data['user_id']) && !empty($data['user_id'])) {
			$user['id'] = $data['user_id'];
			$conditions['user_id'] = $data['user_id'];
		
		// if users_api_token exists
		} else if (isset($data['users_api_token'])) {
			
			// validate token
			$user = $this->apiCommon->validateToken($data['users_api_token']);
			
			// if has no user id
			if (isset($user['id']) === FALSE) {
				$this->error = true;
				$arrReturn['error'] = $this->error;
				$arrReturn['id'] = Configure::read('error.invalid_api_token');
				$arrReturn['content'] = "Invalid Api token";
				
				// return json || normal message
				return $this->returnJSONMessage($arrReturn);
			} else {
				// set condition
				$conditions['user_id'] = $user['id'];
			}
		} else {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.missing_mandatory_parameter_users_api_token_or_user_id');
			$arrReturn['content'] = "Your request is incomplete and missing the mandatory parameter: users_api_token or user_id";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// verify log
		$this->PhoneVerifyLog->openDBReplica();
		$usersVerifyLog = $this->PhoneVerifyLog->find('first',array(
			'conditions' => $conditions,
			'status' => 0,
			'order' => 'created DESC'
		));
		$this->PhoneVerifyLog->closeDBReplica();
		
		// if has no data
		if (empty($usersVerifyLog)) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.this_user_is_not_verified');
			$arrReturn['content'] = "error this user is not verified";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// declare nexmo app
		$nexmo = new VjNexmoApp();
		$result = $nexmo->checkVerifyApi($usersVerifyLog['PhoneVerifyLog']['phone_number'], $user['id'],$usersVerifyLog['PhoneVerifyLog']['request_id'],$data['code']);

		// if result was a success
		if (trim($result) == '') {
			$this->log("[REFERRAL] SMSAuth check success. Trying referral bonus... ", "debug");
			
			// do referral bonus when sms auth is successful
			ClassRegistry::init('User')->addReferralBonus($user['id']);
			
			// show error message
			$this->error = false;
			$arrReturn['content'] = $this->isJSON ? array("checked" => true, "message" => $result) : $result;
			
			//NC-4452 - Post to slack user manager when SMS authentication is done from outside Japan
			//check user agent if not from jap then send.,
			$getUser = $this->User->find('first', array(
				'conditions' => array(
					'User.id' => $user['id']
				),
				'fields' => array(
					'User.country_code',
					'User.id',
					'User.nickname',
					'User.email'
				),
				'recursive' => -1
			));
			if($getUser && $getUser['User']['country_code'] != 'JP') {
				$user = $getUser['User'];
				$countryCode = $this->CountryCode->findByCode($user['country_code']);
				$userDetail = new UserTable($user);
				$this->mySlack = new mySlack();
				$this->mySlack->channel = myTools::checkChannel('#foreign-sms-auth');
				$this->mySlack->text = "```";
				$this->mySlack->text .= "SMS Auth from foreign\n";
				$this->mySlack->text .= "country : ".$countryCode['CountryCode']['country_name'] . "\n";
				$this->mySlack->text .= "Name : ".$user['nickname'] . "\n";
				$this->mySlack->text .= "Mail address : ".$user['email']. "\n";
				$this->mySlack->text .= myTools::getUrl()."/admin/user-manage/member/".$user['id']." \n";
				$this->mySlack->text .= "```";
				$this->mySlack->sendSlack();
			}
		// if has error
		} else {
			$this->error = true;
			$arrReturn['id'] = Configure::read('error.nexmo_error');
			$arrReturn['content'] = $result;
		}
		
		// set error
		$arrReturn['error'] = $this->error;
		
		// return json || normal message
		return $this->returnJSONMessage($arrReturn);
	}
	
	/**
	 * inputPhoneNumber
	 * -> set student's phone number
	 */
	public function inputPhoneNumber(){
		// set json encode
		$data = json_decode($this->request->input(),true);
		$content = "";
		$arrReturn = array(
			'return_json' => $this->isJSON,
			'error' => $this->error,
			'content' => ''
		);
		
		// check if data exists
		if (!$data) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.invalid_request');
			$arrReturn['content'] = "Error! invalid request!";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// check if phone_number exists
		if (
			isset($data['phone_number']) === FALSE || 
			(isset($data['phone_number']) && empty($data['phone_number'])) ||
			(isset($data['phone_number']) && !empty($data['phone_number']) && !is_string($data['phone_number']))
		) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.missing_mandatory_parameter_phone_number');
			$arrReturn['content'] = "Your request is incomplete and missing the mandatory parameter: phone_number";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}

		// check if country_code exists
		if (
			isset($data['country_code']) === FALSE || 
			(isset($data['country_code']) && empty($data['country_code']))
		) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.missing_mandatory_parameter_country_code');
			$arrReturn['content'] = "Your request is incomplete and missing the mandatory parameter: country_code";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// user_id exists
		if (isset($data['user_id']) && !empty($data['user_id'])) {
		
		// if users_api_token exists
		} else if (isset($data['users_api_token'])) {
			
			// validate token
			$user = $this->apiCommon->validateToken($data['users_api_token']);			
			
			if (isset($data['user_status'])) {
				if ($data['user_status'] < 0 || $data['user_status'] > 9) {
					$arrReturn['error'] = true;
					$arrReturn['id'] = Configure::read('error.user_status_invalid');
					$arrReturn['content'] = "user_status is invalid";

					//return json || normal message
					return $this->returnJSONMessage($arrReturn);
				} else {
					//check mismatch
					$params = array('status' => $data['user_status'], 'user' => $user);
					$mismatch = UserTable::checkStatusMatch($params);
					if ($mismatch['fail']) {
						$arrReturn['error'] = true;
						$arrReturn['id'] = $mismatch['data']['error']['id'];
						$arrReturn['content'] = $mismatch['data']['error']['message'];

						//return json || normal message
						return $this->returnJSONMessage($arrReturn);
					}
				}
			}
							
			// if has no user id
			if (isset($user['id']) === FALSE) {
				$this->error = true;
				$arrReturn['error'] = $this->error;
				$arrReturn['id'] = Configure::read('error.invalid_api_token');
				$arrReturn['content'] = "Invalid Api token";
				
				// return json || normal message
				return $this->returnJSONMessage($arrReturn);
			} else {
				// set condition
				$data['user_id'] = $user['id'];
			}
		} else {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.missing_mandatory_parameter_users_api_token_or_user_id');
			$arrReturn['content'] = "Your request is incomplete and missing the mandatory parameter: users_api_token or user_id";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}

		// check if phone number with the country code exists
		$phoneDoubleCheck = $this->User->find('count',array(
			'conditions' => array(
				'User.phone_number' => $data['phone_number'],
				'User.country_code'  => $data['country_code'],
				'User.id !=' => $data['user_id']
			)
		));
		
		// if phone number already exists
		if(!empty($phoneDoubleCheck)){
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.this_number_is_already_in_use');
			$arrReturn['content'] = "This number is already in use";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// set user variable
		$this->User->set(array(
			'id' => $data['user_id'],
			'phone_number' => $data['phone_number'],
			'country_code' => $data['country_code']
		));
		
		// if save was successful
		if ($this->User->save()) {
			$arrReturn['error'] = $this->error;
			$arrReturn['content'] = $this->isJSON ? array("updated" => true) : "";
		
		// if an error occurred during save
		} else {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.save_to_user_table_failure');
			$arrReturn['content'] = "error Save to user table failure";
		}
		
		// return json || normal message
		return $this->returnJSONMessage($arrReturn);
	}
		
	public function isSmsAuthUser(){
		$data = json_decode($this->request->input(),true);
		
		// set api common controller
		$content = "";
		$arrReturn = array(
			'return_json' => $this->isJSON,
			'error' => $this->error,
			'content' => ''
		);
		
		// check if data exists
		if (!$data) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.invalid_request');
			$arrReturn['content'] = "Error! invalid request!";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// user_id exists
		if (isset($data['user_id']) && !empty($data['user_id'])) {
		
		// if users_api_token exists
		} else if (isset($data['users_api_token'])) {
			
			// validate token
			$user = $this->apiCommon->validateToken($data['users_api_token']);
			
			// if has no user id
			if (isset($user['id']) === FALSE) {
				$this->error = true;
				$arrReturn['error'] = $this->error;
				$arrReturn['id'] = Configure::read('error.invalid_api_token');
				$arrReturn['content'] = "Invalid Api token";
				
				// return json || normal message
				return $this->returnJSONMessage($arrReturn);
			} else {
				// set condition
				$data['user_id'] = $user['id'];
			}
		} else {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.missing_mandatory_parameter_users_api_token_or_user_id');
			$arrReturn['content'] = "Your request is incomplete and missing the mandatory parameter: users_api_token or user_id";
			
			// return json || normal message
			return $this->returnJSONMessage($arrReturn);
		}
		
		// get verification log for nexmo app
		$this->PhoneVerifyCheckLog->openDBReplica();
		$countTotalVerify = $this->PhoneVerifyCheckLog->find('count',array(
			'conditions' => array(
				'user_id' => $data['user_id'],
				'status' => 0,
			)
		));
		$this->PhoneVerifyCheckLog->closeDBReplica();
		
		// if user's sms verification was successful		
		if ($countTotalVerify || (isset($user['sms_through_flg']) && $user['sms_through_flg'])) {
			$this->error = false;
			$arrReturn['error'] = $this->error;
			$arrReturn['content'] = $this->isJSON ? array("isSmsAuthUser" => true) : "";
			
		// if user's sms was not verified
		} else {
			$this->error = false;
			$arrReturn['error'] = $this->error;
			$arrReturn['content'] = $this->isJSON ? array("isSmsAuthUser" => false) : "SMS authentication is required";
		}
		
		// return json || normal message
		return $this->returnJSONMessage($arrReturn);
	}
		
	/**
	 * returnJSONMessage
	 * -> return message for smsAuth
	 */
	public function returnJSONMessage($message = array()) {
		// message to be returned
		$returnMessage = "";
		
		// check if client doesn't want a JSON formatted data
		if (!isset($message['return_json']) || !$message['return_json'])
		{ return $message['content']; }

		if (isset($message['error']) && $message['error']) {
			//add id to result if there is
			if (isset($message['id']) && $message['id']) {
				$returnMessage = array(
					'error' => array(
						'id' => $message['id'],
						'message' => $message['content']
					)
				);
			} else {
				$returnMessage = array(
					'error' => array(
						'message' => $message['content']
					)
				);
			}
		} else {
			// return message content
			$returnMessage = $message['content'];
		}
		
		// return message
		return json_encode($returnMessage);
	}
}
