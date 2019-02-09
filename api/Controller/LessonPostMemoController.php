<?php
/**
* Lesson Post Memo
**/
App::uses('ApiCommonController', 'Controller');
class LessonPostMemoController extends AppController {

	public $uses = array('LessonOnair', 'UsersMemo');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;
		$response = array();
		if ($this->request->is('post')) {
			@$data = json_decode($this->request->input(), true);
			if (!$data) {
				$response['error']['message'] = __('Invalid request');
			} else if (!isset($data['users_api_token']) || trim($data['users_api_token']) == "" || empty($data['users_api_token'])) {
				$response['error']['message'] = __('users_api_token is required');
			} else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) { 
				$response['error']['message'] = __('users_api_token must be string');
			} else if (!isset($data['teacher_id']) || trim($data['teacher_id']) == "" || empty($data['teacher_id'])) {
				$response['error']['message'] = __('teacher_id is required');
			} else if (isset($data['teacher_id']) && is_string($data['teacher_id']) || !is_int($data['teacher_id'])) {
				$response['error']['message'] = __('teacher_id must be int');
			} else if (isset($data['chat_hash']) && !is_string($data['chat_hash'])) {
				$response['error']['message'] = __('chat_hash must be string');
			} else if (!isset($data['chat_hash']) || trim($data['chat_hash']) == "" || empty($data['chat_hash'])) {
				$response['error']['message'] = __('chat_hash is required');
			} else if (isset($data['memo']) && !is_string($data['memo'])) {
				$response['error']['message'] = __('memo must be string');
			} else if (isset($data['memo']) && (strlen($data['memo']) > 3000)) {
				$response['error']['message'] = __('memo must not exceed 3000 characters');
			} else if (!isset($data['memo']) || trim($data['memo']) == "" || empty($data['memo'])) {
				$response['error']['message'] = __('memo is required');
			}  else {
				$apiCommon = new ApiCommonController();
				$user = $apiCommon->validateToken($data['users_api_token']);
				if (!$user) {
					$response['error']['message'] = __('Invalid users_api_token');
					return json_encode($response);
				}
					// checks if chat_hash is for the current user
					$userLesson = $this->LessonOnair->find('first', array(
							    'fields' => array('chat_hash','id','teacher_id'),
								'conditions' => array(
										 'user_id' => $user['id'],
										 'teacher_id' => $data['teacher_id'],
										 'BINARY (chat_hash) LIKE' => $data['chat_hash'],
										)
								));	
					if (!$userLesson) {
						$response['error']['message'] = __('Lesson does not exist'); 
						return json_encode($response);
					} else {
						//check if lesson already got a memo
						$prepareData = array(
							'teacher_id' => $userLesson['LessonOnair']['teacher_id'],
							'user_id' => $user['id'],
							'onair_id' =>  $userLesson['LessonOnair']['id'],
							'chat_hash' => $userLesson['LessonOnair']['chat_hash'],
							'memo' => strip_tags($data['memo'])
						);
					}
				$save = false;
				//save or update user_memo
				if(isset($prepareData)){
					$save = $this->UsersMemo->saveMemo($prepareData);
				}
			    
				if ($save) {
					$response['result'] = true;
				} else {
					$response['result'] = false;
				}
			}
		}
		return json_encode($response);
	}
}