<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersSearchController extends AppController {

	public $uses = array(
		'Teacher',
		'UsersFavorite',
		'LessonSchedule',
		'CommonTeacherStatus'
	);
	
	private $api = null;

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('search', 'getTextbookCategoriesSearchItems'));
		$this->api = new ApiCommonController();
	}

	public function search() {
		$this->autoRender = false;

		@$inputs = json_decode($this->request->input(), true);
		$this->request->data = $inputs;
		// request data
		$req = $this->request->data;

		// $this->log('[TeacherSearch] req ' . json_encode($req), 'debug');
		
		if ((isset($req['users_api_token']) && trim($req['users_api_token']) != "") || !isset($req['users_api_token'])) {
			$user = (!empty($req['users_api_token'])) ? $this->api->validateToken($req['users_api_token']) : null;
			if (isset($req['users_api_token']) && !$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');
				return json_encode($response);
			}
			return $this->getTeacherOnlineList($user, $req);
		} else if ((isset($req['users_api_token']) && trim($req['users_api_token']) == "")) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		}
	}

	public function getTeacherOnlineList($user, $queryValue = array()) {
		if (isset($queryValue['conditions'])) {
			foreach ($queryValue['conditions'] as $key => $condition) {
				$queryValue[$key] = $condition;
			}
		}
		// trap status

		if (isset($queryValue['status'])) {
		 if (is_string($queryValue['status'])) {
		 		$response['error']['id'] = Configure::read('error.status_must_be_integer');
				$response['error']['message']  = __('status must be integer');
				return json_encode($response);
			} else if ($queryValue['status'] >= 4) {
				$response['error']['id'] = Configure::read('error.invalid_status');
				$response['error']['message']  = __('status must be 1 to 3');
				return json_encode($response);
			}
		}

		// trap age
		// NC-3458 = make age 1 - 5 if api_version >=4.
		$apiVersion = isset($queryValue['api_version']) ? $queryValue['api_version'] : 0;

		// check version to redirect response result api version 17
		$arrParamVer = array(
			"user"=> $user,
			"api_version" => $apiVersion,
			"native_language2" => $user['native_language2'],
			"query_value"=> $queryValue
		);
		return self::getTeacherOnlineListv17($arrParamVer);
		
		if (isset($queryValue['age'])) {
			if (isset($queryValue['age']) && !is_int($queryValue['age'])) {
				$response['error']['id'] = Configure::read('error.age_must_be_integer');
				$response['error']['message']  = __('age must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['age'], array(1,2,3,4,5))) {
				$response['error']['id'] = Configure::read('error.invalid_age');
				$response['error']['message'] = __('age must be 1 to 5');
				return json_encode($response);
			}
		}
		// trap gender
		if (isset($queryValue['teachers_gender'])) {
			if (isset($queryValue['teachers_gender']) && !is_int($queryValue['teachers_gender'])) {
				$response['error']['id'] = Configure::read('error.teachers_gender_must_be_integer');
				$response['error']['message']  = __('teachers_gender must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['teachers_gender'], array(1,2))) {
				$response['error']['id'] = Configure::read('error.invalid_teachers_gender');
				$response['error']['message'] = __('teachers_gender must be 1 or 2');
				return json_encode($response);
			}
		}
		// trap teachers japanese flag
		if (isset($queryValue['teachers_japanese_flg'])) {
			if (isset($queryValue['teachers_japanese_flg']) && !is_int($queryValue['teachers_japanese_flg'])) {
				$response['error']['id'] = Configure::read('error.teachers_japanese_flg_must_be_integer');
				$response['error']['message']  = __('teachers_japanese_flg must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['teachers_japanese_flg'], array(1,2))) {
				$response['error']['id'] = Configure::read('error.invalid_teachers_japanese_flg');
				$response['error']['message'] = __('teachers_japanese_flg must be 1 or 2');
				return json_encode($response);
			}
		}
		// trap order
		if (isset($queryValue['order'])) {
			if (isset($queryValue['order']) && !is_int($queryValue['order'])) {
				$response['error']['id'] = Configure::read('error.order_must_be_integer');
				$response['error']['message']  = __('order must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['order'], range(1,4))) {
				$response['error']['id'] = Configure::read('error.invalid_order');
				$response['error']['message']  = __('order must be 1 to 4');
				return json_encode($response);
			}
		}
		
		// trap lesson_course
		if (isset($queryValue['lesson_course'])) {
			if (isset($queryValue['lesson_course']) && !is_int($queryValue['lesson_course'])){
				$response['error']['id'] = Configure::read('error.lesson_course_must_be_integer');
				$response['error']['message'] = __('lesson_course must be integer');
				return json_encode($response);
			}
		}
		
		
		// trap pagination
		if (isset($queryValue['pagination'])) {
			if (isset($queryValue['pagination']) && !is_int($queryValue['pagination'])) {
				$response['error']['id'] = Configure::read('error.pagination_must_be_integer');
				$response['error']['message']  = __('pagination must be integer');
				return json_encode($response);
			} else if (is_string($queryValue['pagination']) || preg_match('/[^0-9]/', $queryValue['pagination']) || $queryValue['pagination'] <= 0) {
				$response['error']['id'] = Configure::read('error.pagination_must_be_greater_than_zero');
				$response['error']['message']  = __('pagination must be greater than 0');
				return json_encode($response);
			}
		}

		

		// trap lesson_from_at
		if (isset($queryValue['lesson_from_at'])) {
			if (isset($queryValue['lesson_from_at']) && !is_string($queryValue['lesson_from_at'])) {
				$response['error']['id'] = Configure::read('error.lesson_from_at_must_be_string');
				$response['error']['message']  = __('lesson_from_at must be string');
				return json_encode($response);
			}
		}

		//trap lesson_to_at
		if (isset($queryValue['lesson_to_at'])) {
			if (isset($queryValue['lesson_to_at']) && !is_string($queryValue['lesson_to_at'])) {
				$response['error']['id'] = Configure::read('error.lesson_to_at_must_be_string');
				$response['error']['message']  = __('lesson_to_at must be string');
				return json_encode($response);
			}
		}

		if(isset($queryValue['lesson_begin_at'])) {
			$queryValue['lesson_from_at'] = $queryValue['lesson_begin_at'];
			if (!is_int($queryValue['lesson_begin_at'])) {
				$response['error']['id'] = Configure::read('error.lesson_begin_at_must_be_integer');
				$response['error']['message']  = __('lesson_begin_at must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['lesson_begin_at'], range(1,6))) {
				$response['error']['id'] = Configure::read('error.invalid_lesson_begin_at');
				$response['error']['message'] = __('lesson_begin_at must be 1 to 6');
				return json_encode($response);
			}
		}

		//trap lesson_from_at and lesson_to_at
		if (!isset($queryValue['lesson_begin_at']) && isset($queryValue['lesson_from_at']) && !isset($queryValue['lesson_to_at'])) {
			$response['error']['id'] = Configure::read('error.lesson_to_at_is_required');
			$response['error']['message']  = __('lesson_to_at is required');
			return json_encode($response);
		}

		//trap lesson_from_at and lesson_to_at
		if (isset($queryValue['lesson_to_at']) && !isset($queryValue['lesson_from_at'])) {
			$response['error']['id'] = Configure::read('error.lesson_from_at_is_required');
			$response['error']['message']  = __('lesson_from_at is required');
			return json_encode($response);
		}

		//trap lesson_from_at and lesson_to_at difference		
		if (isset($queryValue['lesson_to_at']) && isset($queryValue['lesson_from_at'])) {
			if (strtotime($queryValue['lesson_from_at']) > strtotime($queryValue['lesson_to_at'])) {
				$response['error']['id'] = Configure::read('error.lesson_from_at_must_be_less_than_lesson_to_at');
				$response['error']['message']  = __('lesson_from_at must be less than lesson_to_at');
				return json_encode($response);
			}                          
		}


		//trap api_version
		if (isset($queryValue['api_version'])) {
			if (isset($queryValue['api_version']) && !is_int($queryValue['api_version'])){
				$response['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$response['error']['message'] = __('api_version must be integer');
				return json_encode($response);
			}
		}



		if (isset($queryValue['date'])) {
			if (isset($queryValue['date']) && !is_int($queryValue['date'])) {
				$response['error']['id'] = Configure::read('error.date_must_be_integer');
				$response['error']['message']  = __('date must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['date'], range(0,7))) {
				$response['error']['id'] = Configure::read('error.invalid_date');
				$response['error']['message'] = __('date must be 0 to 7');
				return json_encode($response);
			}
		}

		if (isset($queryValue['teacher_name_text'])) {
			if (isset($queryValue['teacher_name_text']) && !is_string($queryValue['teacher_name_text'])) {
				$response['error']['id'] = Configure::read('error.lesson_from_at_must_be_string');
				$response['error']['message']  = __('teacher_name_text must be string');
				return json_encode($response);
			}
		}

		$joins = $conditions = $fieldsQuery = array();
		$sortCondition = null;

		$joinConditions = $this->getSearchConditions($queryValue);
		$blockList = (!empty($user)) ? BlockListTable::getBlocks($user['id']) : array(0, 0);

		// default condition 
		$conditions['Teacher.status'] = 1;
		$conditions['Teacher.admin_flg'] = 0;

		// default sorting
		$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, Teacher__status_sort asc, Teacher.counseling_flg desc, Teacher.good_point DESC";

		$joins = array(
			array(
				'table' => 'teacher_ratings_lessons',
				'alias' => 'TeacherRatingsLessons',
				'type' => 'LEFT',
				'conditions' => 'TeacherRatingsLessons.teacher_id = Teacher.id'
			),
			array(
				'table' => 'teacher_rank_coins',
				'alias' => 'TeacherRankCoins',
				'type' => 'LEFT',
				'conditions' => 'TeacherRankCoins.id = Teacher.rank_coin_id'
			)
		);
		if (!empty($user)) {
			$joins[] = array(
				'table' => 'users_favorites',
				'alias' => 'User_Favorite',
				'type' => 'LEFT',
				'conditions' => 'User_Favorite.teacher_id = Teacher.id AND User_Favorite.user_id = '.$user['id'],
			);
		}
		$oUserData = new UserTable($user);
		$membership = (!empty($user)) ? $oUserData->getUserMembership() : null;

		// -- Search condition
		if (count($queryValue) != 0) {
			// ------ Keyword Row
			if (isset($queryValue['text'])) {
		
				$freewordArray = explode(" ", $queryValue['text']);
				foreach ($freewordArray as $keyWord) {
					$freewordConditions[] = array(
 						"
						(Teacher.educational_background like ?
						OR Teacher.hobby like ?
						OR Teacher.message like ?
						OR Teacher.staff_message like ?)
						" => array('%'.$keyWord.'%', '%'.$keyWord.'%', '%'.$keyWord.'%', '%'.$keyWord.'%')
					);
				}

				$conditions[]['OR'] = $freewordConditions;
			}
			// ------ Status Row 
			// Search teacher accrdng to status  : online, offline, available
			if (isset($queryValue['status']) && !empty($queryValue['status'])) {
				if ($queryValue['status'] == 1) {
					$conditions['OR']['AND']['LessonOnair.connect_flg'] = 1;
					$conditions['OR']['AND']['LessonOnair.status !='] = 0;
					$conditions['OR'][] = array(
						'AND' => array(
							'TeacherStatus.status' => array(4,5),
							'(select count(*) from lesson_onairs a
							 where a.teacher_id = Teacher.id
							 and a.connect_flg = 0)' => 0
						)
					);
				} else if ($queryValue['status'] == 2) {
					$conditions['LessonOnair.connect_flg'] = NULL;
					$conditions['Teacher.status_sort'] = 4;
				} else if ($queryValue['status'] == 3) {
					$conditions['LessonOnair.status'] = 1;
					$conditions['LessonOnair.connect_flg'] = 1;
				}
			}

			// ------ favorite
			if (!empty($queryValue['favorite']) && $queryValue['favorite']) {
				$fid = (!empty($user)) ? $this->getMyFavorites($user['id']) : null;
				if (isset($fid)) {
					$conditions['Teacher.id'] = (count($fid) == 1)? array(0,implode($fid)) : ((count($fid) == 0)? array(0,0) : $fid);
				}
			}

			// ------ Gender Row
			if (!empty($queryValue['teachers_gender'])) {
					$conditions['AND']['Teacher.gender'] = $queryValue['teachers_gender'];
			}
			// ------ Age Row
			// NC-3458 change age flags.
			if (!empty($queryValue['age'])) {
				if ($queryValue['age'] == 1) {
					// Age 10's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(20);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(10);
				} else if ($queryValue['age'] == 2) {
					// Age 20's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(30);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(10); // NC-3582 - Teachers in 20's and 10's. because there are few teacher's who age == 10's
				} else if ($queryValue['age'] == 3) {
					// Age 30's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(40);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(30);
				} else if ($queryValue['age'] == 4) {
					// Age 40's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(50);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(40);
				} else {
					// Age 50' and above.
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(50);
				}
			}
			
			// ------- Japanese Flag row
			if (!empty($queryValue['teachers_japanese_flg'])) {
					if ($queryValue['teachers_japanese_flg'] == 1) {
						$conditions['AND']['Teacher.japanese_flg'] = 0;
					} else {
						$conditions['AND']['Teacher.japanese_flg'] = 1;
					}
			}
			if (!empty($queryValue['teacher_name_text'])) {
				$conditions[]['OR'] = array(
							'Teacher.jp_name LIKE' => '%'.$queryValue['teacher_name_text'].'%',
							'Teacher.name LIKE' => '%'.$queryValue['teacher_name_text'].'%'
				);
			}

			if (isset($queryValue['features'])) {
				if (isset($queryValue['features']['callan_halfprice_flg'])) {
					$conditions[] = array('Teacher.callan_halfprice_flg' => $queryValue['features']['callan_halfprice_flg']);
				}
				if (isset($queryValue['features']['beginner_teacher_flg'])) {
					$conditions[] = array('Teacher.beginner_teacher_flg' => $queryValue['features']['beginner_teacher_flg']);
				}
				if (isset($queryValue['features']['free_talk_flg'])) {
					$conditions[] = array('TeacherFeature.best_free_talk' => $queryValue['features']['free_talk_flg']);
				}
				if (isset($queryValue['features']['native_speaker_flg'])) {
					$conditions[] = array('Teacher.native_speaker_flg' => $queryValue['features']['native_speaker_flg']);
				}
				if (isset($queryValue['features']['japanese_flg'])) {
					$conditions[] = array('Teacher.japanese_flg' => $queryValue['features']['japanese_flg']);
				}
				if (isset($queryValue['features']['suitable_for_children_flg'])) {
					$conditions[] = array('TeacherFeature.suitable_for_children' => $queryValue['features']['suitable_for_children_flg']);
				}
			}


			if (!empty($queryValue['nationality'])) {
				$conditions['Teacher.homeland2'] = $queryValue['nationality']; 
			}

			//coin amount filters
			if (isset($queryValue['coin']) && $queryValue['coin']) {
				$coinCond = array();
				foreach ($queryValue['coin'] as $coin) {
					$coinCond[]['TeacherRankCoins.coins'] = $coin;
				}
				$conditions[]['OR'] = $coinCond;
			}

			// ------ Date and Lesson Begin at Row		
			$timeRange = array(		
				1 => array('00:00:00','04:00:00'),		
				2 => array('04:00:00','08:00:00'),		
				3 => array('08:00:00','12:00:00'),		
				4 => array('12:00:00','16:00:00'),		
				5 => array('16:00:00','20:00:00'),		
				6 => array('20:00:00','24:00:00')		
			);

			$qDate = (empty($queryValue['date'])) ? 0 : $queryValue['date'];
			$date = date('Y-m-d', strtotime('+'.$qDate.'Day'));

			if (isset($queryValue['date']) || isset($queryValue['lesson_from_at']) || isset($queryValue['lesson_begin_at'])) {

				if (isset($queryValue['lesson_begin_at'])) {
					$queryValue['lesson_from_at'] =  $timeRange[$queryValue['lesson_begin_at']][0];
				}
				if (isset($queryValue['lesson_from_at'])) {
					$queryValue['lesson_from_at'] =  $queryValue['lesson_from_at'].':00';
				}

				if (!empty($queryValue['lesson_from_at']) && !empty($queryValue['lesson_to_at']) ){
					$startDate = date('Y-m-d H:i:s', strtotime($date.' '.$queryValue['lesson_from_at']));
					$endDate = date('Y-m-d H:i:s', strtotime($date.' '.$queryValue['lesson_to_at'].':00'));
				} else if(!empty($queryValue['lesson_begin_at'])){
					$startDate = date('Y-m-d H:i:s', strtotime($date.' '.$queryValue['lesson_from_at']));
					$endDate = date('Y-m-d H:i:s', strtotime($date.' '.$timeRange[$queryValue['lesson_begin_at']][1]));
				} else {
					$startDate = date('Y-m-d H:i:s', strtotime($date));
					$endDate = date('Y-m-d H:i:s', strtotime($date.' 24:00:00'));
				}

				$newDate = $this->newStartDate($startDate);

				if ( $newDate == $endDate || $newDate > $endDate ) {
					// NC-5036 : skip SQL
					// Mid night heavy query
					$conditions['Teacher.id'] = Configure::read('counselor.connect_id'); // show only counselor
				} else {
				// ShiftWorkon and ShiftWorkHideDay

				$conditions[] = 'Teacher.id IN (( SELECT ShiftWorkon.teacher_id FROM 
					`english`.`shift_workons` AS `ShiftWorkon` 
					LEFT JOIN `english`.`lesson_schedules` AS `LessonSchedule` ON (`LessonSchedule`.`teacher_id` = `ShiftWorkon`.`teacher_id`
							AND `LessonSchedule`.`lesson_time` = `ShiftWorkon`.`lesson_time`
							AND `LessonSchedule`.`status` IN (1 , 0)
							)
					LEFT JOIN
						`english`.`shift_work_hide_dates` AS `ShiftWorkHideDay` 
						ON (
							(`ShiftWorkon`.`teacher_id` = `ShiftWorkHideDay`.`teacher_id`
							AND `ShiftWorkon`.`lesson_time` = `ShiftWorkHideDay`.`lesson_time`
							AND `ShiftWorkHideDay`.`schedule_type` = 1)
							OR 
							(`ShiftWorkon`.`teacher_id` = `ShiftWorkHideDay`.`teacher_id`
							AND `ShiftWorkHideDay`.`lesson_time` BETWEEN \'' . date('Y-m-d 00:00:00', strtotime($startDate)) . '\' AND \'' . date('Y-m-d 23:30:00', strtotime($startDate)) . '\' 
							AND `ShiftWorkHideDay`.`schedule_type` = 2)
							)
					WHERE 
							(`ShiftWorkon`.`lesson_time` >= "'.$newDate.'")
							AND (`ShiftWorkon`.`lesson_time` < "'.$endDate.'")
							AND (`LessonSchedule`.`lesson_time` IS NULL)
							AND (`ShiftWorkHideDay`.`id` IS NULL) ))';
				}

				// display only teacher with reservation hide flg 0
				$conditions['Teacher.reservation_hide_flg'] = 0;
			}
			//for block users
			$conditions['Teacher.id NOT IN'] = $blockList;

			$statusOrder = "Teacher__status_sort asc,";
			if (isset($queryValue['api_version'])) {
				if (isset($queryValue['lesson_from_at']) || isset($queryValue['lesson_begin_at'])) {
					$statusOrder = 	"";
				}
			}
			// ------ Sort Row
			if (!empty($queryValue['order'])) {
				if ($queryValue['order'] == 1) {  // Sort teacher by login last_login_time in DESC
					 $sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, Teacher.last_login_time DESC, Teacher.name ASC";
				} else if ($queryValue['order'] == 2) {  // Sort teacher by name in ASC
					if ($statusOrder == '') {
						$sortCondition = "Teacher__counselor_online DESC, ".$statusOrder." Teacher.counseling_flg desc, TeacherRatingsLessons.ratings DESC, lesson_counts DESC";
					} else {
						$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, TeacherRatingsLessons.ratings DESC, lesson_counts DESC, Teacher.name ASC";
					}
				} else if ($queryValue['order'] == 3) {   // Sort teacher by number of good lesson in DESC
					$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, lesson_counts DESC, Teacher.name ASC";
				} else if ($queryValue['order'] == 4) {   // Sort teacher by name in ASC
					$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, Teacher.name ASC";
				}
			}
		}
		$userFavorite =  (!empty($user)) ? "(CASE WHEN LENGTH(User_Favorite.id) > 0 THEN true ELSE false END) AS is_favorite" : '';
		$lessonConnectflg = "(CASE WHEN LENGTH(LessonOnair.status) is null THEN '' ELSE 1 END) AS connect_flg";
		$limit = 100;
		$pagination = (isset($queryValue['pagination']) && !empty($queryValue['pagination']) && (int)$queryValue['pagination'] >= 1) ? (int)$queryValue['pagination'] : 1;
		$offset = ($pagination - 1) * $limit;

		# has next reservation
		$reservationCondition = array(
			'LessonSchedule.user_id' => $user['id']
		);
		$nextReservation = (!empty($user)) ? $this->api->getReservation($reservationCondition) : array('teacher_id' => null);
		$nativeTeacherRankIds = implode(",", Configure::read('native_teacher_rank_ids'));

		$this->Teacher->virtualFields = array(
			'status_sort' => 'CASE WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0 AND LessonOnair.status = 1 THEN 1
												WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0 AND LessonOnair.status = 2 AND Teacher.id = "'.$nextReservation['teacher_id'].'" THEN 1
												WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0 AND LessonOnair.status = 2 OR LessonOnair.status = 3 THEN 3
												WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg = 0 THEN 4
												WHEN TeacherStatus.status IS NOT NULL AND (TeacherStatus.status = 4 AND TeacherStatus.remarks1 NOT IN (1,4) OR TeacherStatus.status = 5 OR TeacherStatus.remarks2 LIKE "after_lesson_other") THEN 2
												WHEN TeacherStatus.status IS NOT NULL AND (TeacherStatus.status = 1 OR TeacherStatus.status = 3 OR (TeacherStatus.status = 4 AND TeacherStatus.remarks1 IN (1,4))) THEN 3
												ELSE 4 END
												',
			'counselor_online' => 'CASE
										WHEN Teacher.counseling_flg = 1 AND ((LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0) OR TeacherStatus.status = 4) THEN 1
										ELSE 0 END
									',
			'native_now' => 'CASE
								WHEN Teacher.rank_coin_id IN ('.$nativeTeacherRankIds.') AND (LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0) THEN 1
								ELSE 0 END
									'
		);
		$fieldsQuery = array(
				'Teacher.native_speaker_flg',
				'Teacher.homeland2',
				'Teacher.good_point',
				'Teacher.counseling_flg',
				'LessonOnair.connect_flg',
				'LessonOnair.status',
				'TeacherRatingsLessons.ratings',
				'TeacherRatingsLessons.lessons',
				'TeacherStatus.remarks1',
				'TeacherStatus.remarks2',
				'Teacher.status_sort',
				'Teacher.native_now',
				'((Teacher.lesson_count) + 0) AS lesson_counts',
				'TeacherRankCoins.id',
				'TeacherRankCoins.coins'
			);

		if (isset($queryValue['lesson_course']) && !empty($queryValue['lesson_course'])){
			$joins[] = array(
				'table' => 'teacher_badges',
				'alias' => 'TeacherBadge',
				'conditions' => 'TeacherBadge.teacher_id = Teacher.id',
				'type' => 'INNER'
			);
			$conditions['TeacherBadge.textbook_category_id'] = $queryValue['lesson_course'];
		}
		
		$conditions['Teacher.stealth_flg'] = 0; // Temporary HOTFIX NC-1277 (16/03/16 Onishi)
		$query_conditions = array(
			'fields' => array_merge($fieldsQuery, array($userFavorite)),
			'joins' => $joins,
			'conditions' => array($conditions),
			'changeAlias' => array('TeacherStatus.status AS teacher_status' => 'TeacherStatus.status'),
			'order' => $sortCondition,
			'limit' => $limit+1,
			'offset' => $offset
	 	);

	 	$conditionArr = array('joinConditions' => $joinConditions);
		$paramArr = array(
			"query_conditions" => $query_conditions,
			"val" => null
		);
		$TeacherOnlineList = $this->CommonTeacherStatus->getAllStatusApi($paramArr);

		$data = array();
		$teacher_arr = array();
		$count = 0;
		// $nextReservation = $this->nextReservation($userId['id']);
		foreach ($TeacherOnlineList as $tol) {
			if ($count <= 99) {
				//exclude if teacher is counselor
				if (intval($tol['Teacher']['counseling_flg'])) {
					continue;
				}
				$teacher = new TeacherTable($tol['Teacher']);
				$TeacherStatus = new TeacherTable($tol['TeacherStatus']);

				$statusFlag = $this->api->teacherStatusColor(
					array(
						'LessonOnair' => $tol['LessonOnair'],
						'Teacher' => $teacher,
						'TeacherStatus' => $TeacherStatus,
						'nextReservation' => isset($nextReservation['teacher_id']) ? $nextReservation['teacher_id'] : null,
						'userId' => $user['id']
					)
				);
				$countries = strtolower($tol['CountryCode']['country_name']);
				$explodeCountries = explode(' ', $countries);
				$implode = implode('_',$explodeCountries);

				//native teacher is online
				$nativeNowFlg = 0;
				if (isset($tol['LessonOnair']['status']) && isset($tol['TeacherRankCoins']['id'])) {
					$nativeNowFlg = in_array($tol['TeacherRankCoins']['id'], array(13,19)) ? 1 : 0;
				}
				
				//if api version is greater than or equal to 17
				$checkCountryCode = (!$user['native_language2']) ? 'ja' : $user['native_language2'];
				$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $tol['Teacher']['jp_name'] : '' ;
				
				$arr = array(
					'id' => (int)$tol['Teacher']['id'],
					'name' => $getUserSettingLanguage,
					'name_ja' => $tol['Teacher']['jp_name'],
					'name_eng' => $tol['Teacher']['name'],
					'status' => $statusFlag,
					'coin' => is_null($tol['TeacherRankCoins']['coins']) ? 0 : $tol['TeacherRankCoins']['coins'],
					'rating' => is_null($tol['TeacherRatingsLessons']['ratings']) || $tol['TeacherRatingsLessons']['ratings'] == 0 ? null : round($tol['TeacherRatingsLessons']['ratings'], 2),
					'lessons' => (int)($tol[0]['lesson_counts']),
					'goods' => (int)$tol['Teacher']['good_point'],
					'favorite' => (isset($tol[0]['is_favorite'])) ? (boolean)$tol[0]['is_favorite'] : false,
					'nationality_id' => (int)$tol['CountryCode']['id'],
					'native_speaker_flg' => (int)$tol['Teacher']['native_speaker_flg'],
					'suitable_for_children_flg' => isset($tol['TeacherFeature']['suitable_for_children']) ? (int)$tol['TeacherFeature']['suitable_for_children'] : 0,
					'callan_discount_flg' => $tol['Teacher']['callan_halfprice_flg'] ? true : false,
					'beginner_teacher_flg' => $tol['Teacher']['beginner_teacher_flg'] ? 1 : 0,
					'free_talk_flg' => (int)$tol['TeacherFeature']['best_free_talk'],
					'image_main' => $teacher->getImageUrl(),
					'country_image' => FULL_BASE_URL."/user/images/flag/".$implode.".png",
					'native_now_flg' => $nativeNowFlg,
					'message' => $teacher->getTranslateMessage(),
					'message_ja' => $teacher->getTranslateMessage(),
					'staff_message_ja' => $teacher->getTranslateSelfIntroductionThirdPp(),
					'message_eng' => $teacher->message_short,
					'staff_message_eng' => $teacher->getSelfIntroductionThirdPp()
				);	

				unset($arr['name_ja']);
				unset($arr['message_ja']);
				unset($arr['staff_message_ja']);
				unset($arr['staff_message_eng']);
				
				$arr['counseling_flg'] = 0;
				
				array_push($teacher_arr, $arr);
			}
			$count++;
			if ($nativeNowFlg) {
				$nativeNowTeachers = array_keys($teacher_arr);
			}
		}
		//add counselor on top of the list
		if ($pagination == 1) {
			unset($this->Teacher->virtualFields);//unset virtual fields to prevent error
			$counselorData = $this->Teacher->counselorDisplay(array('user_id' => $user['id'],'api_version' =>$apiVersion,'native_language2' => $user['native_language2']));
			//prepend counselor
			if ($counselorData) {
				if ($statusOrder == '' || empty($nativeNowTeachers)) {
					array_unshift($teacher_arr, $counselorData);
				} else {
					// append counselor after native now teachers
					$index = end($nativeNowTeachers) + 1;
					$res = array_splice($teacher_arr, $index, 0,  array($index => $counselorData));
					$teacher_arr = $teacher_arr + $res;
				}
			}
		}
		$response['teachers'] = $teacher_arr;

		if(count($TeacherOnlineList) > 10) {
			$response['has_next'] = true;
		} else {
			$response['has_next'] = false;
		}
		return json_encode($response);
	}

	private function birthday($years){
		return date('Y-m-d', strtotime($years . ' years ago'));
	}

	private function getMyFavorites($userId) {
		$result = array();
		$this->UsersFavorite->openDBReplica();
		$teacher = $this->UsersFavorite->find('list', array(
				'fields' => array('UsersFavorite.teacher_id'),
				'conditions' => array('UsersFavorite.user_id' => $userId)
			)
		);
		$this->UsersFavorite->closeDBReplica();
		$result = $teacher;
		return $result;
	}

	private function newStartDate($dateStart) {
		$nextReservationStart = $this->LessonSchedule->getNextReservationTimeStart();
		return (strtotime($nextReservationStart) > strtotime($dateStart)) ? $nextReservationStart : $dateStart;
	}

	private function getSearchConditions($data) {
		$joinConditions = '';
		$joinConditions .= " AND Teacher.status = 1";
		$joinConditions .= " AND Teacher.admin_flg = 0";

		if (isset($data['teachers_gender'])) {
			if ($data['teachers_gender'] == 1) {
				$joinConditions .= " AND Teacher.gender = 1";
			} else if ($data['teachers_gender'] == 2) {
				$joinConditions .= " AND Teacher.gender = 2";
			}
		}

		// NC-3458
		if (!empty($data['age'])) {
			if ($data['age'] == 1) {
				// Age 10's
				$joinConditions .= " AND DATE(Teacher.birthday) >= '". $this->birthday(20)."'";
				$joinConditions .= " AND DATE(Teacher.birthday) <= '". $this->birthday(10)."'";
			} else if ($data['age'] == 2) {
				// Age 20's
				$joinConditions .= " AND DATE(Teacher.birthday) >= '". $this->birthday(30)."'";
				$joinConditions .= " AND']['DATE(Teacher.birthday) <= '". $this->birthday(20)."'";
			} else if ($data['age'] == 3) {
				// Age 30's
				$joinConditions .= " AND DATE(Teacher.birthday) >= '". $this->birthday(40)."'";
				$joinConditions .= " AND DATE(Teacher.birthday) <= '". $this->birthday(30)."'";
			} else if ($data['age'] == 4) {
				// Age 40's
				$joinConditions .= " AND DATE(Teacher.birthday) >= '". $this->birthday(50)."'";
				$joinConditions .= " AND']['DATE(Teacher.birthday) <= '". $this->birthday(40)."'";
			} else {
				// Age 50' and above.
				$joinConditions .= " AND DATE(Teacher.birthday) <= '". $this->birthday(50)."'";
			}
		}
		
		// ---- teachers feature row
		$teachersFeature = array(
			1 => 'TeacherFeature.new',
			2 => 'TeacherFeature.best_free_talk',
			3 => 'TeacherFeature.good_in_teaching_textbook',
			4 => 'TeacherFeature.suitable_for_intermediate_or_advance_students',
			5 => 'TeacherFeature.have_many_beginner_students',
			6 => 'Teacher.japanese_flg',
			7 => 'TeacherFeature.suitable_for_children',
			8 => 'TeacherFeature.suitable_for_senior',
			9 => 'TeacherFeature.good_grammar_and_vocabulary',
			10 => 'TeacherFeature.pronunciation'
		);

		if (isset($data['teachers_feature'])) {
			$joinConditions .= ' AND '. $teachersFeature[$data['teachers_feature']] . ' = 1';
		}

		return $joinConditions;
	}

	/**
	 * getTextbookCategoriesSearchItems
	 * -> get textbook categories
	 */
	public function getTextbookCategoriesSearchItems () {
		$this->autoRender = false;
		
		// create empty array
		$categories = array(
			array(
				'id' => 0,
				'jp_name' => '指定しない',
				'eng_name' => 'Not Specified'
			)
		);
		
		// get categories
		$activeBadges = TeacherBadgeTable::getBadges();
		
		// check active badges
		if ($activeBadges) {
			
			// loop through active badges
			foreach($activeBadges as $badge){
				if (!isset($categories[$badge['TextbookCategory']['id']])) {
					$categories[$badge['TextbookCategory']['id']] = array(
						'id' => $badge['TextbookCategory']['id'],
						'jp_name' => $badge['TextbookCategory']['name'],
						'eng_name' => $badge['TextbookCategory']['english_name']
					);					
				}
			}
		}
		
		// return categories
		return json_encode(array_values($categories));
	}

	/**
	 * Private functions for api version
	 * 
	 */
	private function getTeacherOnlineListv17( $params = array() ) {

		$queryValue = isset($params["query_value"]) ? $params["query_value"] : array();
		$user = isset($params["user"]) ? $params["user"] : array();
		if (isset($queryValue['conditions'])) {
			foreach ($queryValue['conditions'] as $key => $condition) {
				$queryValue[$key] = $condition;
			}
		}
		// trap status

		if (isset($queryValue['status'])) {
		 if (is_string($queryValue['status'])) {
		 		$response['error']['id'] = Configure::read('error.status_must_be_integer');
				$response['error']['message']  = __('status must be integer');
				return json_encode($response);
			} else if ($queryValue['status'] >= 4) {
				$response['error']['id'] = Configure::read('error.invalid_status');
				$response['error']['message']  = __('status must be 1 to 3');
				return json_encode($response);
			}
		}
		// trap age
		// NC-3458 = make age 1 - 5 if api_version >=4.
		$apiVersion = isset($queryValue['api_version']) ? $queryValue['api_version'] : 0;
		
		if (isset($queryValue['age'])) {
			if (isset($queryValue['age']) && !is_int($queryValue['age'])) {
				$response['error']['id'] = Configure::read('error.age_must_be_integer');
				$response['error']['message']  = __('age must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['age'], array(1,2,3,4,5))) {
				$response['error']['id'] = Configure::read('error.invalid_age');
				$response['error']['message'] = __('age must be 1 to 5');
				return json_encode($response);
			}
		}
		// trap gender
		if (isset($queryValue['teachers_gender'])) {
			if (isset($queryValue['teachers_gender']) && !is_int($queryValue['teachers_gender'])) {
				$response['error']['id'] = Configure::read('error.teachers_gender_must_be_integer');
				$response['error']['message']  = __('teachers_gender must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['teachers_gender'], array(1,2))) {
				$response['error']['id'] = Configure::read('error.invalid_teachers_gender');
				$response['error']['message'] = __('teachers_gender must be 1 or 2');
				return json_encode($response);
			}
		}
		// trap teachers japanese flag
		if (isset($queryValue['teachers_japanese_flg'])) {
			if (isset($queryValue['teachers_japanese_flg']) && !is_int($queryValue['teachers_japanese_flg'])) {
				$response['error']['id'] = Configure::read('error.teachers_japanese_flg_must_be_integer');
				$response['error']['message']  = __('teachers_japanese_flg must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['teachers_japanese_flg'], array(1,2))) {
				$response['error']['id'] = Configure::read('error.invalid_teachers_japanese_flg');
				$response['error']['message'] = __('teachers_japanese_flg must be 1 or 2');
				return json_encode($response);
			}
		}
		// trap order
		if (isset($queryValue['order'])) {
			if (isset($queryValue['order']) && !is_int($queryValue['order'])) {
				$response['error']['id'] = Configure::read('error.order_must_be_integer');
				$response['error']['message']  = __('order must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['order'], range(1,4))) {
				$response['error']['id'] = Configure::read('error.invalid_order');
				$response['error']['message']  = __('order must be 1 to 4');
				return json_encode($response);
			}
		}
		
		// trap lesson_course
		if (isset($queryValue['lesson_course'])) {
			if (isset($queryValue['lesson_course']) && !is_int($queryValue['lesson_course'])){
				$response['error']['id'] = Configure::read('error.lesson_course_must_be_integer');
				$response['error']['message'] = __('lesson_course must be integer');
				return json_encode($response);
			}
		}
		
		
		// trap pagination
		if (isset($queryValue['pagination'])) {
			if (isset($queryValue['pagination']) && !is_int($queryValue['pagination'])) {
				$response['error']['id'] = Configure::read('error.pagination_must_be_integer');
				$response['error']['message']  = __('pagination must be integer');
				return json_encode($response);
			} else if (is_string($queryValue['pagination']) || preg_match('/[^0-9]/', $queryValue['pagination']) || $queryValue['pagination'] <= 0) {
				$response['error']['id'] = Configure::read('error.pagination_must_be_greater_than_zero');
				$response['error']['message']  = __('pagination must be greater than 0');
				return json_encode($response);
			}
		}

		

		// trap lesson_from_at
		if (isset($queryValue['lesson_from_at'])) {
			if (isset($queryValue['lesson_from_at']) && !is_string($queryValue['lesson_from_at'])) {
				$response['error']['id'] = Configure::read('error.lesson_from_at_must_be_string');
				$response['error']['message']  = __('lesson_from_at must be string');
				return json_encode($response);
			}
		}

		//trap lesson_to
		if (isset($queryValue['lesson_to'])) {
			if (isset($queryValue['lesson_to']) && !is_string($queryValue['lesson_to'])) {
				$response['error']['id'] = Configure::read('error.lesson_to_at_must_be_string');
				$response['error']['message']  = __('lesson_to must be string');
				return json_encode($response);
			}
		}

		if(isset($queryValue['lesson_begin_at'])) {
			$queryValue['lesson_from_at'] = $queryValue['lesson_begin_at'];
			if (!is_int($queryValue['lesson_begin_at'])) {
				$response['error']['id'] = Configure::read('error.lesson_begin_at_must_be_integer');
				$response['error']['message']  = __('lesson_begin_at must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['lesson_begin_at'], range(1,6))) {
				$response['error']['id'] = Configure::read('error.invalid_lesson_begin_at');
				$response['error']['message'] = __('lesson_begin_at must be 1 to 6');
				return json_encode($response);
			}
		}

		//trap lesson_from and lesson_to
		if (!isset($queryValue['lesson_begin_at']) && isset($queryValue['lesson_from']) && !isset($queryValue['lesson_to'])) {
			$response['error']['id'] = Configure::read('error.lesson_to_at_is_required');
			$response['error']['message']  = __('lesson_to is required');
			return json_encode($response);
		}

		//trap lesson_from and lesson_to
		if (isset($queryValue['lesson_to']) && !isset($queryValue['lesson_from'])) {
			$response['error']['id'] = Configure::read('error.lesson_from_at_is_required');
			$response['error']['message']  = __('lesson_from is required');
			return json_encode($response);
		}

		//trap lesson_from and lesson_to difference		
		if (isset($queryValue['lesson_to']) && isset($queryValue['lesson_from'])) {
			if (strtotime($queryValue['lesson_from']) > strtotime($queryValue['lesson_to'])) {
				$response['error']['id'] = Configure::read('error.lesson_from_at_must_be_less_than_lesson_to_at');
				$response['error']['message']  = __('lesson_from must be less than lesson_to');
				return json_encode($response);
			}                          
		}


		//trap api_version
		if (isset($queryValue['api_version'])) {
			if (isset($queryValue['api_version']) && !is_int($queryValue['api_version'])){
				$response['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$response['error']['message'] = __('api_version must be integer');
				return json_encode($response);
			}
		}



		if (isset($queryValue['date'])) {
			if (isset($queryValue['date']) && !is_int($queryValue['date'])) {
				$response['error']['id'] = Configure::read('error.date_must_be_integer');
				$response['error']['message']  = __('date must be integer');
				return json_encode($response);
			} else if (!in_array($queryValue['date'], range(0,7))) {
				$response['error']['id'] = Configure::read('error.invalid_date');
				$response['error']['message'] = __('date must be 0 to 7');
				return json_encode($response);
			}
		}

		if (isset($queryValue['teacher_name_text'])) {
			if (isset($queryValue['teacher_name_text']) && !is_string($queryValue['teacher_name_text'])) {
				$response['error']['id'] = Configure::read('error.lesson_from_at_must_be_string');
				$response['error']['message']  = __('teacher_name_text must be string');
				return json_encode($response);
			}
		}

		$joins = $conditions = $fieldsQuery = array();
		$sortCondition = null;

		$joinConditions = $this->getSearchConditions($queryValue);
		$blockList = (!empty($user)) ? BlockListTable::getBlocks($user['id']) : array(0, 0);

		// default condition 
		$conditions['Teacher.status'] = 1;
		$conditions['Teacher.admin_flg'] = 0;

		// default sorting
		$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, Teacher__status_sort asc, Teacher.counseling_flg desc, Teacher.good_point DESC";

		$joins = array(
			array(
				'table' => 'teacher_ratings_lessons',
				'alias' => 'TeacherRatingsLessons',
				'type' => 'LEFT',
				'conditions' => 'TeacherRatingsLessons.teacher_id = Teacher.id'
			)
		);
		if (!empty($user)) {
			$joins[] = array(
				'table' => 'users_favorites',
				'alias' => 'User_Favorite',
				'type' => 'LEFT',
				'conditions' => 'User_Favorite.teacher_id = Teacher.id AND User_Favorite.user_id = '.$user['id'],
			);
		}
		$oUserData = new UserTable($user);
		$membership = (!empty($user)) ? $oUserData->getUserMembership() : null;

		// -- Search condition
		if (count($queryValue) != 0) {
			// ------ Keyword Row
			if (isset($queryValue['text'])) {
		
				$freewordArray = explode(" ", $queryValue['text']);
				foreach ($freewordArray as $keyWord) {
					$freewordConditions[] = array(
 						"
						(Teacher.educational_background like ?
						OR Teacher.hobby like ?
						OR Teacher.message like ?
						OR Teacher.staff_message like ?)
						" => array('%'.$keyWord.'%', '%'.$keyWord.'%', '%'.$keyWord.'%', '%'.$keyWord.'%')
					);
				}

				$conditions[]['OR'] = $freewordConditions;
			}
			// ------ Status Row 
			// Search teacher accrdng to status  : online, offline, available
			if (isset($queryValue['status']) && !empty($queryValue['status'])) {
				if ($queryValue['status'] == 1) {
					$conditions['OR']['AND']['LessonOnair.connect_flg'] = 1;
					$conditions['OR']['AND']['LessonOnair.status !='] = 0;
					$conditions['OR'][] = array(
						'AND' => array(
							'TeacherStatus.status' => array(4,5),
							'(select count(*) from lesson_onairs a
							 where a.teacher_id = Teacher.id
							 and a.connect_flg = 0)' => 0
						)
					);
				} else if ($queryValue['status'] == 2) {
					$conditions['LessonOnair.connect_flg'] = NULL;
					$conditions['Teacher.status_sort'] = 4;
				} else if ($queryValue['status'] == 3) {
					$conditions['LessonOnair.status'] = 1;
					$conditions['LessonOnair.connect_flg'] = 1;
				}
			}

			// ------ favorite
			if (!empty($queryValue['favorite']) && $queryValue['favorite']) {
				$fid = (!empty($user)) ? $this->getMyFavorites($user['id']) : null;
				if (isset($fid)) {
					$conditions['Teacher.id'] = (count($fid) == 1)? array(0,implode($fid)) : ((count($fid) == 0)? array(0,0) : $fid);
				}
			}

			// ------ Gender Row
			if (!empty($queryValue['teachers_gender'])) {
					$conditions['AND']['Teacher.gender'] = $queryValue['teachers_gender'];
			}
			// ------ Age Row
			// NC-3458 change age flags.
			if (!empty($queryValue['age'])) {
				if ($queryValue['age'] == 1) {
					// Age 10's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(20);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(10);
				} else if ($queryValue['age'] == 2) {
					// Age 20's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(30);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(10); // NC-3582 - Teachers in 20's and 10's. because there are few teacher's who age == 10's
				} else if ($queryValue['age'] == 3) {
					// Age 30's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(40);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(30);
				} else if ($queryValue['age'] == 4) {
					// Age 40's
					$conditions['AND']['DATE(Teacher.birthday) >='] = $this->birthday(50);
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(40);
				} else {
					// Age 50' and above.
					$conditions['AND']['DATE(Teacher.birthday) <='] = $this->birthday(50);
				}
			}
			
			// ------- Japanese Flag row
			if (!empty($queryValue['teachers_japanese_flg'])) {
					if ($queryValue['teachers_japanese_flg'] == 1) {
						$conditions['AND']['Teacher.japanese_flg'] = 0;
					} else {
						$conditions['AND']['Teacher.japanese_flg'] = 1;
					}
			}
			if (!empty($queryValue['teacher_name_text'])) {
				$conditions[]['OR'] = array(
							'Teacher.jp_name LIKE' => '%'.$queryValue['teacher_name_text'].'%',
							'Teacher.name LIKE' => '%'.$queryValue['teacher_name_text'].'%'
				);
			}

			$joins[] = array(
					'table' => 'teacher_rank_coins',
					'alias' => 'TeacherRankCoins',
					'type' => 'LEFT',
					'conditions' => 'TeacherRankCoins.id = Teacher.rank_coin_id',
			);

			if (isset($queryValue['features'])) {
				if (isset($queryValue['features']['callan_halfprice_flg'])) {
					$conditions[] = array('Teacher.callan_halfprice_flg' => $queryValue['features']['callan_halfprice_flg']);
				}
				if (isset($queryValue['features']['beginner_teacher_flg'])) {
					$conditions[] = array('Teacher.beginner_teacher_flg' => $queryValue['features']['beginner_teacher_flg']);
				}
				if (isset($queryValue['features']['free_talk_flg'])) {
					$conditions[] = array('TeacherFeature.best_free_talk' => $queryValue['features']['free_talk_flg']);
				}
				if (isset($queryValue['features']['native_speaker_flg'])) {
					$conditions[] = array('Teacher.native_speaker_flg' => $queryValue['features']['native_speaker_flg']);
				}
				if (isset($queryValue['features']['japanese_flg'])) {
					$conditions[] = array('Teacher.japanese_flg' => $queryValue['features']['japanese_flg']);
				}
				if (isset($queryValue['features']['suitable_for_children_flg'])) {
					$conditions[] = array('TeacherFeature.suitable_for_children' => $queryValue['features']['suitable_for_children_flg']);
				}
			}


			if (!empty($queryValue['nationality'])) {
				$conditions['Teacher.homeland2'] = $queryValue['nationality']; 
			}

			//coin amount filters
			if (isset($queryValue['coin']) && $queryValue['coin']) {
				$coinCond = array();
				foreach ($queryValue['coin'] as $coin) {
					$coinCond[]['TeacherRankCoins.coins'] = $coin;
				}
				$conditions[]['OR'] = $coinCond;
			}

			if ( isset($queryValue['lesson_from']) ) {
				
				if (!empty($queryValue['lesson_from']) && !empty($queryValue['lesson_to']) ){
					$startDate = date('Y-m-d H:i:s', strtotime($queryValue['lesson_from']));
					$endDate = date('Y-m-d H:i:s', strtotime($queryValue['lesson_to']));
				} else {
					$startDate = date('Y-m-d H:i:s', strtotime('Y-m-d 00:00:00'));
					$endDate = date('Y-m-d H:i:s', strtotime('Y-m-d 24:00:00'));
				}

				$newDate = $this->newStartDate($startDate);

				if ( $newDate == $endDate || $newDate > $endDate ) {
					// NC-5036 : skip SQL
					// Mid night heavy query
					$conditions['Teacher.id'] = Configure::read('counselor.connect_id'); // show only counselor
				} else {
				// ShiftWorkon and ShiftWorkHideDay

				$conditions[] = 'Teacher.id IN (( SELECT ShiftWorkon.teacher_id FROM 
					`english`.`shift_workons` AS `ShiftWorkon` 
					LEFT JOIN `english`.`lesson_schedules` AS `LessonSchedule` ON (`LessonSchedule`.`teacher_id` = `ShiftWorkon`.`teacher_id`
							AND `LessonSchedule`.`lesson_time` = `ShiftWorkon`.`lesson_time`
							AND `LessonSchedule`.`status` IN (1 , 0)
							)
					LEFT JOIN
						`english`.`shift_work_hide_dates` AS `ShiftWorkHideDay` 
						ON (
							(`ShiftWorkon`.`teacher_id` = `ShiftWorkHideDay`.`teacher_id`
							AND `ShiftWorkon`.`lesson_time` = `ShiftWorkHideDay`.`lesson_time`
							AND `ShiftWorkHideDay`.`schedule_type` = 1)
							OR 
							(`ShiftWorkon`.`teacher_id` = `ShiftWorkHideDay`.`teacher_id`
							AND `ShiftWorkHideDay`.`lesson_time` BETWEEN \'' . date('Y-m-d 00:00:00', strtotime($startDate)) . '\' AND \'' . date('Y-m-d 23:30:00', strtotime($startDate)) . '\' 
							AND `ShiftWorkHideDay`.`schedule_type` = 2)
							)
					WHERE 
							(`ShiftWorkon`.`lesson_time` >= "'.$newDate.'")
							AND (`ShiftWorkon`.`lesson_time` < "'.$endDate.'")
							AND (`LessonSchedule`.`lesson_time` IS NULL)
							AND (`ShiftWorkHideDay`.`id` IS NULL) ))';
				}

				// display only teacher with reservation hide flg 0
				$conditions['Teacher.reservation_hide_flg'] = 0;
			}

			//for block users
			$conditions['Teacher.id NOT IN'] = $blockList;

			$statusOrder = "Teacher__status_sort asc,";
			if (isset($queryValue['api_version'])) {
				if ( isset($queryValue['lesson_from']) ) {
					$statusOrder = 	"";
				}
			}
			// ------ Sort Row
			if (!empty($queryValue['order'])) {
				if ($queryValue['order'] == 1) {  // Sort teacher by login last_login_time in DESC
					 $sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, Teacher.last_login_time DESC, Teacher.name ASC";
				} else if ($queryValue['order'] == 2) {  // Sort teacher by name in ASC
					if ($statusOrder == '') {
						$sortCondition = "Teacher__counselor_online DESC, ".$statusOrder." Teacher.counseling_flg desc, TeacherRatingsLessons.ratings DESC, lesson_counts DESC";
					} else {
						$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, TeacherRatingsLessons.ratings DESC, lesson_counts DESC, Teacher.name ASC";
					}
				} else if ($queryValue['order'] == 3) {   // Sort teacher by number of good lesson in DESC
					$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, lesson_counts DESC, Teacher.name ASC";
				} else if ($queryValue['order'] == 4) {   // Sort teacher by name in ASC
					$sortCondition = "Teacher__counselor_online DESC, Teacher__native_now DESC, ".$statusOrder." Teacher.counseling_flg desc, Teacher.name ASC";
				}
			}
		}
		$userFavorite =  (!empty($user)) ? "(CASE WHEN LENGTH(User_Favorite.id) > 0 THEN true ELSE false END) AS is_favorite" : '';
		$lessonConnectflg = "(CASE WHEN LENGTH(LessonOnair.status) is null THEN '' ELSE 1 END) AS connect_flg";
		$limit = 100;
		$pagination = (isset($queryValue['pagination']) && !empty($queryValue['pagination']) && (int)$queryValue['pagination'] >= 1) ? (int)$queryValue['pagination'] : 1;
		$offset = ($pagination - 1) * $limit;

		# has next reservation
		$reservationCondition = array(
			'LessonSchedule.user_id' => isset($user['id']) ? $user['id'] : null
		);
		$nextReservation = (!empty($user)) ? $this->api->getReservation($reservationCondition) : array('teacher_id' => null);
		$nativeTeacherRankIds = implode(",", Configure::read('native_teacher_rank_ids'));
		$this->Teacher->virtualFields = array(
			'status_sort' => 'CASE WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0 AND LessonOnair.status = 1 THEN 1
												WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0 AND LessonOnair.status = 2 AND Teacher.id = "'.$nextReservation['teacher_id'].'" THEN 1
												WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0 AND LessonOnair.status = 2 OR LessonOnair.status = 3 THEN 3
												WHEN LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg = 0 THEN 4
												WHEN TeacherStatus.status IS NOT NULL AND (TeacherStatus.status = 4 AND TeacherStatus.remarks1 NOT IN (1,4) OR TeacherStatus.status = 5 OR TeacherStatus.remarks2 LIKE "after_lesson_other") THEN 2
												WHEN TeacherStatus.status IS NOT NULL AND (TeacherStatus.status = 1 OR TeacherStatus.status = 3 OR (TeacherStatus.status = 4 AND TeacherStatus.remarks1 IN (1,4))) THEN 3
												ELSE 4 END
												',
			'counselor_online' => 'CASE
										WHEN Teacher.counseling_flg = 1 AND ((LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0) OR TeacherStatus.status = 4) THEN 1
										ELSE 0 END
									',
			'native_now' => 'CASE
								WHEN Teacher.rank_coin_id IN ('.$nativeTeacherRankIds.') AND (LessonOnair.status IS NOT NULL AND LessonOnair.connect_flg != 0) THEN 1
								ELSE 0 END
									'
		);
		$fieldsQuery = array(
				'Teacher.native_speaker_flg',
				'Teacher.homeland2',
				'Teacher.good_point',
				'Teacher.counseling_flg',
				'LessonOnair.connect_flg',
				'LessonOnair.status',
				'TeacherRatingsLessons.ratings',
				'TeacherRatingsLessons.lessons',
				'TeacherStatus.remarks1',
				'TeacherStatus.remarks2',
				'Teacher.status_sort',
				'Teacher.native_now',
				'((Teacher.lesson_count) + 0) AS lesson_counts',
				'TeacherRankCoins.id',
				'TeacherRankCoins.coins'
			);

		if (isset($queryValue['lesson_course']) && !empty($queryValue['lesson_course'])){
			$joins[] = array(
				'table' => 'teacher_badges',
				'alias' => 'TeacherBadge',
				'conditions' => 'TeacherBadge.teacher_id = Teacher.id',
				'type' => 'INNER'
			);
			$conditions['TeacherBadge.textbook_category_id'] = $queryValue['lesson_course'];
		}
		
		$conditions['Teacher.stealth_flg'] = 0; // Temporary HOTFIX NC-1277 (16/03/16 Onishi)
		$query_conditions = array(
			'fields' => array_merge($fieldsQuery, array($userFavorite)),
			'joins' => $joins,
			'conditions' => array($conditions),
			'changeAlias' => array('TeacherStatus.status AS teacher_status' => 'TeacherStatus.status'),
			'order' => $sortCondition,
			'limit' => $limit+1,
			'offset' => $offset
	 	);

	 	$conditionArr = array('joinConditions' => $joinConditions);
		$paramArr = array(
			"query_conditions" => $query_conditions,
			"val" => null
		);
		$TeacherOnlineList = $this->CommonTeacherStatus->getAllStatusApi($paramArr);
		$this->log(" TEACHER_LIST : " . json_encode($TeacherOnlineList), "debug");
		$data = array();
		$teacher_arr = array();
		$count = 0;

		// $nextReservation = $this->nextReservation($userId['id']);
		foreach ($TeacherOnlineList as $tol) {
			if ($count <= 99) {
				//exclude if teacher is counselor
				if (intval($tol['Teacher']['counseling_flg'])) {
					continue;
				}
				$teacher = new TeacherTable($tol['Teacher']);
				$TeacherStatus = new TeacherTable($tol['TeacherStatus']);

				$statusFlag = $this->api->teacherStatusColor(
					array(
						'LessonOnair' => $tol['LessonOnair'],
						'Teacher' => $teacher,
						'TeacherStatus' => $TeacherStatus,
						'nextReservation' => isset($nextReservation['teacher_id']) ? $nextReservation['teacher_id'] : null,
						'userId' => $user['id']
					)
				);
				$countries = strtolower($tol['CountryCode']['country_name']);
				$explodeCountries = explode(' ', $countries);
				$implode = implode('_',$explodeCountries);

				//native teacher is online
				$nativeNowFlg = 0;
				if (isset($tol['LessonOnair']['status']) && isset($tol['TeacherRankCoins']['id'])) {
					$nativeNowFlg = in_array($tol['TeacherRankCoins']['id'], array(13,19)) ? 1 : 0;
				}
				
				//if api version is greater than or equal to 17
				$checkCountryCode = (empty($params['native_language2']) && !empty($queryValue['user_language'])) ? $queryValue['user_language'] : $params['native_language2'];
				$getUserSettingLanguage2 = ( $checkCountryCode == 'ja' || empty($checkCountryCode) ) ? $tol['Teacher']['jp_name'] : '' ;
				
				$arr = array(
					'id' => (int)$tol['Teacher']['id'],
					'name' => $getUserSettingLanguage2,
					'name_ja' => $tol['Teacher']['jp_name'],
					'name_eng' => $tol['Teacher']['name'],
					'status' => $statusFlag,
					'coin' => is_null($tol['TeacherRankCoins']['coins']) ? 0 : $tol['TeacherRankCoins']['coins'],
					'rating' => is_null($tol['TeacherRatingsLessons']['ratings']) || $tol['TeacherRatingsLessons']['ratings'] == 0 ? null : round($tol['TeacherRatingsLessons']['ratings'], 2),
					'lessons' => (int)($tol[0]['lesson_counts']),
					'goods' => (int)$tol['Teacher']['good_point'],
					'favorite' => (isset($tol[0]['is_favorite'])) ? (int)$tol[0]['is_favorite'] : false,
					'nationality_id' => (int)$tol['CountryCode']['id'],
					'native_speaker_flg' => (int)$tol['Teacher']['native_speaker_flg'],
					'suitable_for_children_flg' => isset($tol['TeacherFeature']['suitable_for_children']) ? (int)$tol['TeacherFeature']['suitable_for_children'] : 0,
					'callan_discount_flg' => $tol['Teacher']['callan_halfprice_flg'] ? true : false,
					'beginner_teacher_flg' => $tol['Teacher']['beginner_teacher_flg'] ? 1 : 0,
					'free_talk_flg' => (int)$tol['TeacherFeature']['best_free_talk'],
					'image_main' => $teacher->getImageUrl(),
					'country_image' => FULL_BASE_URL."/user/images/flag/".$implode.".png",
					'native_now_flg' => $nativeNowFlg,
					'message' => $teacher->getTranslateMessage(),
					'message_ja' => $teacher->getTranslateMessage(),
					'staff_message_ja' => $teacher->getTranslateSelfIntroductionThirdPp(),
					'message_eng' => $teacher->message_short,
					'staff_message_eng' => $teacher->getSelfIntroductionThirdPp()
				);	
				
				unset($arr['name_ja']);
				unset($arr['message_ja']);
				unset($arr['staff_message_ja']);
				unset($arr['staff_message_eng']);
				$arr['counseling_flg'] = 0;
				array_push($teacher_arr, $arr);
			}
			$count++;
			if ($nativeNowFlg) {
				$nativeNowTeachers = array_keys($teacher_arr);
			}
		}

		//add counselor on top of the list
		if ($pagination == 1) {
			unset($this->Teacher->virtualFields);//unset virtual fields to prevent error
			$userId = isset($user['id']) ? $user['id'] : null;
			$counselorData = $this->Teacher->counselorDisplay(array('user_id' => $userId,'api_version' => $apiVersion,'native_language2' => $params['native_language2'], 'user_language' => $queryValue['user_language']));
			//prepend counselor
			if ($counselorData) {
				if ($statusOrder == '' || empty($nativeNowTeachers)) {
					array_unshift($teacher_arr, $counselorData);
				} else {
					// append counselor after native now teachers
					$index = end($nativeNowTeachers) + 1;
					$res = array_splice($teacher_arr, $index, 0,  array($index => $counselorData));
					$teacher_arr = $teacher_arr + $res;
				}
			}
		}
		$response['teachers'] = $teacher_arr;

		if(count($TeacherOnlineList) > 10) {
			$response['has_next'] = true;
		} else {
			$response['has_next'] = false;
		}
		return json_encode($response);
	}
}
