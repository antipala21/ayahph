<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersFavoriteController extends AppController {
	public $uses = array(
		'Teacher',
		'User',
		'UsersFavorite'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
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
			} else if (!isset($data['users_api_token']) || trim($data['users_api_token']) == "") {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			} else if (!isset($data['teachers_id']) || trim($data['teachers_id']) == "") {
				$response['error']['id'] = Configure::read('error.teachers_id_is_required');
				$response['error']['message'] = __('teachers_id is required');
			} else if (!isset($data['favorited'])) {
				$response['error']['id'] = Configure::read('error.favorited_is_required');
				$response['error']['message'] = __('favorited is required');
			} else if (trim($data['favorited']) == "") {
				$response['error']['id'] = Configure::read('error.favorited_is_required');
				$response['error']['message'] = __('favorited is required');
			} else if (!is_int($data['favorited'])) {
				$response['error']['id'] = Configure::read('error.favorited_must_be_integer');
				$response['error']['message'] = __('favorited must be integer');
			} else if (!is_int($data['teachers_id'])) {
				$response['error']['id'] = Configure::read('error.teachers_id_must_be_integer');
				$response['error']['message'] = __('teachers_id must be integer');
			} else if ($data['favorited'] != 0 && $data['favorited'] != 1) {
				$response['error']['id'] = Configure::read('error.invalid_favorited');
				$response['error']['message'] = __("Favorited must be only 0 and 1");
			} else {
				$api = new ApiCommonController();

				$token = $api->validateToken($data['users_api_token']);

				$userId = $token['id'];
				$teacherId = $data['teachers_id'];
				$teacherFavorite = $this->UsersFavorite->useReplica()->find('first', array(
						'conditions' => array(
							'UsersFavorite.teacher_id' => $teacherId,
							'UsersFavorite.user_id' => $userId
						)
					)
				);
				
				if ($api->checkBlocked($data['teachers_id'],$token['id'])) {
					$response['error']['id'] = Configure::read('error.missing_teacher');
					$response['error']['message'] = __($api->missing_teacher);
				} else if (!$token) {
					$response['error']['id'] = Configure::read('error.missing_teacher');
					$response['error']['message'] = $api->error;
				} else if (!$api->validateTeachersId($data['teachers_id'])) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = $api->error;
				} else if ($data['favorited'] && $teacherFavorite) {
					$response['favorited'] = 1;
				} else {
					$userId = $token['id'];
					$teacherId = $data['teachers_id'];
					$response['favorited'] = $this->favorite($data['favorited'], $userId, $teacherId);
				}
			}
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		}
		return json_encode($response);
	}

	private function favorite($type, $userId, $teacherId) {
		$arrData = array();
		if ($type) {
			$arrData['UsersFavorite'] = array(
				'user_id' 	 => $userId,
				'teacher_id' => $teacherId,
			);
			$this->UsersFavorite->save($arrData);
			return 1;
		} else {
			$arrData = array(
				'UsersFavorite.user_id' 	=> $userId,
				'UsersFavorite.teacher_id' 	=> $teacherId,
			);
			$this->UsersFavorite->deleteAll($arrData,false);
			return 0;
		}
	}

}