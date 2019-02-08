<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class LessonForceTerminateController extends AppController {
	public $uses = array('LessonOnair', 'User');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');

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

	public function index() {
		$this->autoRender = false;

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

		// get lesson onair
		$lessonOnair = $this->LessonOnair->find('first', array(
			'fields' => array(
				'LessonOnair.id',
				'LessonOnair.lesson_type'
			),
			'conditions' => array(
				'LessonOnair.user_id' => $user['id']
			),
			'recursive' => -1
		));
		
		// if has ongoing lesson onair information
		if (!$lessonOnair) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.force_terminate.missing_lesson_onairs');
			$arrReturn['content'] = "Unable to delete missing lesson onair!";
			return $this->returnJSONMessage($arrReturn);
		}
		
		// if not normal lesson
		if (isset($lessonOnair["LessonOnair"]["lesson_type"]) && $lessonOnair["LessonOnair"]["lesson_type"] != 1) {
			$this->error = true;
			$arrReturn['error'] = $this->error;
			$arrReturn['id'] = Configure::read('error.force_terminate.invalid_lesson_type');
			$arrReturn['content'] = "Unable to forcefully terminate ongoing lesson!";
			return $this->returnJSONMessage($arrReturn);
		}
		
		// log
		$this->log("[delete_lesson_onair_api] deleting lesson onair -> " . json_encode($lessonOnair), "debug");
		
		// delete lesson oanir
		LessonOnairTable::delete($lessonOnair['LessonOnair']['id'], array(), 6);

		// set success message
		$arrReturn['content'] = $this->isJSON ? array('result' => true) : '';
		$arrReturn['return_json'] = $this->isJSON;
		
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

		// if has error
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

		// else, has no error
		} else {
			// return message content
			$returnMessage = $message['content'];
		}
		
		// return message
		return json_encode($returnMessage);
	}
}
