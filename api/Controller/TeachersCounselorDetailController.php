<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersCounselorDetailController extends AppController {

	public $uses = array(
		'User',
		'UsersClassEvaluation',
		'Teacher',
		'TeacherImage',
		'LessonSchedule',
		'LessonOnairsLog'
	);

	private $api = null;

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('counselorDetail'));
		$this->api = new ApiCommonController();
	}

	public function counselorDetail() {
		$this->autoRender = false;
		$json = array();
		if ($this->request->is('post')) {
			$data = @json_decode($this->request->input(), true);

			if (isset($data['users_api_token']) && trim($data['users_api_token']) == "") {
				$json['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
				$json['error']['message'] = __('users_api_token can not be empty');
				return json_encode($json);
			}
			$user = (!empty($data['users_api_token'])) ? $this->api->validateToken($data['users_api_token']) : null;
			if (!empty($data['users_api_token']) && empty($user)) {
				$json['error']['id'] = Configure::read('error.invalid_api_token');
				$json['error']['message'] = $this->api->error;
				return json_encode($json);
		    } else if (isset($data['api_version']) && is_string($data['api_version'])) {
				$json['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$json['error']['message'] = __('api version must not be string'); 
				return json_encode($json);
            } 
		    //NC-4380 get api version
			$apiVersion = isset($data['api_version'])? $data['api_version'] : 0;
			if ($apiVersion > 10) {
				if ($user['counseling_attended_flg']) {
					$coin = 100;
				} else {
					$coin = 0;
				}
			} else {
				$coin = 0;
			}

	  		$defaultTeacher = $this->Teacher->getDefaultCounselorDetail();
			$teacher = new TeacherTable($defaultTeacher['Teacher']);
			$counselorTeachers = $this->Teacher->getCounselorStatus(array('user_id' => $user['id']));
			$counselorId = $counselorTeachers['counselorId'];
			$status = $counselorTeachers['status'];
	    	$choseAge = $this->UsersClassEvaluation->getRatings($counselorId, true);
	    	$choseAge = (empty($choseAge))? 0 : $choseAge;
	    	$rates = new UsersClassEvaluationTable($choseAge[0][0]);
	    	$rateBreakdown = new UsersClassEvaluationTable($choseAge[0]['TeacherRatingsLesson']);

	    	$stateOption = $this->stateStatus(array(
					"user_data" => $user,
					"teacher_data" => $counselorTeachers,
					"status" => $status,
					"version" => $apiVersion
				)
			);
	    	$json = array(
	    		'teacher' => array(
	    			'status' => $status,
	    			'coin' => $coin,
	    			'state_button' => $stateOption,
	    			"evaluation" => array(
						"count" => $rates->getEvaluationCount(),
						"value" => $counselorTeachers['rating'],
						"comment_count" => $this->UsersClassEvaluation->getCommentCount($counselorId),
						"self_comment_count" => (!empty($user)) ? $this->UsersClassEvaluation->getSelfCommentCount($counselorId, $user['id']) : null
					),
					'images' => array(
						'main' => $teacher->getImageUrl(),
						'album' => $this->_getAlbum()
					),
					'youtube_tag' => $teacher->youtube_tag,
					'counseling_flg' => 1
	    		)
	    	);
	    	
			if ($apiVersion > 10) {
				$json['teacher']['attended_flg'] = intval($user['counseling_attended_flg']);
			}

			return json_encode($json);
		}else{
			$json['error']['id'] = Configure::read('error.invalid_request');
			$json['error']['message'] = __('Invalid request');
		}
		return json_encode($json);
	}

	/**
	 * Get the list of image in teacher
	 * @param string $id - of the Teacher
	 * @return NULL|Ambigous <NULL, string>
	 */	
	public function _getAlbum() {

		$album = null;

		$result = $this->TeacherImage->useReplica()->find('all',array(
				'conditions' => array(
					'TeacherImage.teacher_id = 584',
					'TeacherImage.is_profile' => 0,
					'OR' => array(
						'TeacherImage.approve_flg' => 1,
						'TeacherImage.approve_required' => 0
					)
				),
				'fields' => array(
						'TeacherImage.image'
				),
				'order' => array('TeacherImage.id DESC')
			)
		);

		if ($result) {
			foreach($result as $row){
				$teacher  = new TeacherImageTable($row['TeacherImage']);
				$album[] = FULL_BASE_URL.$teacher->getImage();
				$teacher = null;
			}
		} else {
			return null;
		}

		return $album;
	}

	/**
	* ISSUE: NC-3155
	* 
	* Returns current state status of teacher
	* parameters array of teachers information
	* @return integer
	*/
 	private function stateStatus( $params = array() ) {
		$result = 0;
		$user = $params['user_data'];
		$api_version = $params['version'];

		// Priority #1 -> New registration / Sign in and proceed to the lesson
		if (empty($user)) {
			return $result = Configure::read('user_detail_state_button.new_registration_or_sign_in_and_proceed_to_the_lesson');
		}

		if ($params) {
			$teacher = (isset($params['teacher_data']) && count($params['teacher_data']) > 0 )? $params['teacher_data'] : false;
			$status = (isset($params['status']))? $params['status'] : false;
			$lessonTime = $this->api->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
			$lessonTime = date('Y-m-d H:i:s',$lessonTime);
			$reservSql = $this->LessonSchedule->find( "first", array(
					"conditions" => array(
						"LessonSchedule.status" => 1,
						"LessonSchedule.user_id" => $user["id"],
						"LessonSchedule.teacher_id" => $teacher['counselorId'],
						"LessonSchedule.lesson_time" => $lessonTime
					),
					"fields" => array(
						"TextbookConnect.id",
						"TextbookConnect.textbook_id",
						"TextbookCategory.id",
						"TextbookCategory.type_id",
					),
					"joins" => array(
						array(
							'type' => 'LEFT',
							'table' => 'textbook_connects',
							'alias' => 'TextbookConnect',
							'conditions' => array("LessonSchedule.connect_id = TextbookConnect.id")
						),
						array(
							'type' => 'LEFT',
							'table' => 'textbook_categories',
							'alias' => 'TextbookCategory',
							'conditions' => array("TextbookConnect.category_id = TextbookCategory.id AND TextbookCategory.status = 1")
						)
					),
					'recursive' => -1
				)
			);

			// Priority #2 -----> Re-subscribe and Try 7days free trial
			// free user
			$userNotAdminFlg = ($user['admin_flg'] != 1); //Overide payment if user is admin_flg = 1
			$freeUser = ( UserTable::userAdminStatus( $user['status'], $user['charge_flg'], $user['fail_flg'] , $user['hash16'], $user['id'] ) == 5 );
			$freeTrial = (($user['fail_flg'] == 0 && $user['double_check_flg'] != 2));
			$noFreeTrial = (($user['fail_flg'] == 1 && $user['double_check_flg'] == 2) ||
							 ($user['fail_flg'] == 0 && $user['double_check_flg'] == 2) ||
							 ($user['fail_flg'] == 1 && $user['double_check_flg'] == 1)
							);

			if ($freeUser && $noFreeTrial && $userNotAdminFlg) {
				// Re-subscribe: state = 12
				return Configure::read('user_detail_state_button.re_subscribe');
			}

			if ($freeUser && $freeTrial && $userNotAdminFlg) {
				// Try 7days free trial: state = 13
				return Configure::read('user_detail_state_button.try_7days_free_trial');
			}

			// Priority #3 -----> Perform SMS authentication
			// check PhoneVerifyCheckLog count
			$countTotalVerify = ClassRegistry::init('PhoneVerifyCheckLog')->useReplica()->find('count',array(
				'conditions' => array(
					'user_id' => $user['id'],
					'status' => 0,
				)
			));
			$totalVerify = ( $countTotalVerify > 0 ) ? true : false ;
			$smsThroughFlg = (isset($user['sms_through_flg']) && $user['sms_through_flg'] == 0 ) ? false : true ;

			// if user's sms verification was not successful
			if ((($totalVerify) || ($smsThroughFlg)) == false ) {
				$result = Configure::read('user_detail_state_button.sms_authentication');
				return $result;
			}

			// Priority #4 --> Get Last Lesson Loading 
			if ($teacher && $status == 1) {
				$secRemaining = $this->LessonOnairsLog->getLastLessonLoading(array( 
						"user_id" => $user["id"], 
						"counselor_id" => $teacher['counselorId'] 
					)
				);
				if ($secRemaining != 0) {
					$result = Configure::read('user_detail_state_button.last_lesson_is_being_processed');
					return $result;
				}
			}

			// Priority #5 -----> teacher status color
 			if ($status) {
 				if ($api_version < 11) {
					if (($reservSql && $status == 1) || $status == 5 ) {
						// NC-3378 : teacher selected others during reservation lesson
						if ( $status != null && $status == '4' || $this->api->preperationTime()) {
							return Configure::read('user_detail_state_button.busy');
						} else {
							$result = Configure::read('user_detail_state_button.go_to_reserved_lesson');
						}
					} elseif ($status == 1) {
						return Configure::read('user_detail_state_button.proceed_to_the_lesson_immediately');
					} elseif ($status == 2) {
						return Configure::read('user_detail_state_button.online_within_ten_minutes');
					} elseif ($status == 3) {
						return Configure::read('user_detail_state_button.busy');
					} elseif ($status == 4) {
						return Configure::read('user_detail_state_button.offline');
					}
				} else {
					if (($reservSql && $status == 1) || $status == 5 ) {
						// NC-3378 : teacher selected others during reservation lesson
						if ( $status != null && $status == '4'|| $this->api->preperationTime()) {
							return Configure::read('user_detail_state_button.other');
						} else {
							$result = Configure::read('user_detail_state_button.go_to_reserved_lesson');
						}
					} else {
						return Configure::read('user_detail_state_button.other');
					}
				}
			}
		}
		return $result;
	}
}
