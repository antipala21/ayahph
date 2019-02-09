<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersDeviceNotificationController extends AppController {
    public $uses = array('TeacherDeviceToken', 'Teacher');

    public function beforeFilter() {
    	parent::beforeFilter();
    	$this->Auth->allow(array(
    		'index',
    		'unregister'
    	));
    	$this->autoRender = false;
    }

    public function index() {
    	$data = array();
    	$response = array();

    	if ($this->request->is('post')) {
    		$data = json_decode($this->request->input(), true);
    	}

    	// check for request data
    	if (empty($data)) {
    		$response['error']['id'] = Configure::read('error.invalid_request');
    		$response['error']['message'] = __('Invalid request.');

    	} else {

    		// check device type
    		if (!isset($data['device_type']) ) {
    			$response['error']['id'] = Configure::read('error.device_type_is_required');
    			$response['error']['message'] = __('device_type is required');
    		} else {
    			if (!is_int($data['device_type'])) {
    				$response['error']['id'] = Configure::read('error.device_type_must_be_integer');
    				$response['error']['message'] = __('device_type must be integer');
    			}

    			if ($data['device_type'] != 1 && $data['device_type'] != 2) {
    				$response['error']['id'] = Configure::read('error.invalid_device_type');
    				$response['error']['message'] = __('device_type must be 1 or 2');
    			}
    		}

    		// check device token
    		if (!isset($data['device_token'])) {
    			$response['error']['id'] = Configure::read('error.device_token_is_required');
    			$response['error']['message'] = __('Your request is incomplete and missing the mandatory parameter: device_token');
    		} else {
    			if (!is_string($data['device_token'])) {
    				$response['error']['id'] = Configure::read('error.device_token_must_be_string');
    				$response['error']['message'] = __('device_token must be string');
    			}

    			if (strlen(trim($data['device_token'])) == 0) {
    				$response['error']['id'] = Configure::read('error.device_token_is_required');
    				$response['error']['message'] =  __('device_token can not be empty');
    			}
    		}

    		// check teachers api token
    		if (!isset($data['teachers_api_token'])) {
    			$response['error']['id'] = Configure::read('error.missing_mandatory_parameter_users_api_token_or_user_id');
    			$response['error']['message'] =  __('Your request is incomplete and missing the mandatory parameter: teachers_api_token');
    		} else {
    			if(!is_string($data['teachers_api_token'])) {
    				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
    				$response['error']['message'] = __('teachers_api_token must be string');
    			}

    			if (strlen(trim($data['teachers_api_token'])) == 0) {
    				$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
    				$response['error']['message'] =  __('teachers_api_token can not be empty');
    			}
    		}
    	}


    	// check for errors
    	if (isset($response['error'])) {
    		return json_encode($response);

    	} else {
    		$token = $data['teachers_api_token'];
    		$deviceToken = $data['device_token'];
    		$deviceType = $data['device_type'];

    		// check valid token
    		$getTeacherData = $this->Teacher->getFromToken($token);
    		if( count($getTeacherData) == 0) {
    			$response['error']['id'] = Configure::read('error.invalid_api_token');
    			$response['error']['message'] = __('invalid_api_token');
    			return json_encode($response);
    		}

    		// check device token
    		$row = $this->TeacherDeviceToken->find('first', array(
    			'fields' => array(
    				'TeacherDeviceToken.id',
    				'TeacherDeviceToken.teacher_id',
    				'TeacherDeviceToken.endpoint_arn'
    			),
    			'conditions' => array(
    				'TeacherDeviceToken.device_token' => $deviceToken,
    				'TeacherDeviceToken.device_type' => $deviceType
    			)
    		));

    		$teacherPN = new teacherPushNotification();

    		// update teacher id
    		if (!empty($row)) {
    			$updateRow = array('teacher_id' => $getTeacherData['id']);
    			$endpointArn = $row['TeacherDeviceToken']['endpoint_arn'];
    			$updateEndpoint = false;

    			// check endpoint
    			if (strlen(trim($endpointArn)) == 0) {
    				$updateEndpoint = true;
    			} else {
    				$checkEndpoint = $teacherPN->isEndpointEnabled($endpointArn);
    				if (isset($checkEndpoint['enabled']) && $checkEndpoint['enabled'] === false) {
    					$updateEndpoint = true;
    				}
    			}

    			// update endpoint for device token
    			if ($updateEndpoint) {
    				$endpoint = $teacherPN->getEndpointArn($deviceToken, $deviceType);
    				if ($endpoint) {
    					$updateRow['endpoint_arn'] = $endpoint;
    				}
    			}

    			$this->TeacherDeviceToken->read(null, $row['TeacherDeviceToken']['id']);
    			$this->TeacherDeviceToken->set($updateRow);
    			$save = $this->TeacherDeviceToken->save();

    			$response['success'] = $save ? true : false;
    			$response['action'] = "update";

    		// save new device token
    		} else {
    			$newRow = array(
    				'teacher_id' => $getTeacherData['id'],
    				'device_token' => $deviceToken,
    				'device_type' => $deviceType,
    				'endpoint_arn' => ''
    			);

    			// get endpoint for device token
    			$endpoint = $teacherPN->getEndpointArn($deviceToken, $deviceType);
    			if ($endpoint) {
    				$newRow['endpoint_arn'] = $endpoint;
    			}

    			$this->TeacherDeviceToken->create();
    			$this->TeacherDeviceToken->set($newRow);
    			$save = $this->TeacherDeviceToken->save();

    			$response['success'] = $save ? true : false;
    			$response['action'] = "new";
    		}

    		return json_encode($response);

    	}
    }

    public function unregister() {
    	$data = array();
    	$response = array();

    	if ($this->request->is('post')) {
    		$data = json_decode($this->request->input(), true);
    	}

    	// check for request data
    	if (empty($data)) {
    		$response['error']['id'] = Configure::read('error.invalid_request');
    		$response['error']['message'] = __('Invalid request.');

    	} else {

    		// check device type
    		if (!isset($data['device_type']) ) {
    			$response['error']['id'] = Configure::read('error.device_type_is_required');
    			$response['error']['message'] = __('device_type is required');
    		} else {
    			if (!is_int($data['device_type'])) {
    				$response['error']['id'] = Configure::read('error.device_type_must_be_integer');
    				$response['error']['message'] = __('device_type must be integer');
    			}

    			if ($data['device_type'] != 1 && $data['device_type'] != 2) {
    				$response['error']['id'] = Configure::read('error.invalid_device_type');
    				$response['error']['message'] = __('device_type must be 1 or 2');
    			}
    		}

    		// check device token
    		if (!isset($data['device_token'])) {
    			$response['error']['id'] = Configure::read('error.device_token_is_required');
    			$response['error']['message'] = __('Your request is incomplete and missing the mandatory parameter: device_token');
    		} else {
    			if (!is_string($data['device_token'])) {
    				$response['error']['id'] = Configure::read('error.device_token_must_be_string');
    				$response['error']['message'] = __('device_token must be string');
    			}

    			if (strlen(trim($data['device_token'])) == 0) {
    				$response['error']['id'] = Configure::read('error.device_token_is_required');
    				$response['error']['message'] =  __('device_token can not be empty');
    			}
    		}

    		// check teachers api token
    		if (!isset($data['teachers_api_token'])) {
    			$response['error']['id'] = Configure::read('error.missing_mandatory_parameter_users_api_token_or_user_id');
    			$response['error']['message'] =  __('Your request is incomplete and missing the mandatory parameter: teachers_api_token');
    		} else {
    			if(!is_string($data['teachers_api_token'])) {
    				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
    				$response['error']['message'] = __('teachers_api_token must be string');
    			}

    			if (strlen(trim($data['teachers_api_token'])) == 0) {
    				$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
    				$response['error']['message'] =  __('teachers_api_token can not be empty');
    			}
    		}
    	}


    	// check for errors
    	if (isset($response['error'])) {
    		return json_encode($response);

    	} else {
    		$token = $data['teachers_api_token'];
    		$deviceToken = $data['device_token'];
    		$deviceType = $data['device_type'];

    		// check valid token
    		$getTeacherData = $this->Teacher->getFromToken($token);
    		if ( count($getTeacherData) == 0 ) {
    			$response['error']['id'] = Configure::read('error.invalid_api_token');
    			$response['error']['message'] = __('invalid_api_token');
    			return json_encode($response);
    		}

    		// check device token
    		$row = $this->TeacherDeviceToken->find('first', array(
    			'fields' => array(
    				'TeacherDeviceToken.id',
    				'TeacherDeviceToken.teacher_id',
    				'TeacherDeviceToken.endpoint_arn'
    			),
    			'conditions' => array(
    				'TeacherDeviceToken.teacher_id' => $getTeacherData['id'],
    				'TeacherDeviceToken.device_token' => $deviceToken,
    				'TeacherDeviceToken.device_type' => $deviceType
    			)
    		));

    		if (!empty($row)) {
    			// remove teacher id
    			$this->TeacherDeviceToken->read('teacher_id', $row['TeacherDeviceToken']['id']);
    			$this->TeacherDeviceToken->set('teacher_id', null);
    			$save = $this->TeacherDeviceToken->save();

    			$response['success'] = $save ? true : false;

    		} else {
    			$response['error']['id'] = Configure::read('error.this_user_does_not_have_this_device_token');
    			$response['error']['message'] = __('teacher does not have this device_token');
    		}

    		return json_encode($response);
    	}
    }
}