<?php
/**
* NC-3877 Reservation create of counselor teacher only for API
* @param required users_api_token, begin_at
* @return created => true, teacher_id => ?
*/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class CounselingReservationController extends AppController {
	public $uses = array(
		'Teacher',
		'LessonSchedule',
		'Textbook',
		'User',
		'Counseling'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {

		$this->autoRender = false;

		if ($this->request->is('post')) {

			// request data.
			$request = @json_decode($this->request->input(),true);

			$apiCommon = new ApiCommonController();

			// trap the reqeust data...
			if(!$request) {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
			} elseif(!isset($request['begin_at']) || empty($request['begin_at'])) {
				$response['error']['id'] = Configure::read('error.begin_at_is_required');
				$response['error']['message'] = __('begin_at is required');
			} elseif (!isset($request['users_api_token']) || empty($request['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			} elseif (!is_string($request['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['error']['message'] = __('users_api_token must be string');
			} else if (isset($request['api_version']) && is_string($request['api_version'])) {
				$response['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$response['error']['message'] = __('api version must not be string');
			} else {
				// NC-4789 questionnaire added in counseling_create
				$isQuestionnaire = false;
				
				if (!$request['questionnaire_items']) {
					$response['error']['id'] = Configure::read('error.invalid_request');
					$response['error']['message'] = __('Invalid request');
					return json_encode($response);
				} else {
					$items = $request['questionnaire_items'];
					if (!isset($items['answer_main_target']) || empty($items['answer_main_target'])) {
						$response['error']['id'] = Configure::read('error.answer_main_target_is_required');
						$response['error']['message'] = __('answer_main_target is required');
						return json_encode($response);
					} elseif (!is_int($items['answer_main_target'])) {
						$response['error']['id'] = Configure::read('error.answer_main_target_must_be_integer');
						$response['error']['message'] = __('answer_main_target must be integer');
						return json_encode($response);
					} elseif (!isset($items['answer_occupation']) || empty($items['answer_occupation'])) {
						$response['error']['id'] = Configure::read('error.answer_occupation_is_required');
						$response['error']['message'] = __('answer_occupation is required');
						return json_encode($response);
					} elseif (!is_int($items['answer_occupation'])) {
						$response['error']['id'] = Configure::read('error.answer_occupation_must_be_integer');
						$response['error']['message'] = __('answer_occupation must be integer');
						return json_encode($response);
					} elseif (!isset($items['answer_exp_abroad_flg'])) {
						$response['error']['message'] = __('answer_exp_abroad_flg is required');
						return json_encode($response);
					} elseif (!is_int($items['answer_exp_abroad_flg'])) {
						$response['error']['message'] = __('answer_exp_abroad_flg must be integer');
						return json_encode($response);
					} elseif (!isset($items['answer_english_school_career']) || empty($items['answer_english_school_career'])) {
						$response['error']['id'] = Configure::read('error.answer_english_school_career_is_required');
						$response['error']['message'] = __('answer_english_school_career is required');
						return json_encode($response);
					} elseif (!is_int($items['answer_english_school_career'])) {
						$response['error']['id'] = Configure::read('error.answer_english_school_career_must_be_integer');
						$response['error']['message'] = __('answer_english_school_career must be integer');
						return json_encode($response);
					} elseif (!isset($items['answer_physical_english_school_career']) || empty($items['answer_physical_english_school_career'])) {
						$response['error']['id'] = Configure::read('error.answer_physical_english_school_career_is_required');
						$response['error']['message'] = __('answer_physical_english_school_career is required');
						return json_encode($response);
					} elseif (!is_int($items['answer_physical_english_school_career'])) {
						$response['error']['id'] = Configure::read('error.answer_physical_english_school_career_must_be_integer');
						$response['error']['message'] = __('answer_physical_english_school_career must be integer');
						return json_encode($response);
					} elseif (
						(!isset($items['answer_goal']) || empty($items['answer_goal'])) 
						&& (!isset($items['answer_to_do']) || empty($items['answer_to_do']))
					) {
						$response['error']['id'] = Configure::read('error.answer_goal_is_required');
						$response['error']['message'] = __('answer_goal is required');
						return json_encode($response);
					} elseif (!empty($items['answer_to_do']) && !is_string($items['answer_to_do'])) {
						$response['error']['id'] = Configure::read('error.answer_to_do_must_be_string');
						$response['error']['message'] = __('answer_to_do must be string');
						return json_encode($response);
					} elseif (!empty($items['answer_goal']) && is_array($items['answer_goal']) && $items['answer_goal'] != array_filter($items['answer_goal'], 'is_numeric')) {
						$response['error']['id'] = Configure::read('error.answer_goal_must_be_integer');
						$response['error']['message'] = __('answer_goal must be integer');
						return json_encode($response);
					} elseif (!empty($items['answer_goal']) && !is_array($items['answer_goal']) && !is_int($items['answer_goal'])) {
						$response['error']['id'] = Configure::read('error.answer_goal_must_be_integer');
						$response['error']['message'] = __('answer_goal must be integer');
						return json_encode($response);
					} elseif (isset($items['answer_eiken']) && !is_int($items['answer_eiken'])) {
						$response['error']['id'] = Configure::read('error.answer_eiken_must_be_integer');
						$response['error']['message'] = __('answer_eiken must be integer');
						return json_encode($response);
					} elseif (isset($items['answer_purpose']) && !is_int($items['answer_purpose'])) {
						$response['error']['id'] = Configure::read('error.answer_purpose_must_be_integer');
						$response['error']['message'] = __('answer_purpose must be integer');
						return json_encode($response);
					} elseif (isset($items['answer_toeic']) && !is_int($items['answer_toeic'])) {
						$response['error']['id'] = Configure::read('error.answer_toeic_must_be_integer');
						$response['error']['message'] = __('answer_toeic must be integer');
						return json_encode($response);
					} elseif (isset($items['answer_by_when']) && !is_int($items['answer_by_when'])) {
						$response['error']['id'] = Configure::read('error.answer_by_when_must_be_integer');
						$response['error']['message'] = __('answer_by_when must be integer');
						return json_encode($response);
					} else {
						$isQuestionnaire = true;
					}
				}
				
				// check time format before finding a teacher.
				$dateAndTime = $request['begin_at'];
				$dateAndTime = substr($dateAndTime,0,17).'00';
				$minutes = substr($dateAndTime,14,5);

				if ($minutes != '30:00' && $minutes != '00:00') {
					$response['error']['id'] = Configure::read('error.invalid_time_format');
					$response['error']['message'] = __('Invalid time format');
					return json_encode($response);
				}
				if (strtotime($dateAndTime) < time()) {
					$response['error']['id'] = Configure::read('error.reservation_time_has_already_passed');
					$response['error']['message'] = __('Reservation time has already passed');
					return json_encode($response);
				}

				// user api token check
				$User = $apiCommon->validateToken($request['users_api_token']);
				$apiVersion = isset($request['api_version']) ? $request['api_version'] :0;


				if (!$User || empty($User['id'])) { // trap the user ivalid api token
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = __('Invalid users_api_token');
					return json_encode($response);
				} else {
					$this->userId = $User['id'];
				}

				// check reservation limit for user 
				// 20/day and 4/day for all counselor reservation
				$totalReservation = $this->LessonSchedule->countUserTotalReservation(array('userId' => $this->userId));
				$totalReservationCounselor = $this->LessonSchedule->countUserReservationForTeacherCounselor(array(
					'userId' => $this->userId,
					'lessonDate' => date('Y-m-d', strtotime($request['begin_at']))
				));

				if ($totalReservation >= 20 || $totalReservationCounselor >= 4) {
					$response['error']['id'] = Configure::read('error.the_all_reservation_limit_has_been_reached');
					$response['error']['message'] =__('All of your reservation limit has been reached.');
					return json_encode($response);
				}
				
				// //if user already attended the free counseling set counselor reservation free flg OFF
				$userAttendedFreeCounseling = $apiCommon->checkUserAttendedFreeCounseling(array(
					'userId' => $this->userId,
					'nextChargeDate' => $User['next_charge_date']
				));
				
				if ($userAttendedFreeCounseling || !$User['counseling_attended_flg']) {
					$this->User->read(array('counseling_attended_flg'), $this->userId);
					$this->User->validate = false;
					$this->User->set(array('counseling_attended_flg' => 1));
					$this->User->save();
				}

				// get counselor data
				$Teacher = $this->Teacher->find('first', array(
					'fields' => array(
						'Teacher.id',
						'Teacher.rank_coin_id',
						'Teacher.counseling_flg',
						'Teacher.counselor_order',
						'ShiftWorkon.id',
						'ShiftWorkon.teacher_id',
						'ShiftWorkon.lesson_time',
						'LessonSchedule.id',
						'LessonSchedule.teacher_id',
						'LessonSchedule.lesson_time'
					),
					'joins' => array(
						array(
							'table' => 'shift_workons',
							'alias' => 'ShiftWorkon',
							'type' => 'LEFT',
							'conditions' => array(
									'ShiftWorkon.lesson_time' => $request['begin_at'],
									'Teacher.id = ShiftWorkon.teacher_id'
							)
						),
						array(
							'table' => 'lesson_schedules',
							'alias' => 'LessonSchedule',
							'type' => 'LEFT',
							'conditions' => array(
									'LessonSchedule.lesson_time' => $request['begin_at'],
									'LessonSchedule.teacher_id = Teacher.id'
							)
						)
					),
					'conditions' =>  array(
						'Teacher.counseling_flg' => 1,
						'Teacher.counselor_order IS NOT NULL',
						'LessonSchedule.id' => NULL,
						'ShiftWorkon.id IS NOT NULL',
					),
					'group' => 'Teacher.id',
					'order' => 'Teacher.counselor_order ASC, Teacher.id ASC',
					'recursive' => -1
				));

				// log
				$this->log(__METHOD__ . ' [Teacher Counselor DATA ] => ' . json_encode($Teacher), 'debug');

				// trap teacher counselor and User
				if (!$Teacher || empty($Teacher['Teacher']['id'])) { // no counselor available
					$response['error']['id'] = 'no_counselor_available'; //@TODO create constant id
					$response['error']['message'] = __('No counselor available');
				} elseif ($Teacher) {

					// set needed vars
					$teacherId = $Teacher['Teacher']['id'];

					// validate time
					$this->LessonSchedule->validate['begin_at'] = array(
						'rule' => array('datetime'),
						'message' => __('Invalid date format')
					);
					$this->LessonSchedule->set(array('begin_at' => $dateAndTime));

					// if validation fails
					if (!$this->LessonSchedule->validates() || !$apiCommon->validateDate($dateAndTime)) {
						$response['error']['id'] = Configure::read('error.invalid_date_format');
						$response['error']['message'] = __('Invalid date format');
					} else {

						// log
						$this->log(__METHOD__ . ' [**** START PROCESS **** Schedule Time is validated]', 'debug');

						// set connectId always free talk 1004
						$connectId = Configure::read('counselor.connect_id');

						// check allowed textbook
						$getTextbookArr = array(
							'select_method' => 'first',
							'teacher_id' => $teacherId,
							'env_flag' => 'reservation',
							'user_id' => $this->userId,
							'auto_select' => 'off',
							'connect_id' => $connectId,
							'counselor_flag' => true // for this feature only
						);

						// log
						$this->log(__METHOD__ . ' [getTextbookArr] => ' . json_encode($getTextbookArr), 'debug');

						$textbookData = $this->Textbook->getTextbooks($getTextbookArr);

						// log
						$this->log(__METHOD__ . ' [textbookData] => ' . json_encode($textbookData), 'debug');

						$textbook = isset($textbookData['res_data']) ? $textbookData['res_data'] : null;

						if ($textbook) {
							$categoryType = $textbook['TextbookCategory']['textbook_category_type'];
							//NC-3344
							$categoryName = $textbook['TextbookCategory']['name'];
						
						} else {
							$response['error']['id'] = Configure::read('error.invalid_connect_id');
							$response['error']['message'] = 'Invalid connect_id';
							// log
							$this->log(__METHOD__ . ' [NO Textbook data] connectId => ' . $connectId . ' response => ' . json_encode($response), 'debug');
							return json_encode($response);
						}

						// save reserve data
						$reserveData = array(
							'user_id' => $this->userId,
							'teacher_id' => $teacherId, // teachre ID
							'reserve_time' => $request['begin_at'],
							'connect_id' => $connectId,
							'counseling_flg' => true,
							'counseling_attended_flg' => intval($User['counseling_attended_flg']),
							'reserve_coin' => '0', // coin for counselor reservation always 0
							'category_type' => $categoryType, //used for UsersPoint
							'category_name' => $categoryName,
							'chapter_name' => null,
							'api_version' => $apiVersion
						);

						$reserveData['is_application'] = true;
						
						if ($isQuestionnaire) {
							$reserveData['counseling_pc_reservation'] = true;
						}

						// log
						$this->log(__METHOD__ . '[reserveData] => ' . json_encode($reserveData), 'debug');

						// add the reservation data.
						$res = @$this->LessonSchedule->addReserve($reserveData);

						if (isset($res['lessonSchedId']) && $isQuestionnaire) {
							$counselingScheduleId = $res['lessonSchedId'];
							$answer_goal = !empty($items['answer_goal']) ? (is_array($items['answer_goal']) ? implode(',', $items['answer_goal']) : $items['answer_goal']) : '';
								
								$data = array(
					                'user_id' => $this->userId,
									'teacher_id' => $teacherId,
					                'lesson_schedule_id' => $counselingScheduleId,
									'lesson_time' => $request['begin_at'],
									'consultation_detail' => $items['answer_main_target'],
									'occupation' => $items['answer_occupation'],
									'exp_abroad_flg' => $items['answer_exp_abroad_flg'],
									'purpose' => isset($items['answer_purpose']) ? $items['answer_purpose'] : null,
									'english_school_career' => $items['answer_english_school_career'],
									'english_physical_school_career' => $items['answer_physical_english_school_career'],
									'eiken' => isset($items['answer_eiken']) ? $items['answer_eiken'] : null,
									'toeic' => isset($items['answer_toeic']) ? $items['answer_toeic'] : null,
									'other_score' => isset($items['answer_other_score']) ? $items['answer_other_score'] : '',
									'by_when' => isset($items['answer_by_when']) ? $items['answer_by_when'] : null,
									'goal' => $answer_goal,
									'to_do' => isset($items['answer_to_do']) ? $items['answer_to_do'] : null
								);

								$this->Counseling->create();
								$this->Counseling->set($data);
								$this->Counseling->save();

								$res = 1;
						}
						
						switch ($res) {
							case '1':
								$response = array('created' => true, 'teacher_id' => $teacherId);
								break;
							case '-1':
								//'予約済み
								$response['error']['id'] = Configure::read('error.the_begin_time_you_specified_is_already_scheduled');
								$response['error']['message'] =__('The begin time you specified is already scheduled.');
								break;
							case '-5':
								$response['error']['id'] = Configure::read('error.within_the_duration_of_the_campaign_period_is_you_can_only_reserve_once');
								$response['error']['message'] = __('Within the duration of the Campaign  Period is, you can only reserve once');
								break;
							case '-4':
								$response['error']['id'] = Configure::read('error.schedule_is_not_available_for_reservation');
								$response['error']['message'] = __('Schedule is not available for reservation.');
								break;
							case '-3':
								$response['error']['id'] = Configure::read('error.you_do_not_have_enough_coins_for_reservation');
								$response['error']['message'] = __("You do not have enough coins for reservation.");
								break;
							case '-2':
								$response['error']['id'] = Configure::read('error.reservation_is_possible_until_ten_minutes_before');
								$response['error']['message'] = __("It could not be reserved. Reservation is possible until 10 minutes before.");
								break;
							default:
								$response['error']['id'] = Configure::read('error.the_begin_time_you_specified_is_already_scheduled');
								$response['error']['message'] =__('The begin time you specified is already scheduled.');
								break;
						}

					}
				} else {
					$response['error']['id'] = Configure::read('error.invalid_request');
					$response['error']['message'] = __('Invalid request');
				}
			}

			// log
			$this->log(__METHOD__  . '[ **RESPONSE** ] => '. json_encode($response), 'debug');
			return json_encode($response);
		}
	}

}