<?php
/********************************
*																*
*	Lesson Start for API 					*
*	Author: John Mart Belamide		*
*	August 2015										*
*																*
********************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTools','Lib');

class LessonStartController extends AppController {
	public $uses = array(
	  'LessonOnair',
	  'LessonOnairsLog',
	  'LessonSchedule',
	  'Teacher',
	  'UserFirstLesson'
	);
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			array(
				'index',
				'counselingStart'
			)
		);
	}
	public function index() {
    $this->autoRender = false;

    $response = array();
		if ($this->request->is('post')) {
	    $data = json_decode($this->request->input(), true);
	    if (empty($data)) {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
      } else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
      	$response['error']['id'] = Configure::read('error.users_api_token_is_required');
      	$response['error']['message'] = __('users_api_token is required');
      } else if (!is_string($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['error']['message'] =__('users_api_token must be string');
      } else if (!isset($data['teachers_id']) || empty($data['teachers_id'])) {
      	$response['error']['id'] = Configure::read('error.teachers_id_is_required');
      	$response['error']['message'] = __('teachers_id is required');
	    } else {
	      $user_api_token = $data['users_api_token'];
	      $teacher_id = $data['teachers_id'];
	      $api = new ApiCommonController();
	      $user = $api->findApiToken($user_api_token);

	      if (!$user) {
          $response['error']['id'] = Configure::read('error.invalid_api_token');
          $response['error']['message'] = $api->error;
	      } else if (!$api->validateTeachersId($teacher_id)) {
          $response['error']['id'] = Configure::read('error.invalid_teachers_id');
          $response['error']['message'] = $api->error;
	      } else {
	        $user_id = $user['id'];
	        $userTable = new UserTable($user);
	        $membership = $userTable->getMembership();
	        $adminFlag = $user['admin_flg'];

	        $onAir = $this->LessonOnair->findByTeacherIdAndConnectFlg($teacher_id,1);

	        $conflict = $this->hasConflict($teacher_id);

	        $scheduleuserId = null;

	        if ( isset($onAir['LessonOnair']) ) {
	        	$date = date('Y-m-d');
	        	$minutes = date('i');
	        	$minutes = $minutes < 30 ? '00' : '30';
	        	$datetime = $date.' '.date('H').':'.$minutes.':00';
	        	$conditions = array(
	        		'LessonSchedule.lesson_time' => $datetime,
	        		'LessonSchedule.teacher_id' => $teacher_id
	        	);
		        $schedule = $this->LessonSchedule->find('first',array(
		        	'fields' => array(
		        		'LessonSchedule.id',
		        		'LessonSchedule.user_id',
		        		'LessonSchedule.teacher_id'
		        		),
		        	'conditions' => $conditions
		        	)
		        );
		        $scheduleuserId	= isset($schedule['LessonSchedule']) ? $schedule['LessonSchedule']['user_id'] : null;
		      }


	        if ($api->checkBlocked($data['teachers_id'],$user['id'])) {
	        	$response['error']['id'] = Configure::read('error.missing_teacher');
	        	$response['error']['message'] =  __($api->missing_teacher);
	        } else if (empty($onAir['LessonOnair'])){
	        	$response['error']['id'] = Configure::read('error.this_teacher_is_not_onair');
	        	$response['error']['message'] = __('This teacher is not onair.');
	        } else if ($onAir['LessonOnair']['status'] == 2 && $scheduleuserId !== $user_id) {
	        	$response['error']['id'] = Configure::read('error.teacher_has_already_been_reserved');
	        	$response['error']['message'] =  __('Teacher has already been reserved.');
	        } else if ($onAir['LessonOnair']['status'] == 3 && $onAir['LessonOnair']['user_id'] !== $user_id) {
	        	$response['error']['id'] = Configure::read('error.teacher_is_during_lesson');
	        	$response['error']['message'] =  __('Teacher is during lesson');
	        } else if ($conflict['conflict']) {
	        	$response['error']['id'] = Configure::read('error.schedule_is_conflict');
	        	$response['error']['message'] =  __("There is a schedule on ").$conflict['end_time'];
	        } else if ($adminFlag != 1 && $membership != '有料会員（クレジット認証済）') {
	        	$response['error']['id'] = Configure::read('error.only_paid_user_or_admin_flag_1_can_start_a_lesson');
	        	$response['error']['message'] =  __('Only paid user or admin flag 1 can start a lesson');
	        } else if ($this->onLesson($user_id, $teacher_id)) {
	        	$response['error']['id'] = Configure::read('error.you_have_entered_another_lesson');
	        	$response['error']['message'] =  __('You have entered another lesson.');
	        } else {

				/** NC-3966 **/

				// check if heavy user
				if ($userTable->heavy_user_flg) {
					$cantLesson = $this->LessonOnairsLog->checkIfHeavyUserCantSuddenLesson($userTable->id);

					if ($cantLesson) {
						$response['error']['id'] = Configure::read('error.user_sudden_lesson_time_limit');
						$response['error']['message'] = __('user sudden lesson time limit');
						return json_encode($response);
					}
				}
				/** NC-3966 end **/

				// set action
				$param = array(
					'method' => APP_DIR.' | '.__METHOD__,
					'action_by' => 1
				);

				// NC-2706: add rank_coin_id and home_flg in param if home based teacher
				$teacherData = $this->Teacher->find('first', array(
					'fields' => array(
						'Teacher.rank_coin_id',
						'Teacher.home_flg'
					),
					'conditions' => array(
						'Teacher.id' => $teacher_id,
						'Teacher.home_flg' => 1
					),
					'recursive' => -1
				));

				if ($teacherData) {
					$param['rankId'] = $teacherData['Teacher']['rank_coin_id'];
					$param['homeFlag'] = $teacherData['Teacher']['home_flg'];
				}
				/* end */

				// start lesson
				$LessonOnairTable = LessonOnairTable::studentStart($teacher_id, $user_id, $param);
				if (!isset($LessonOnairTable['LessonOnair'])) {
					$response['error']['id'] = Configure::read('error.this_teacher_is_not_onair');
					$response['error']['message'] = __('This teacher is not onair.');
					return json_encode($response);
				}
				
				if (!is_array($LessonOnairTable)) {
					$response['error']['id'] = Configure::read('error.lesson_onair_error');
					$response['error']['message'] = __($LessonOnairTable);
					return json_encode($response);
				}
				
				$this->log("API LessonStart: ".json_encode($LessonOnairTable['LessonOnair']), 'debug');
				
				// NC-4098 check if has first lesson date.
				$has_first_lesson = $this->UserFirstLesson->checkFirstLesson(array('user_id' => $user_id));

				$user_first_lesson_data = array(
					'teacher_id' => $teacher_id,
					'start_time' => date('Y-m-d H:i:s'),
					'chat_hash' => $LessonOnairTable['LessonOnair']['chat_hash']
				);

				$this->UserFirstLesson->clear();
				// add or update first lesson.
				if (empty($has_first_lesson)) {
					$this->UserFirstLesson->create();
					$user_first_lesson_data['user_id'] = $user_id;
					$this->UserFirstLesson->set($user_first_lesson_data);
					$this->UserFirstLesson->save();
				} else if($has_first_lesson && is_null($has_first_lesson['UserFirstLesson']['start_time'])) {
					$this->UserFirstLesson->read(array('id'), $has_first_lesson['UserFirstLesson']['id']);
					$this->UserFirstLesson->set($user_first_lesson_data);
					$this->UserFirstLesson->save();
				}

				// lesson count
				$lessonOnairsCount = $this->LessonOnairsLog->countOwnLessons($user_id, $teacher_id);
				
				// response
				$response = array(
					'chat_hash' => $onAir['LessonOnair']['chat_hash'],
					'onair_id' => $onAir['LessonOnair']['id'],
					'lesson_count' => $lessonOnairsCount,
					'lesson_type' => $LessonOnairTable['LessonOnair']['lesson_type']
				);
          }
        } 
	    }
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		}

		// return response
		return json_encode($response);
	}

	public function counselingStart() {
		$this->autoRender = false;
		$response = array();
		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
			$teacherCounselorId = ( $this->checkReservationCounselorTeacher($data['users_api_token']) != 0 ) ? (int)$this->checkReservationCounselorTeacher($data['users_api_token']) : (int)$this->getAvailableCounselorTeacher();
			if (empty($data)) {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
			} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			} else if (!is_string($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['error']['message'] =__('users_api_token must be string');
			} else if ( $teacherCounselorId == 0) {
				$response['error']['id'] = Configure::read('error.counselor_not_available');
				$response['error']['message'] = __('There is no available counselor teacher.');
			} else {
					$user_api_token = $data['users_api_token'];
					$teacher_id = $teacherCounselorId;
					$api = new ApiCommonController();
					$user = $api->findApiToken($user_api_token);

				if (!$user) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = $api->error;
				} else if (!$api->validateTeachersId($teacher_id)) {
					$response['error']['id'] = Configure::read('error.invalid_teachers_id');
					$response['error']['message'] = $api->error;
				} else {
					$user_id = $user['id'];
					$userTable = new UserTable($user);
					$membership = $userTable->getMembership();
					$adminFlag = $user['admin_flg'];
					$onAir = $this->LessonOnair->findByTeacherIdAndConnectFlg($teacher_id,1);
					$conflict = $this->hasConflict($teacher_id);
					$scheduleuserId = null;

					if ( isset($onAir['LessonOnair']) ) {
						$date = date('Y-m-d');
						$minutes = date('i');
						$minutes = $minutes < 30 ? '00' : '30';
						$datetime = $date.' '.date('H').':'.$minutes.':00';
						$conditions = array(
							'LessonSchedule.lesson_time' => $datetime,
							'LessonSchedule.teacher_id' => $teacher_id
						);
						$schedule = $this->LessonSchedule->find('first',array(
								'fields' => array(
									'LessonSchedule.id',
									'LessonSchedule.user_id',
									'LessonSchedule.teacher_id'
								),
								'conditions' => $conditions
							)
						);
						$scheduleuserId	= isset($schedule['LessonSchedule']) ? $schedule['LessonSchedule']['user_id'] : null;
					}
					
					if ($api->checkBlocked($teacherCounselorId,$user['id'])) {
						$response['error']['id'] = Configure::read('error.missing_teacher');
						$response['error']['message'] =  __($api->missing_teacher);
					} else if (empty($onAir['LessonOnair'])){
						$response['error']['id'] = Configure::read('error.this_teacher_is_not_onair');
						$response['error']['message'] = __('This teacher is not onair.');
					} else if ($onAir['LessonOnair']['status'] == 2 && $scheduleuserId !== $user_id) {
						$response['error']['id'] = Configure::read('error.teacher_has_already_been_reserved');
						$response['error']['message'] =  __('Teacher has already been reserved.');
					} else if ($onAir['LessonOnair']['status'] == 3 && $onAir['LessonOnair']['user_id'] !== $user_id) {
						$response['error']['id'] = Configure::read('error.teacher_is_during_lesson');
						$response['error']['message'] =  __('Teacher is during lesson');
					} else if ($conflict['conflict']) {
						$response['error']['id'] = Configure::read('error.schedule_is_conflict');
						$response['error']['message'] =  __("There is a schedule on ").$conflict['end_time'];
					} else if ($adminFlag != 1 && $membership != '有料会員（クレジット認証済）') {
						$response['error']['id'] = Configure::read('error.only_paid_user_or_admin_flag_1_can_start_a_lesson');
						$response['error']['message'] =  __('Only paid user or admin flag 1 can start a lesson');
					} else if ($this->onLesson($user_id, $teacher_id)) {
						$response['error']['id'] = Configure::read('error.you_have_entered_another_lesson');
						$response['error']['message'] =  __('You have entered another lesson.');
					} else { 

						// set action
						$param = array(
							'method' => APP_DIR.' | '.__METHOD__,
							'action_by' => 1
						);

						// NC-2706 add rank_coin_id and home_flg in param if home based teacher
						$teacherData = $this->Teacher->find('first', array(
							'fields' => array(
								'Teacher.rank_coin_id',
								'Teacher.home_flg'
							),
							'conditions' => array(
								'Teacher.id' => $teacher_id,
								'Teacher.home_flg' => 1
							),
							'recursive' => -1
						));

						if ($teacherData) {
							$param['rankId'] = $teacherData['Teacher']['rank_coin_id'];
							$param['homeFlag'] = $teacherData['Teacher']['home_flg'];
						}

						// NC-3873: check first come first serve
						if ( $teacher_id ) {
							$uniqKeyPattern = "onairFCFS_".$teacher_id;
							App::uses('myMemcached', 'Lib');
							$memcached = new myMemcached();
							if( $memcached->get($uniqKeyPattern) ) {
								$response['error']['id'] = "this_teacher_is_already_in_lesson";
								$response['error']['message'] = __('This teacher is already in lesson.');
								return json_encode($response);
							} else {
								$memcached->set(array(
									'key' => $uniqKeyPattern,
									'value' => 1,
									'expire' => 60 // 1 minute
								));
							}
						}
						// start lesson
						$LessonOnairTable = LessonOnairTable::studentStart($teacher_id, $user_id, $param);
						if (!isset($LessonOnairTable['LessonOnair'])) {
							$response['error']['id'] = Configure::read('error.this_teacher_is_not_onair');
							$response['error']['message'] = __('This teacher is not onair.');
							return json_encode($response);
						}

						if (!is_array($LessonOnairTable)) {
							$response['error']['id'] = Configure::read('error.lesson_onair_error');
							$response['error']['message'] = __($LessonOnairTable);
							return json_encode($response);
						}

						$this->log("API LessonStart: ".json_encode($LessonOnairTable['LessonOnair']), 'debug');

						// lesson count
						$lessonOnairsCount = $this->LessonOnairsLog->countOwnLessons($user_id, $teacher_id);

						// response
						$response = array(
							'chat_hash' => $onAir['LessonOnair']['chat_hash'],
							'onair_id' => $onAir['LessonOnair']['id'],
							'lesson_count' => $lessonOnairsCount,
							'lesson_type' => $LessonOnairTable['LessonOnair']['lesson_type'],
							'teacher_id' => $onAir['LessonOnair']['teacher_id'],
							'connect_id' => Configure::read('counselor.connect_id')
						);
					}
				}
			}
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		}

		// return response
		return json_encode($response);
	}

	public function onLesson($user_id, $teacher_id){
		$onAir = $this->LessonOnair->find('first',array(
			'fields' => array('LessonOnair.id'),
			'conditions' => array('LessonOnair.user_id' => $user_id, 'LessonOnair.teacher_id <>' => $teacher_id)
			));
		return isset($onAir['LessonOnair']) ? true : false;
	}

	private function hasConflict($teacher_id) {
		$response['conflict'] = false;
		$date 				= date('Y-m-d');
		$timeNow 			= date('H:i');
		$minutes 			= substr($timeNow,3,2);
		$minutes 			= ((int)$minutes - 30 >= 0) ? '55' : '25';
		$conflictTime = substr($timeNow,0,2).':'.$minutes;
		$endTime 			= $date.' '.substr($timeNow,0,2).':30:00';
		if ((int)$minutes - 30 >= 0) {
			$h 				= ((int)substr($timeNow,0,2))+1;
			$endTime 	= $date.' '.$h.':00:00';
		}
		$conflict = $this->LessonSchedule->find('first',array(
				'conditions' => array(
					'LessonSchedule.teacher_id' 	=> $teacher_id,
					'LessonSchedule.lesson_time' 	=> $endTime,
					'LessonSchedule.status' 			=> 1
					)
			));
		if ($conflict && $timeNow >= $conflictTime) {
			$response['conflict'] = true;
			$response['end_time'] = date('H:i',strtotime($endTime));
		}
		return $response;
	}
	// NC-3873 - get available counselor
	private function getAvailableCounselorTeacher() {
		$result = 0;
		$getOneCounselorTeacher = $this->LessonOnair->find("first", array(
				"conditions" => array(
					"LessonOnair.status" => 1,
					"Teacher.status" => 1,
					"Teacher.counseling_flg" => 1,
					"Teacher.stealth_flg" => 0,
					"Teacher.admin_flg" => 0,
					"Teacher.counselor_order !=" => NULL
				),
				"fields" => array("Teacher.id"),
				"joins" => array(
					array(
						'table' => 'teachers',
						'alias' => 'Teacher',
						'type' => 'LEFT',
						'conditions' => 'Teacher.id = LessonOnair.teacher_id'
					)
				),
				'order' => 'Teacher.counselor_order ASC',
			)
		);

		if ($getOneCounselorTeacher) {
			$result = isset($getOneCounselorTeacher['Teacher']['id']) ? $getOneCounselorTeacher['Teacher']['id'] : 0;
		}
		return $result;
	}

	// NC-3873 - check reservation counselor
	private function checkReservationCounselorTeacher($user_api_token = null) {
		$this->autoRender = false;
		$result = 0;
		$api = new ApiCommonController();
		$user = $api->findApiToken($user_api_token);

		if ( !$user || $user == "Invalid users_api_token" ) {
			return $result;
		}
		$userId = $user['id'];
		$startDate = (date('i') >= 30) ? date('Y-m-d H:30:00') : date('Y-m-d H:00:00');

		$getOneCounselorTeacher = $this->LessonOnair->find("first", array(
				"conditions" => array(
					"LessonOnair.status" => array(1,2,3),
					"LessonSchedule.user_id" => $userId,
					"LessonSchedule.lesson_time" => $startDate,
					"Teacher.status" => 1,
					"Teacher.counseling_flg" => 1,
					"Teacher.stealth_flg" => 0,
					"Teacher.admin_flg" => 0,
					"Teacher.counselor_order !=" => null
				),
				"fields" => array("Teacher.id"),
				"joins" => array(
					array(
						'table' => 'teachers',
						'alias' => 'Teacher',
						'type' => 'LEFT',
						'conditions' => "Teacher.id = LessonOnair.teacher_id"
					),
					array(
						'type' => 'LEFT',
						'table' => 'lesson_schedules',
						'alias' => 'LessonSchedule',
						'conditions' => "LessonSchedule.teacher_id = Teacher.id"
					)
				)
			)
		);

		if ($getOneCounselorTeacher) {
			$result = isset($getOneCounselorTeacher['Teacher']['id']) ? $getOneCounselorTeacher['Teacher']['id'] : 0;
		}
		return $result;
	}

}