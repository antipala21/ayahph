<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class LessonMessagesFinishReadController extends AppController {
	public $uses = array(
		'LessonOnairsLog',
		'Notification',
		'NotificationsRead'
	);

	public function beforeFilter() {
		$this->Auth->allow('index');
		parent::beforeFilter();
	}

	public function index() {
		$this->autoRender = false;

		$data = json_decode($this->request->input(), true);
		$response = array();
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required.');
		} else {

			$apiCommon = new ApiCommonController();

			$user = $apiCommon->validateToken($data['users_api_token']);
			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');
				return json_encode($response);
			}

			if (isset($data['all_read_flg'])) {
				if (!is_bool($data['all_read_flg'])) {
					$response['error']['id'] = Configure::read('error.all_read_flg_must_be_boolean');
					$response['error']['message'] = __('all_read_flg must be boolean');
					return json_encode($response);
				}

				if ($data['all_read_flg']) {
					// get logs
					$lessonLogs = $this->LessonOnairsLog->find('all', array(
						'recursive' => -1,
						'fields' => array('id'),
						'conditions' => array(
							'LessonOnairsLog.user_id' => $user['id'],
							'LessonOnairsLog.connect_id !=' => NULL,
							'LessonOnairsLog.teacher_id !=' => NULL,
							'LessonOnairsLog.student_read_flg !=' => 1,
							'LessonOnairsLog.lesson_memo_disp_flg' => 1,
							'LessonOnairsLog.lesson_memo !=' => '',
							'LessonOnairsLog.lesson_memo_sent_time !=' => '',
						)
					));
					
					// if has any logs
					if ($lessonLogs) {
						foreach ($lessonLogs as $lessonLog) {
							// see/read lesson messages
							$this->LessonOnairsLog->seeReadMessages(array(
								'lessonId' => $lessonLog['LessonOnairsLog']['id'],
								'userId' => $this->sharedUserData['User']['id'],
								'viewType' => 'all'
							));
						}
					}
				}
			} 

			if (isset($data['chat_hash']) && empty($data['all_read_flg'])) {
				if (empty($data['chat_hash'])) {
					$response['error']['id'] = Configure::read('error.invalid_chat_hash');
					$response['error']['message'] = __('Invalid chat_hash');
					return json_encode($response);
				} else if (!is_string($data['chat_hash'])) {
					$response['error']['id'] = Configure::read('error.chat_hash_must_be_string');
					$response['error']['message'] = __('chat_hash must be string');
					return json_encode($response);
				}

				$onAir = $this->LessonOnairsLog->findByChatHash($data['chat_hash']);
				if (!isset($onAir['LessonOnairsLog'])) {
					$response['error']['id'] = Configure::read('error.invalid_chat_hash');
					$response['error']['message'] = __('Invalid chat_hash');
					return json_encode($response);
				}

				if (@$onAir['LessonOnairsLog']['chat_hash'] !== $data['chat_hash']) {
					$response['error']['id'] = Configure::read('error.invalid_chat_hash');
					$response['error']['message'] = __('Invalid chat_hash');
					return json_encode($response);
				}
				
				// update onair
				if ($onAir){
					// see/read lesson messages
					$this->LessonOnairsLog->seeReadMessages(array(
						'lessonId' => $onAir['LessonOnairsLog']['id'],
						'userId' => $user['id'],
						'viewType' => 'all'
					));
				}
			}

			// get the number of unread messages!
			$unreadMessagesCount = $this->LessonOnairsLog->countUnseenUnreadMessages(array('userId' => $user['id'], 'viewType' => 'read'));
			$response['unread_message_count'] = $unreadMessagesCount;
		}

		return json_encode($response);
	}
}