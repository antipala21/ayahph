<?php
/**
* Memo Create
*	Author: Jacob Earl G. P. (Braulioz)
*	September 2, 2015
**/
App::uses('ApiCommonController', 'Controller');
class MemoCreateController extends AppController {

	public $uses = array('LessonOnairsLog', 'UsersMemo');

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
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
			} else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) { 
				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['error']['message'] = __('users_api_token must be string');
			} else if (isset($data['text']) && !is_string($data['text'])) {
				$response['error']['id'] = Configure::read('error.text_must_be_string');
				$response['error']['message'] = __('text must be string');
			} else if (!isset($data['users_api_token']) || trim($data['users_api_token']) == "" || empty($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			} else if (!isset($data['text']) || trim($data['text']) == "" || empty($data['text'])) {
				$response['error']['id'] = Configure::read('error.text_is_required');
				$response['error']['message'] = __('text is required');
			} else if (isset($data['lesson_id']) && is_string($data['lesson_id'])) {
				$response['error']['id'] = Configure::read('error.lesson_id_must_be_integer');
				$response['error']['message'] = __('lesson_id must be int');
			} else {
				$apiCommon = new ApiCommonController();
				$user = $apiCommon->validateToken($data['users_api_token']);
				if (!$user) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = __('Invalid users_api_token');
					return json_encode($response);
				}
				if (isset($data['lesson_id']) && is_int($data['lesson_id'])) {
					// checks if lesson_id is for the current user
					$userLesson = $this->LessonOnairsLog->find('first', array(
							    'fields' => array('chat_hash','onair_id'),
								'conditions' => array(
										 'id' =>	$data['lesson_id'],
										 'user_id' => $user['id']
										)
								));	
					if (!$userLesson) {
						$response['error']['id'] = Configure::read('error.invalid_lesson_id');
						$response['error']['message'] = __('Invalid lesson_id'); 
						return json_encode($response);
					} else {
						//check if lesson already got a memo
						$this->UsersMemo->openDBReplica();
						$userMemo = $this->UsersMemo->findByChatHashAndUserId($userLesson['LessonOnairsLog']['chat_hash'], $user['id']);
						$this->UsersMemo->closeDBReplica();
						$prepareData = array('UsersMemo' => array(
								'user_id' 	=> $user['id'],
								'onair_id' 	=> $userLesson['LessonOnairsLog']['onair_id'],
								'chat_hash' => $userLesson['LessonOnairsLog']['chat_hash'],
								'memo' => $data['text']
							)
						);
					}
				} else {
					$prepareData = array( 'UsersMemo' => array(
							'memo' => $data['text'],
							'user_id' 	=> $user['id']
							)
						);
				}
				//if theres a user memo for the lesson update only
				if (isset($userMemo['UsersMemo']['id'])) {
					$this->UsersMemo->id = $userMemo['UsersMemo']['id'];
				}
				//save or update user_memo
				if ($this->UsersMemo->save($prepareData)) {
					$response['created'] = true;
				} else {
					$response['created'] = false;
				}
			}
		}
		return json_encode($response);
	}
}