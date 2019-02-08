<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersUpdateNotificationController extends AppController {
	public $uses = array('User','DeviceToken', 'UsersSubscription');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index', 'checkNotificationEndpoint', 'unregisterDeviceToken', 'createNotificationEndpoint'));
		$this->autoRender = false;
	}
	public function index() {
		@$data = json_decode($this->request->input(),true);
		if ($data) {
			foreach($data as $key => $value) {
				$data[$key] = $value;
			}
		}
		
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (!isset($data['device_token']) || empty($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_is_required');
			$response['error']['message'] = __('device_token is required');
		} else if (!isset($data['active_flg'])) {
			$response['error']['id'] = Configure::read('error.active_flg_is_required');
			$response['error']['message'] = __('active_flg is required');
		} else if(!is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else if (!is_string($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_must_be_string');
			$response['error']['message'] = __('device_token must be string');
		} else if (!is_int($data['active_flg'])) {
			$response['error']['id'] = Configure::read('error.active_flg_must_be_integer');
			$response['error']['message'] = __('active_flg must be integer');
		} else if (!in_array($data['active_flg'],array(0,1))) {
			$response['error']['id'] = Configure::read('error.invalid_active_flg');
			$response['error']['message'] = __('active_flg must be 0 or 1');
		} else {
			$api = new ApiCommonController();
			$user['User'] = $api->validateToken($data['users_api_token']);
			if (!$user['User']) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $api->error;
			} else {
				$device = $this->getUserDevice($user['User']['id'], $data['device_token']);				
				if (!$device) {
					$response['error']['id'] = Configure::read('error.this_user_does_not_have_this_device_token');
					$response['error']['message'] = __("This user doesn't have this device token");
				} else {
					if ($this->DeviceToken->read(null, $device['DeviceToken']['id'])) {
						$this->DeviceToken->set(array('active_flg' => $data['active_flg']));
						try {
							if ($this->DeviceToken->save()) {
								$response['updated'] = true;
							} else {
								$response['updated'] = false;
							}
						} catch (Exception $e) {
							CakeLog::write("error", $e->getMessage());
							$response['error']['id'] = Configure::read('error.save_to_device_token_table_failure');
							$response['error']['message'] = __("Error Occured. Could not update device");
						}						
					} else {
						$response['error']['id'] = Configure::read('error.device_token_does_not_exist');
						$response['error']['message'] = __("Error Occured. Device token does not exist");
					}
				}
			}
		}
		return json_encode($response);
	}

	public function getUserDevice($id,$token) {
		$token = trim($token);
		$valid = false;
		if (!empty($token)) {
			$valid = $this->DeviceToken->find('first', array(
				'fields' => array(
					'DeviceToken.id',
					'DeviceToken.endpoint_arn',
					'DeviceToken.device_type',
					'DeviceToken.active_flg'
					),
				'conditions' => array(
					'DeviceToken.device_token' => $token,
					'DeviceToken.user_id' => $id
					),
				'order' => 'DeviceToken.id DESC'
				));
		}

		return $valid;
	}
	
	/**
	 * delete device token and delete associated endpoint
	 * @return [type] [description]
	 */
	public function unregisterDeviceToken() {
		$response = array();
		
		@$data = json_decode($this->request->input(),true);
		if ($data) {
			foreach($data as $key => $value) {
				$data[$key] = $value;
			}
		}
		
		//validate passed data
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if(!is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else if (!isset($data['device_token']) || empty($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_is_required');
			$response['error']['message'] = __('device_token is required');
		} else if (!is_string($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_must_be_string');
			$response['error']['message'] = __('device_token must be string');
		} else if (!isset($data['device_type']) ) {
			$response['error']['id'] = Configure::read('error.device_type_is_required');
			$response['error']['message'] = __('device_type is required');
		} else if (!is_int($data['device_type'])) {
			$response['error']['id'] = Configure::read('error.device_type_must_be_integer');
			$response['error']['message'] = __('device_type must be integer');
		} else if ($data['device_type']!=1 && $data['device_type']!=2) {
			$response['error']['id'] = Configure::read('error.invalid_device_type');
			$response['error']['message'] = __('device_type must be 1 or 2');
		} else {
			//validate api token
			$api = new ApiCommonController();
			$user['User'] = $api->validateToken($data['users_api_token']);
			if (!$user['User']) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $api->error;
			} else {
				//find row of this device token
				$deviceToken = $this->DeviceToken->find('first', array(
					'fields' => array(
						'DeviceToken.id',
						'DeviceToken.endpoint_arn'
					),
					'conditions' => array(
						'DeviceToken.device_token' => $data['device_token'],
						'DeviceToken.device_type' => $data['device_type']
					)
				));
				
				if ($deviceToken) {
					$pn = new pushNotification();
					$deleteEndpoint = $result = false;

					//set device token active_flg to 0 and endpoint to null
					if ($this->DeviceToken->read(null, $deviceToken['DeviceToken']['id'])) {
						$this->DeviceToken->begin();
						$this->DeviceToken->set(array('active_flg' => 0, 'endpoint_arn' => null));
						try {
							$result = $this->DeviceToken->save();
						} catch (Exception $e) {
							CakeLog::write("error", $e->getMessage());
						}

						//delete endpoint
						$deleteEndpoint = $pn->deleteEndpoint($deviceToken['DeviceToken']['endpoint_arn']);

						//unsubscribe past token subscriptions
						$subscriptions = $this->UsersSubscription->find('count', array(
							'conditions' => array(
								'UsersSubscription.device_token_id' => $deviceToken['DeviceToken']['id'],
								'UsersSubscription.status' => 1
								)
							));

						if ($subscriptions > 0) {
							$unsubscribe = $this->UsersSubscription->updateAll(
								array('status' => 0), 
								array(
									'device_token_id' => $deviceToken['DeviceToken']['id'],
									'status' => 1
									)
								);
						} else {
							$unsubscribe = true;
						}

						//commit if successfully deleted
						if ($deleteEndpoint && $unsubscribe) {
							$this->DeviceToken->commit();
							$result = true;
						} else {
							$this->DeviceToken->rollback();
							$result = false;
						}

						$response['delete'] = ($result) ? true : false;
					} else {
						$response['error']['id'] = Configure::read('error.save_to_device_token_table_failure');
						$response['error']['message'] = __("Error Occured. Could not update device");	
					}

				} else {
					$response['error']['id'] = Configure::read('error.device_token_does_not_exist');
					$response['error']['message'] = __("Device token does not exist.");					
				}				
			}
		}

		return json_encode($response);
	}
	
	/**
	 * check notification endpoint if present and active
	 */
	public function checkNotificationEndpoint() {
		$response = array();
		
		@$data = json_decode($this->request->input(),true);
		if ($data) {
			foreach($data as $key => $value) {
				$data[$key] = $value;
			}
		}
		
		//validate passed data
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if(!is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else if (!isset($data['device_token']) || empty($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_is_required');
			$response['error']['message'] = __('device_token is required');
		} else if (!is_string($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_must_be_string');
			$response['error']['message'] = __('device_token must be string');
		} else {
			//validate api token
			$api = new ApiCommonController();
			$user['User'] = $api->validateToken($data['users_api_token']);
			if (!$user['User']) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $api->error;
			} else {
				$device = $this->getUserDevice($user['User']['id'], $data['device_token']);				
				//check if device_token is registered under user
				if ($device) {
					//check if endpoint_arn is not empty
					if (isset($device['DeviceToken']['endpoint_arn']) && !is_null($device['DeviceToken']['endpoint_arn'])) {
						//check if endpoint is enabled
						$pn = new pushNotification();
						$enabled = $pn->isEndpointEnabled($device['DeviceToken']['endpoint_arn']);
						$save = false;

						//-----Update Active Flag
						if ($this->DeviceToken->read(null, $device['DeviceToken']['id'])) {
							$this->DeviceToken->set(array(
								'active_flg' => ($enabled) ? 1 : 0
							));
							try {
								$save = $this->DeviceToken->save();
							} catch (Exception $e) {
								CakeLog::write("error", $e->getMessage());
							}
						}

						if ($enabled && $save) {
							//----Subscribe
							if (
								isset($user['User']['sns_topic_arn']) && 
								!is_null($user['User']['sns_topic_arn']) && 
								!empty($user['User']['sns_topic_arn'])
							) {
								//check if topic arn column exists and is not null
								$topicArn = $user['User']['sns_topic_arn'];
							} else {
								$topicArn = $this->UsersSubscription->createTopicForUser($user['User']['id']);
							}

							$subscribe = $this->UsersSubscription->subscribeToTopic($device['DeviceToken']['id'], $device['DeviceToken']['endpoint_arn'], $user['User']['id'], $topicArn);
							$response['endpoint'] = $subscribe;
						} else {
							$response['error']['id'] = Configure::read('error.notification_endpoint_is_disabled');
							$response['error']['message'] = __("Endpoint for device token is disabled.");
						}
					} else {
						$response['error']['id'] = Configure::read('error.notification_endpoint_does_not_exist');
						$response['error']['message'] = __("Endpoint for device token does not exist.");
					}
				}  else {
					$response['error']['id'] = Configure::read('error.this_user_does_not_have_this_device_token');
					$response['error']['message'] = __("This user does not have this device token.");
				}
			}
		}
		return json_encode($response);
	}
	
	/**
	 * Create an endpoint with passed device token
	 * save endpoint to db and update user_id
	 */
	public function createNotificationEndpoint() {
		$response = array();
		
		@$data = json_decode($this->request->input(),true);
		if ($data) {
			foreach($data as $key => $value) {
				$data[$key] = $value;
			}
		}
		
		//validate passed data
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if(!is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else if (!isset($data['device_token']) || empty($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_is_required');
			$response['error']['message'] = __('device_token is required');
		} else if (!is_string($data['device_token'])) {
			$response['error']['id'] = Configure::read('error.device_token_must_be_string');
			$response['error']['message'] = __('device_token must be string');
		} else {
			//validate api token
			$api = new ApiCommonController();
			$user['User'] = $api->validateToken($data['users_api_token']);
			if (!$user['User']) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $api->error;
			} else {
				//find device token
				$deviceToken = $this->DeviceToken->find('first', array(
					'fields' => array(
						'DeviceToken.id',
						'DeviceToken.endpoint_arn',
						'DeviceToken.device_type'
					),
					'conditions' => array('DeviceToken.device_token' => $data['device_token']),
					'order' => array('DeviceToken.id' => 'DESC')
				));

				if ($deviceToken) {
					$pn = new pushNotification();
					$save = $endpointSave = false;

					//if row has endpoint_arn presently, delete old endpoint
					if (isset($deviceToken['DeviceToken']['endpoint_arn']) && !is_null($deviceToken['DeviceToken']['endpoint_arn'])) {
						$pn->deleteEndpoint($deviceToken['DeviceToken']['endpoint_arn']);
					}
					
					//create Endpoint with deviceToken and get EndpointArn
					$endpointArn = $pn->getEndpointArn($data['device_token'], $deviceToken['DeviceToken']['device_type']);
					
					//if endpoint created
					if ($endpointArn) {
						if ($this->DeviceToken->read(null, $deviceToken['DeviceToken']['id'])) {
							//update row and save EndpointArn to database
							$this->DeviceToken->set(array(
								"user_id" => $user['User']['id'],//replace user_id if different user
								"active_flg" => 1,
								"endpoint_arn" => $endpointArn
							));
							try {
								$save = $this->DeviceToken->save();
							} catch (Exception $e) {
								CakeLog::write("error", $e->getMessage());
							}
							$endpointSave = ($save) ? true : false;
						}						
					}
					$response['endpoint_created'] = $endpointSave;

				} else {
					$response['error']['id'] = Configure::read('error.device_token_does_not_exist');
					$response['error']['message'] = __("Device token does not exist.");
				}
			}
		}
		return json_encode($response);
	}

}