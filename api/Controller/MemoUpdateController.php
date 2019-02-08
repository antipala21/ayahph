<?php
/********************************
*																*
*	Memo List for API 						*
*	Author: John Mart Belamide		*
*	August 2015										*
*																*
********************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTools','Lib');
class MemoUpdateController extends AppController {
	public $uses = array(
		'UsersMemo'
	);
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
			} else if (empty($data['lesson_id'])) {
				$response['error']['id'] = Configure::read('error.lesson_id_is_required');
				$response['error']['message'] = __('lesson_id is required');
			} else if (isset($data['lesson_id']) && !is_int($data['lesson_id'])) {
				$response['error']['id'] = Configure::read('error.lesson_id_must_be_integer');
				$response['error']['message'] = __('lesson_id must be int');
			} else {
				$apiCommon = new ApiCommonController();
				$user = $apiCommon->validateToken($data['users_api_token']);
				$this->UsersMemo->openDBReplica();
				$userMemo = $this->UsersMemo->findByIdAndUserId($data['lesson_id'], $user['id']);	// checks if lesson_id is for the current user
				$this->UsersMemo->closeDBReplica();
				if ( !$user ) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = __('Invalid users_api_token');
				} else if ( !isset($userMemo['UsersMemo']) ) {
					$response['error']['id'] = Configure::read('error.invalid_lesson_id');
					$response['error']['message'] = __('Invalid lesson_id');
				} else {
					$this->UsersMemo->id = $data['lesson_id'];
					$data = array('UsersMemo' => array(
						'memo' => $data['text']
						)
					);
					if ( $this->UsersMemo->save( $data ) ) {
						$response['updated'] = true;
					} else {
						$response['updated'] = false;
					}
				}
			}
		}
		return json_encode($response);
	}
}