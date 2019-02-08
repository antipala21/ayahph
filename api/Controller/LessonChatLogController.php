<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTools','Lib');

class LessonChatLogController extends AppController {
	public $uses = array(
			'ChatHistory',
			'Teacher'
		);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index', 'chatLogList');
	}

	public function index() {
		$this->autoRender = false;
		$response = array();

		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
			if (empty($data)) {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request.');

			} elseif (
				!isset($data['users_api_token']) || 
					(
						isset($data['users_api_token']) &&
						empty($data['users_api_token'])
					)
				) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');

			} elseif (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');

			} elseif(
				!isset($data['page']) || 
					(
						isset($data['page']) &&
						empty($data['page'])
					)
				) {
				$response['error']['id'] = Configure::read('error.page_is_required');
				$response['error']['message'] = __('page is required');

			} elseif (
				isset($data['page']) && 
					(
						!is_numeric($data['page']) || 
						$data['page'] == 0 ||
						is_float($data['page'])
					)
				) {
				$response['error']['id'] = Configure::read('error.invalid_page');
				$response['error']['message'] = __('Invalid page');

			} elseif (
				isset($data['teacher_id']) &&
					(
						is_float($data['teacher_id']) ||
						!is_numeric($data['teacher_id'])
					)

				) {
				$response['error']['id'] = Configure::read('error.invalid_teachers_id');
				$response['error']['message'] = __('Invalid teacher_id');

			} elseif (
				isset($data['chat_hash']) && 
					(
						!is_string($data['chat_hash']) ||
						empty($data['chat_hash'])
					)
				) {
				$response['error']['id'] = Configure::read('error.invalid_chat_hash');
				$response['error']['message'] = __('Invalid chat_hash');

			} else {
				$userApiToken = $data['users_api_token'];
				$api = new ApiCommonController();
				$user = $api->findApiToken($userApiToken);
				$chatHash = isset($data['chat_hash'])? $data['chat_hash'] : null;
				if (!is_array($user)) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = $api->error;
					return json_encode($response);
				}

				$user = new UserTable($user);

				//If no teacher_id sent on POSTMAN
				if (!isset($data['teacher_id'])) {
					$data['teacher_id'] = "";
				}
				$data = $this->chatLogPageConditions($user, $data['teacher_id'], $chatHash, $data['page']);
				if ($data) {
					return json_encode($data);
				}
			}
			return json_encode($response);

		}
	}

	private function chatLogPageConditions($user = null, $teacherId = null, $chatHash = null, $page = 1) {
		$response = array();
		$tempArr = array();
		$limit = 20;
		$offset = ($page - 1) * $limit;
		$query = "";
		$conditions = array();

		$conditions['ChatHistory.user_id'] = $user->id;
		$conditions['ChatHistory.member_type !='] = 0;
		$conditions['ChatHistory.message <>'] = '';
		if (!is_null($chatHash)) {
			$conditions['ChatHistory.chat_hash'] = $chatHash;
		} else {
			$conditions['ChatHistory.teacher_id'] = $teacherId;
		}

		$chatLogLists = $this->ChatHistory->useReplica()->find('all', array(
				'fields' => array(
					'ChatHistory.id',
					'ChatHistory.member_type',
					'ChatHistory.created',
					'ChatHistory.message',
					'LessonTrackLog.id',
					'LessonTrackLog.lesson_number'
				),
				'conditions' => $conditions,
				'joins' => array(
					array(
						'table' => 'lesson_track_logs',
						'alias' => 'LessonTrackLog',
						'type' => 'LEFT',
						'conditions' => array('ChatHistory.chat_hash = LessonTrackLog.chat_hash')
					)
				),
				'order' => array('ChatHistory.id DESC'),
				'limit' => $limit + 1,
				'offset' => $offset
			));

		$ctr = 1;
		foreach ($chatLogLists as $chatLogList) {
			$ch = new ChatHistoryTable($chatLogList['ChatHistory']);
			$ltl = new LessonTrackLogTable($chatLogList['LessonTrackLog']);
			if ($ctr <= $limit) {
				$tempArr['chatlog'][] = array(
					'member_type' => $ch->member_type,
					'lesson_number' => isset($ltl->lesson_number) ? $ltl->lesson_number : null,
					'time' => $ch->created,
					'text' => preg_replace('/<("[^"]*"|\'[^\']*\'|[^\'">])*>/', '', $ch->message)
				);
				$ctr++;
			}
		}

		if (!empty($chatLogLists)) {
			$response = $tempArr;
			if (count($chatLogLists) >= $limit) {
				$response['has_next'] = true;
			} else {
				$response['has_next'] = false;
			}
		} else {
			$response['result'] = false;
		}

		return $response;
	}

	public function chatLogList() {
		$this->autoRender = false;
		$response = array();
		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
			if (empty($data)) {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');

			} elseif (
				!isset($data['users_api_token']) || 
					(
						isset($data['users_api_token']) &&
						empty($data['users_api_token'])
					)
				) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');

			} elseif (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');

			} elseif(
				!isset($data['page']) || 
					(
						isset($data['page']) &&
						empty($data['page'])
					)
				) {
				$response['error']['id'] = Configure::read('error.page_is_required');
				$response['error']['message'] = __('page is required');

			} elseif (
				isset($data['page']) && 
					(
						!is_numeric($data['page']) || 
						is_float($data['page']) ||
						$data['page'] == 0
					)
				) {
				$response['error']['id'] = Configure::read('error.invalid_page');
				$response['error']['message'] = __('Invalid page');

			} elseif (isset($data['api_version']) && is_string($data['api_version'])) {
				$response['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$response['error']['message'] = __('api version must not be string');  

			} else {
				$userApiToken = $data['users_api_token'];
				$api = new ApiCommonController();
				$user = $api->findApiToken($userApiToken);

				if (!is_array($user)) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = $api->error;
					return json_encode($response);
				}

				$data['api_version'] = empty($data['api_version']) ? 0 : $data['api_version'];

				$user = new UserTable($user);

				$data = $this->chatLogListPageConditions($user, $data['page']);
				if ($data) {
					return json_encode($data);
				}
			}

			return json_encode($response);
		}
	}


	private function chatLogListPageConditions($user = array(), $page = 1) {
		$response = array();
		$tempArr = array();
		$limit = 20;
		$offset = ($page - 1) * $limit;

		$query = "";

		$query .= "SELECT Teacher.id, Teacher.name, Teacher.jp_name, Teacher.image_url, Teacher.counseling_flg, ChatHistory.id, ChatHistory.chat_hash, ChatHistory.message, ChatHistory.created ";
		$query .= "FROM teachers as Teacher ";
		$query .= "LEFT JOIN chat_histories AS ChatHistory ON ";
		$query .= "(ChatHistory.id = (SELECT id FROM chat_histories WHERE teacher_id = Teacher.id AND user_id = {$user->id} AND member_type != 0 ORDER BY id DESC LIMIT 1)) ";
		$query .= "WHERE Teacher.status = 1 ";
		$query .= "AND ChatHistory.id IS NOT NULL ";
		$query .= "AND ChatHistory.message IS NOT NULL ";
		$query .= "ORDER BY ChatHistory.id DESC ";
		$query .= "LIMIT ".($limit + 1)." ";
		$query .= "OFFSET {$offset}";
		$this->Teacher->openDBReplica();
		$chatLogs = $this->Teacher->query($query);
		$this->Teacher->closeDBReplica();
		$ctr = 1;

		$defaultTeacher = $this->Teacher->getDefaultCounselorDetail();

		//get block id
		$blockList = BlockListTable::getBlocks($user->id);
		
		foreach ($chatLogs as $key => $value) {
			if ($value['Teacher']['counseling_flg']) {
				//to get the ID of the teacher not the default counselor ID
				$counselorTrueData = new TeacherTable($value['Teacher']);
				$defaultTeacher['Teacher']['id'] = $counselorTrueData->id;
				$teacher = new TeacherTable($defaultTeacher['Teacher']);
			} else {
				$teacher = new TeacherTable($value['Teacher']);
			}
			$chatLog = new ChatHistoryTable($value['ChatHistory']);
			if (!empty($chatLog->message) && $ctr <= 20) {

				$checkCountryCode = (!$user->native_language2) ? 'ja' : $user->native_language2;
				$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $teacher->jp_name : '' ;
				
				$tempArr[] = array(
					'teacher_name' => $getUserSettingLanguage,
					'teacher_name_ja' => $teacher->jp_name,
					'teacher_name_eng' => $teacher->name,					
					'teacher_id' => $teacher->id,
					'teacher_image' => $teacher->getImageUrl(),
					'chatlog_last_text' => preg_replace('/<("[^"]*"|\'[^\']*\'|[^\'">])*>/', '', $chatLog->message),
					'datetime' => $chatLog->created,
					'blocked_by_teacher_flg' => (isset($blockList[$teacher->id]) ? 1 : 0)
				);
				$ctr++;
			}

		}
		
		foreach ($tempArr as $index => $value) {
			unset($tempArr[$index]['teacher_name_ja']);
		} 
		
		if (!empty($chatLogs)) {
			$response['chatlog_list'] = $tempArr;
			if (count($chatLogs) >= $limit) {
				$response['has_next'] = true;
			} else {
				$response['has_next'] = false;
			}
		} else {
			$response['result'] = false;
		}

		return $response;
	}
}