<?php
/********************************************
*																						*
*  Reservation message for API 							*
*  Author: John Mart Belamide								*
*	 October 2015															*
*																						*
********************************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class LessonMessagesController extends AppController {

	public $uses = array(
			'Teacher',
			'LessonOnairsLog'
		);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;

		@$data = json_decode($this->request->input(), true);

		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else if (isset($data['page']) && !is_int($data['page'])) {
			$response['error']['id'] = Configure::read('error.page_must_be_integer');
			$response['error']['message'] = __('page must be integer');
		} else if (isset($data['page']) && $data['page'] <= 0) {
			$response['error']['id'] = Configure::read('error.page_must_be_greater_than_zero');
			$response['error']['message'] = __('page must be greater than 0');
		} else if (isset($data['api_version']) && is_string($data['api_version'])) {
			$response['error']['id'] = Configure::read('error.api_version_must_be_integer');
			$response['error']['message'] = __('api version must not be string');  
		} else {

			$apiCommon = new ApiCommonController();

			$user = $apiCommon->validateToken($data['users_api_token']);

			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');
				return json_encode($response);
			}

			$limit 			= 20;
			$page 			= (isset($data['page']) && !empty($data['page']) && (int)$data['page'] >= 1) ? (int)$data['page'] : 1;
			$offset 		= ($page - 1) * $limit;
			$offsetNext = ($page) * $limit;

			$condition 	= array(
				'LessonOnairsLog.user_id' => $user['id'],
				'LessonOnairsLog.lesson_memo_disp_flg'	=> 1,
				'LessonOnairsLog.connect_id !='	=> NULL,
				'LessonOnairsLog.modified !=' => '',
				'LessonOnairsLog.lesson_memo !=' => '',
				'LessonOnairsLog.lesson_memo_sent_time !=' => '',
			);

			$fields = array(
				'LessonOnairsLog.id',
				'Teacher.id',
				'Teacher.jp_name',
				'Teacher.name',
				'Teacher.image_url',
				'Teacher.counseling_flg',
				'LessonOnairsLog.modified',
				'LessonOnairsLog.lesson_memo',
				'LessonOnairsLog.lesson_memo_sent_time',
				'LessonOnairsLog.chat_hash',
				'LessonOnairsLog.student_read_flg',
				'LessonTrackLog.id',
				'LessonTrackLog.lesson_number',
			);

			$joins = array(
				array(
					'type'				=> 'LEFT',
					'table'				=> 'teachers',
					'alias' 			=> 'Teacher',
					'conditions' 	=> array('LessonOnairsLog.teacher_id = Teacher.id')
				),
				array(
					'type' => 'LEFT',
					'table' => 'lesson_track_logs',
					'alias' => 'LessonTrackLog',
					'conditions' => 'LessonOnairsLog.chat_hash = LessonTrackLog.chat_hash'
				),
			);

			$data = $this->LessonOnairsLog->find('all',array(
				'fields'			=>	$fields,
				'conditions'	=>	$condition,
				'joins' 			=>	$joins,
				'limit' 			=>	$limit,
				'offset'			=>	$offset,
				'order'				=>	array('LessonOnairsLog.lesson_memo_sent_time DESC')
				)
			);

			if (!$data) {
				return null;
			} else {

				// check if has next
				// NC-3332 fix hasnext.
				$hasNext = $this->LessonOnairsLog->find('all', array(
					'conditions' =>	$condition,
					'offset' => $offsetNext,
					'limit' => $limit,
					'fields' => array('LessonOnairsLog.id'),
					'order' =>	array('LessonOnairsLog.lesson_memo_sent_time DESC')
				));
				
				// parse as boolean
				// NC-3332 check if hasNext has data.
				$hasNext = (count($hasNext) > 0) ? true : false;

			}

			$response['messages'] = array();
			$response['has_next'] = $hasNext;

			$counselorDetail = $this->Teacher->getDefaultCounselorDetail();
			
			//get teacher_id
			$blockList = BlockListTable::getBlocks($user['id']);

			// Put all result in a proper associate name
			foreach ($data as $key => $row) {
				$teacherTable = new TeacherTable($row['Teacher']);

				$lesson = json_decode($row['LessonOnairsLog']['lesson_memo'],true);

				$todaysLesson = $row['LessonOnairsLog']['lesson_memo'];
				if ( isset($lesson['message_1']) ) {
					$todaysLesson = $this->getFirstIndexValue($lesson['message_1']);
				}
				//display default detail for counselor 
				if ($teacherTable->counseling_flg) {
					$teacherTable = new TeacherTable($counselorDetail['Teacher']);
				}

				$goodPoints = isset($lesson['message_2']) ? $this->getNoneEmptyMessage($lesson['message_2']) : null;
				$reviews = isset($lesson['message_3']) ? $this->getNoneEmptyMessage($lesson['message_3']) : null;
				$others = isset($lesson['message_4']) ? $this->getFirstIndexValue($lesson['message_4']) : null;
				$isRead = ($row['LessonOnairsLog']['student_read_flg'] == 1) ? true : false;
				$message = array(
					"lesson_number" => $row['LessonTrackLog']['lesson_number'],
					"lesson_id" => (int)$row['LessonOnairsLog']['id'],
					"teacher_id" => $teacherTable->id,
					"teacher_name_eng" => $teacherTable->name,
					"teacher_image" => $teacherTable->getImageUrl(),
					"created_date" => $row['LessonOnairsLog']['lesson_memo_sent_time'],
					"todays_lesson" => $todaysLesson,
					"is_read" => $isRead,
					"good_points" => $goodPoints,
					"review" => $reviews,
					"others" => $others,
					"chathash" => $row['LessonOnairsLog']['chat_hash'],
					"blocked_by_teacher_flg" => (isset($blockList[$teacherTable->id]) ? 1 : 0)
				);
				//if api version is greater than or equal to 17
				$checkCountryCode = (!$user['native_language2']) ? 'ja' : $user['native_language2'];
				$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $teacherTable->jp_name : '' ;
				$message["teacher_name"] = $getUserSettingLanguage;
				
				unset($message['teacher_name_ja']);
				$message['counseling_flg'] = intval($teacherTable->counseling_flg);
				array_push($response['messages'],$message);
			}
		}

		return json_encode($response);
	}

	/**
	* Get value of first index in an array
	* @param array $data
	* @return string $message
	*/

	public function getFirstIndexValue($data) {
		@$message = $data[0] ? $data[0] : null;
		return $message;
	}

	/**
	* Get none empty value of an array
	* @param array $data
	* @return array $messages
	*/
	public function getNoneEmptyMessage($data) {
		$messages = array();

		foreach($data as $message) {
			if (!empty($message)) {
				$messages[] = $message;
			}
		}

		return $messages ? $messages : null;
	}

}
