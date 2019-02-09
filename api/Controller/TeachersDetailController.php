<?php
/********************************
*																*
*	Lesson Start for API 					*
*	Author: John Mart Belamide		*
*	January 2016									*
*																*
********************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTranslator','Lib');
class TeachersDetailController extends AppController {

	public $uses = array(
		'TeacherImage',
		'User',
		'UsersClassEvaluation',
		'UsersFavorite',
		'TeacherFeature',
		'TeacherStatus',
		'LessonOnairsLog',
		'LessonSchedule',
		'LessonScheduleCancel',
		'CommonTeacherStatus',
		'TeacherRankCoin',
		'TextbookCategory',
		'Textbook',
		'LessonOnair',
		'TeacherBadge',
		'ShiftWorkOn',
		'ShiftWorkHideDate',
		'CountryCode'
	);

	private $api = null;

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('detail'));
		$this->api = new ApiCommonController();
	}

	public function detail() {
		$Validate = $this->api;
		$this->autoRender = false;
		$json = array();
		$lang = $Schedule = $apiVersion = $nativeLanguage = $suportedlang = null;
		if ($this->request->is('post')) {
			$reqData = @json_decode($this->request->input(), true);

			if (isset($reqData['users_api_token']) && trim($reqData['users_api_token']) == "") {
				$json['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
				$json['error']['message'] = __('users_api_token can not be empty');
				return json_encode($json);
			}
			$user = (!empty($reqData['users_api_token'])) ? $Validate->validateToken($reqData['users_api_token']) : null;
			if (!empty($reqData['users_api_token']) && empty($user)) {
				 $json['error']['id'] = Configure::read('error.invalid_api_token');
				 $json['error']['message'] = $Validate->error;
			     return json_encode($json);

		    } else if (!$Validate->validateTeachersId($reqData['teachers_id'])) {
				 $json['error']['id'] = Configure::read('error.invalid_teachers_id');
				 $json['error']['message'] = $Validate->error;
			     return json_encode($json);
		    }

			$options = array(
				array(
					'table' => 'lesson_onairs',
					'type' => 'LEFT',
					'alias' => 'LessonOnair',
					'conditions' => array('LessonOnair.teacher_id = Teacher.id')
				)
			);
			if (!empty($user) && $Validate->checkBlocked($reqData['teachers_id'],$user['id'])){
				$json['error']['id'] = Configure::read('error.missing_teacher');
				$json['error']['message'] = __($Validate->missing_teacher);
				return json_encode($json);
			}

			if (isset($reqData['api_version']) && !is_int($reqData['api_version'])) {
				$json['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$json['error']['message'] = __('api_version must be integer');
				return json_encode($json);		
			}

			$query_conditions = array(
				'show' => 'first',
				'changeAlias' => array('TeacherStatus.status AS teacher_status' => 'TeacherStatus.status'),
				'fields' => array(
					'Teacher.homeland2',
					'Teacher.hobby as hobbies',
					'Teacher.good_point as goods',
					'(select count(teacher_id) from lesson_onairs_logs where teacher_id = Teacher.id)  as lesscount',
					'TeacherStatus.status',
					'TeacherStatus.remarks2'
				),
				'joins' => $options,
				'conditions' => array(
					array('Teacher.id' => $reqData['teachers_id'])
				),
			);

			$result = $this->CommonTeacherStatus->getAllStatus('listTeacher',$query_conditions);

			if (!empty($result)) {
				$teacher = new TeacherTable($result['Teacher']);
				$nativeLanguage = (isset($user['native_language2']) && $user['native_language2'] != '') ? $user['native_language2'] : Configure::read('default.user_language');
				$apiVersion = isset($reqData['api_version']) ? $reqData['api_version'] : null;
				if (isset($apiVersion)) {
					// Not logged in
					if ( !isset($reqData['users_api_token']) && isset($reqData['user_language']) ) {
						$lang = strtolower($reqData['user_language']);
						$nativeLanguage = in_array( $lang, array("ja","ko","th") ) ? $lang : "en";
					}
					$suportedlang = 'jpn';
					if (isset($nativeLanguage) && $nativeLanguage) {
						$suportedlang = $this->CountryCode->commonMemcachedCountryCode();
						$suportedlang = $suportedlang[$nativeLanguage]["iso_639_2"];
						$lang = $nativeLanguage;
					}
				}

				$features = $this->__getTeacherFeature($result['Teacher']['id'],$teacher,$apiVersion,$nativeLanguage,$suportedlang); // call method to get teacher features
				$features = (empty($features))? null : $features;
 				$hobbies = (empty($result['Teacher']['hobbies']))? null : explode(",",$result['Teacher']['hobbies']);
 				$this->UsersClassEvaluation->openDBReplica();
 				$choseAge = $this->UsersClassEvaluation->getRatings($reqData['teachers_id'], true); // array of ages // array of ages
 				$this->UsersClassEvaluation->closeDBReplica();
 				$choseAge = (empty($choseAge))? 0 : $choseAge;
 				$rates = new UsersClassEvaluationTable($choseAge[0][0]);
				$rateBreakdown = new UsersClassEvaluationTable($choseAge[0]['TeacherRatingsLesson']);

				// new value for rate
				$averageRate = $rateBreakdown->getAverageRate();

				$chooseAge = array(
					$rates->getPercent('one'),
					$rates->getPercent('two'),
					$rates->getPercent('three'),
					$rates->getPercent('four'),
					$rates->getPercent('five'),
					$rates->getPercent('six'),
					$rates->getPercent('seven')
				);
				// if reservation_hide_flg is 0, display opened reservation slots
				if (!$teacher->reservation_hide_flg) {
					$Schedule = $this->_posibleReservationList((!empty($user['id']) ? $user['id'] : null), $teacher->id);
				}

				$TeacherStatus = new TeacherTable($result['TeacherStatus']);

				if (!empty($user)) {
					// get reservation
					$nextReservation = $this->api->getReservation(array(
						'LessonSchedule.teacher_id' => $teacher->id,
						'LessonSchedule.user_id' => $user['id']
					));
				} else {
					$nextReservation = array('teacher_id' => null);
				}
				$teacherStatusColorParams = array(
					'LessonOnair' => $result['LessonOnair'],
					'Teacher' => $teacher,
					'TeacherStatus' => $TeacherStatus,
					'nextReservation' => isset($nextReservation['teacher_id']) ? $nextReservation['teacher_id'] : null,
					'userId' => $user['id']
				);

				$status = $this->api->teacherStatusColor($teacherStatusColorParams);

				// get teacher rank coins
				$coin = $this->getTeacherReservationCoin($teacher->rank_coin_id);

				// remove text that is for pc only.
				$staffMessage = $this->stripSelectedTagsContent($teacher->staff_message, array(array('span', 'class="pconly"')));
				// fetch number of days past from last lesson for teacher
				$selfDaysPast = 0;
				if (isset($user['id'])) {
					$selfDaysPast = $this->LessonOnairsLog->countDaysPast($user['id'], $teacher->id);
					$selfDaysPast = $selfDaysPast >= 0 ? $selfDaysPast : 0;
				}
				//reservation and cancellation breakdown
				$reserveAndCancel = $this->getReserveAndCancelled($teacher->id);
				// state button option status
				$teacherStatus = ( isset($result['TeacherStatus']['status']) && $result['TeacherStatus']['status'] != null ) ? $result['TeacherStatus']['status'] : null ;
				$hasOtherReservation = $this->LessonSchedule->hasOtherReservation($user['id'],$teacher->id);
				$stateOption = $this->stateStatus(array(
						"user_data" => $user,
						"teacher_data" => (array)$teacher,
						"status" => $status,
						"teacher_status" => $teacherStatus,
						"hasOtherReservation" => $hasOtherReservation,
						"api_version" => $apiVersion
					)
				);

				$beforeHistory = $teacher->getInstructorHistory();

				//get current time
				$nowDatetime = strtotime(date('Y-m-d'));
				$nowMonth = date("Y",$nowDatetime)*12 + date("m",$nowDatetime);
				//teacher's registration time
				$teacherCreated = strtotime($teacher->created);
				$createdMonth = date("Y",$teacherCreated)*12 + date("m",$teacherCreated);
				$instructorHistory = ($nowMonth - $createdMonth) + $beforeHistory;
				$historyYear = floor($instructorHistory / 12);
				$historyMonth = $instructorHistory % 12;
				$enYear = $historyYear > 1 ? "years" : "year";
				$enMonth = $historyMonth > 1 ? "months" : "month";				
				$apiVersion = isset($reqData['api_version'])? $reqData['api_version'] : 0;
				$setNativeLanguage = $nativeLanguage;
				if ($nativeLanguage == Configure::read('english_language')) {
					$setNativeLanguage = Configure::read('default.user_language');
				}

				$this->loadModel('Translation');
				$translationCategories = Configure::read('translation_categories');
				$translateTextParams = array(
					'languageCode' => $setNativeLanguage,
					'messageId' => $teacher->id
				);

				if (isset($teacher->message)  && !empty($teacher->message)) {
					$translateTextParams['text'] = $teacher->message;
					$translateTextParams['categoryId'] = $translationCategories['teacher_message'];
					$translatedMessageTranslation = $this->Translation->translateText($translateTextParams);
				} else {
					$translatedMessageTranslation = !empty($teacher->translate_message) ? $teacher->translate_message : null;
				}

				if (isset($teacher->self_introduction_third_pp) && !empty($teacher->self_introduction_third_pp)) {
					$translateTextParams['text'] = $teacher->self_introduction_third_pp;
					$translateTextParams['categoryId'] = $translationCategories['teacher_self_introduction_third_pp'];
					$googleTranslator = new myTranslator();
					$translatedThirdppTranslation = $this->Translation->translateText($translateTextParams);
				} else {
					$translatedThirdppTranslation = !empty($teacher->translate_self_introduction_third_pp) ? $teacher->translate_self_introduction_third_pp : null;
				}

				$countries = strtolower($result['CountryCode']['country_name']);
				$explodeCountries = explode(' ', $countries);
				$implode = implode('_',$explodeCountries);
					
				Configure::write('Config.language',$suportedlang);
				$this->Session->write('Config.language',$suportedlang);

				if($teacher->counseling_flg != 1) {
					if ($historyYear == 0 && $historyMonth == 0) {
						if ($suportedlang == "eng") {
							$instructor_history = "1 ".$enMonth;
						} else {
							$instructor_history = "1".__d('instructor_history','ヶ月');
						}
					} elseif($historyMonth == 0) {
						if ($suportedlang == "eng") {
							$instructor_history = $historyYear." ".$enYear;
						} else {
							$instructor_history = $historyYear.__d('instructor_history','年');
						}
					} elseif ($historyYear == 0) {
						if ($suportedlang == "eng") {
							$instructor_history = $historyMonth." ".$enMonth;
						} else {
							$instructor_history = $historyMonth.__d('instructor_history','ヶ月');
						}
					} else {
						if ($suportedlang == "eng") {
							$instructor_history = $historyYear." ".$enYear." ".$historyMonth." ".$enMonth;
						} else {
							$instructor_history = $historyYear.__d('instructor_history','年').$historyMonth.__d('instructor_history','ヶ月');
						}
					}
				}

				//if api version is greater than or equal to 17
				$checkCountryCode = (!$user['native_language2']) ? 'ja' : $user['native_language2'];
				$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $teacher->jp_name : '';
				$badgeParam = array(
					"teacher_id" => $teacher->id,
					"api_version" => $reqData['api_version'],
					"lang" => $lang
				);

				$json = array(
					'teacher' => array(
						'id' => (int)$teacher->id,
						'name' => $getUserSettingLanguage,
						'name_ja' => $teacher->jp_name,
						'name_eng' => $teacher->name,
						'age' => (int)$teacher->getAge(),
						'instructor_history' => (isset($instructor_history))? $instructor_history : null,
						'status' => $status,
						'coin' => (int)$coin,
						"rating" => ($averageRate == '-') ? null : (float)$averageRate,
						'lessons' => (int)$teacher->lesson_count,
						'reserve_count' => (int)$this->LessonSchedule->countTeacherReservedLessons(array('teacherId' => $teacher->id)),
						'selfLessonCount' => isset($user['id']) ? $this->LessonOnairsLog->countOwnLessons($user['id'], $teacher->id) : 0,
						'selfDaysPast' => $selfDaysPast,
						'state_button' => $stateOption,
						'goods' => (int)$teacher->goods,
						'favorite' => $this->__checkFave((!empty($user['id']) ? $user['id'] : null) ,$teacher->id),
						'nationality_id' =>(int) $result['CountryCode']['id'],
						'native_speaker_flg' => (int)$teacher->native_speaker_flg,
						'callan_discount_flg' => $teacher->callan_halfprice_flg ? true : false,
						'beginner_teacher_flg' => $teacher->beginner_teacher_flg,
						'message' => $teacher->message,
						'message_translation' => $translatedMessageTranslation,
						"features" => $features,
						"hobbies" => $hobbies,
						'staff_message' => $teacher->self_introduction_third_pp,
						'staff_message_translation' => $translatedThirdppTranslation,
						"evaluation" => array(
							"count" => $rates->getEvaluationCount(),
							"value" => $rateBreakdown->getAverageRate(),
							"comment_count" => $this->UsersClassEvaluation->getCommentCount($teacher->id),
							"self_comment_count" => (!empty($user)) ? $this->UsersClassEvaluation->getSelfCommentCount($teacher->id, $user['id']) : null
						),
						'choosen_for_age' => $chooseAge,
						'ratings' => array((int)$rateBreakdown->rate1, (int)$rateBreakdown->rate2, (int)$rateBreakdown->rate3, (int)$rateBreakdown->rate4,(int) $rateBreakdown->rate5),
						'images' => array(
							'main' => $teacher->getImageUrl(),
							'album' => $this->_getAlbum($reqData['teachers_id'])
						),
						'youtube_tag' => $teacher->youtube_tag,
						'country_image' => FULL_BASE_URL."/user/images/flag/".$implode.".png",
						'counseling_flg' => $teacher->counseling_flg,
						'last_lesson_loading' => $this->api->getLastLessonLoading($user['id'], $teacher->id, $status),
						'open_reservations' => $Schedule,
						'joinable_short_lesson' => $this->getJoinableShortLesson(array(
								'User' => $user,
								'Teacher' => $teacher,
								'TeacherStatus' => $TeacherStatus,
								'OnAir' => $result['LessonOnair']
							)
						),
						'course_badge' => $this->getBadges($badgeParam)
					)
				);
				unset($json['teacher']['name_ja']);	
				//check if reservation if callan variant (callan level check, callan method or callan for business)
				if ($status == 5 && (isset($nextReservation['textbook_category_type']) && in_array($nextReservation['textbook_category_type'], array(2,5)))) {
					$json['teacher']['reserved_lesson_type'] = 'callan';
				} else {
					$json['teacher']['reserved_lesson_type'] = null;
				}
				$json['teacher']['reserve_and_cancel'] = array(
						'this_month_reserved' => $reserveAndCancel['this_month_reserved'],
						'this_month_cancellation_rate' => $reserveAndCancel['this_month_cancellation_rate'],
						'last_month_reserved' => $reserveAndCancel['last_month_reserved'],
						'last_month_cancellation_rate' => $reserveAndCancel['last_month_cancellation_rate']
					);
			}

		} else {
			$json['error']['id'] = Configure::read('error.invalid_request');
			$json['error']['message'] = __('Invalid request');
		}

		// remove open_reservations if api_version is greater than 1
		if (isset($reqData['api_version'])) {
			unset($json['teacher']['open_reservations']);
		}

		return json_encode($json);
	}

	/**
	 * Get teachers badges
	 * @param
	 * int $teacherId
	 * @return
	 * array badges
	*/
	public function getBadges( $params = array() ) {

		$teacherId = isset($params["teacher_id"]) ? $params["teacher_id"] : null;
		$apiVersion = isset($params["api_version"]) ? $params["api_version"] : null;
		$lang = isset($params["lang"]) ? $params["lang"] : null;

		#get course and badge
		$series = $this->TextbookCategory->useReplica()->find('all', array(
			'fields' => array(
				'TextbookCategory.name',
				'TextbookCategory.id',
				'TeacherBadge.textbook_category_id',
				'TeacherBadge.badge_flg',
				'TextbookCategory.english_name',
				'TextbookCategory.sort'
			),
			'conditions' => array(
				'TextbookCategory.status' => 1,
				'TextbookCategory.type_id' => 2
			),
			'joins' => array(
					array(
							'table' => 'teacher_badges',
							'alias' => 'TeacherBadge',
							'type' => 'LEFT',
							'conditions' => array('TeacherBadge.textbook_category_id = TextbookCategory.id', 'TeacherBadge.teacher_id' => $teacherId)
						),

			),
      		'order' => array('TextbookCategory.sort' => 'ASC')
		));

		$badges = TeacherBadgeTable::getBadges();

		if ($badges) {
			//get badge list
			foreach ($badges as $data) {
				if (isset($data['TextbookCategory']['id'])) {
					$textbookCategoryIdArr = Configure::read('allowed_textbooks_for_foreign');
					$foreignLangArr = Configure::read('allowed_foreign_lang');
					$checkLang = ($lang != null && in_array($lang, $foreignLangArr));
					// NC-4896 : Selected textbooks for Thailand(th) and Korea(ko)
					if ( $checkLang ) {
						if ( in_array($data['TextbookCategory']['id'], $textbookCategoryIdArr) ) {
							$badge[$data['TextbookCategory']['id']][] = array(
								'name' => isset($data['TextbookCategory']['name']) ? $data['TextbookCategory']['name'] : ''
							);
						}
					} else {
							$badge[$data['TextbookCategory']['id']][] = array(
								'name' => isset($data['TextbookCategory']['name']) ? $data['TextbookCategory']['name'] : ''
							);
					}
				}
			}
		}

		$badgesArr = array();
		if (isset($series)) {
		    foreach ($series as $item) {
		        //check if TextbookCategory array exists, skip if doesn't exist
		        if (!isset($item['TextbookCategory'])) {
 		            continue;
		        }
		        $series = new TextbookCategoryTable($item['TextbookCategory']);
		        $badged = $item['TeacherBadge'];
			    if (isset($series->id) && isset($series->sort) && isset($badge[$series->id])) {
	        		if ($badged['badge_flg']) {
 		            	$badgesArr[] = isset($series->id) ? (int) $series->id : '';
	        		}
			    }
		    }
		}

		return $badgesArr;
	}

	/**
	 * Get count of Evalution and Stars
	 * @param array $params
	 * @return int $status
	 */
	private function getStatus($params) {
		$teacher = $params['Teacher'];
		$ts = $params['TeacherStatus'];

		$LOA_status = $params['lo_status'];
		$LOA_connectflg = $params['connect_flg'];
		$statusCheck = array(1, 4);
		$result = 4;

		if (!empty($LOA_status) && $LOA_connectflg <> 0) {
			if ($LOA_status == 1) {
				$result = 1;
			} else if ($LOA_status == 2 && $this->reservedByMe($params)) {
				$result = 5;
			} else if ($LOA_status == 2 || $LOA_status == 3) {
				$result = 3;
			}
		} else {
			if (!empty($LOA_status) && $LOA_connectflg == '0') {
				$result = 4;
			} elseif (!empty($ts->status) && (($ts->status == 4 && !in_array($ts->remarks1, $statusCheck)) || $ts->status == 5)) {
				$result = 2;
			}  elseif (!empty($ts->status) && ($ts->status == 1 || $ts->status == 3 || ($ts->status == 4 && in_array($ts->remarks1, $statusCheck)))) {
				$result = 3;
			} else {
				$result = 4;
			}
		}
		return $result;
	}

	/**
	 * Check if time is 5 mins/less before new lesson
	 * @return boolean
	 */
	private function preperationTime() {
		$time = ceil(time() / (30 * 60)) * (30 * 60);
		if ($time - time() < 300)return true;
		return false;
	}

	/**
	 * Check reservation if belongs to this student
	 * @param array $params
	 * @return boolean
	 */
	private function reservedByMe($params) {
		$lesson_time = $this->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
		$lesson_time = date('Y-m-d H:i:s',$lesson_time);
		$conditions = array(
			'LessonSchedule.lesson_time' => $lesson_time,
			'LessonSchedule.teacher_id' => $params['teacher_id'],
			'LessonSchedule.user_id' => $params['user_id'],
			'LessonSchedule.status' => 1
		);
		$schedule = $this->LessonSchedule->find('first',array(
			'fields' => array('LessonSchedule.id'),
			'conditions' => $conditions,
			'recursive' => -1
			)
		);
		return isset($schedule['LessonSchedule']) ? true : false;
	}

	/**
	 * get User id by token
	 * @param string $api_token - user current token
	 * @return unknown|boolean
	 */
	public function getApiToken($api_token = null) {

		$result = $this->User->useReplica()->find('first', array(
			'conditions' => array('User.api_token' => $api_token),
			'recursive' => -1,
			'fields' => array(
				'User.id',
				'User.api_token'
			)
		));
		if (!empty($result)) {
			return $result['User'];
		} else {
			return false;
		}
	}

	/**
	 * Check Teacher Detail if favorite
	 * @param number $api_token - current user token
	 * @param number $teacher_id - teacher id to view
	 * @return boolean
	 */
	private function __checkFave($user_id = 0, $teacher_id = 0){
		$result = $this->UsersFavorite->useReplica()->find('first', array(
			'conditions' => array(
				'AND' => array(
					array("UsersFavorite.user_id" => $user_id),
					array("UsersFavorite.teacher_id" => $teacher_id)
				)
			)
		));

		if (!empty($result)) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Get Age array for those who evaluate the current teacher
	 * @param number $teacher_id - current Teacher id
	 * @return $ageArray - list / empty
	 */
	public function getForAge($teacher_id = 0) {

		$idList = array();
		$ageArray = array(0, 0, 0, 0, 0, 0, 0);

		$chosenAge = array(10, 20, 30, 40, 50, 60);

		$arryID = $this->getUserBirthDay($teacher_id);
		if (!empty($arryID)) {
			foreach ($arryID as $row) {
				$idList[] = $row['UsersClassEvaluation']['user_id'];
			}
		}
		$result = $this->User->useReplica()->find('all', array(
			'conditions' => array('User.id' => $idList),
			'recursive' => -1,
			'fields' => array('User.birthday', 'User.id'),
			'order' =>  array('User.birthday DESC'),
		));

		if (!empty($result)) {

			foreach ($result as $row) {
				if (!empty($row['User']['birthday'])) {
					$from = new DateTime($row['User']['birthday']);
					$to   = new DateTime('today');
					//$ageArray[] = intval(($from->diff($to)->y) / 10) * 10 ;
					$age = $from->diff($to)->y;
					foreach ($chosenAge  as $key => $value) {
						if ($age < $value) {
							$ageArray[$key] += 1;
							break;
						}
					}
				}

			}

		}

		return $ageArray;
	}

	/**
	 * Get all user id birthday in Teacher detail whose to evaluate
	 * the current teacher
	 * @param number $teacher_id - current teacher id
	 * @return array id|boolean
	 */
	public function getUserBirthDay($teacher_id = 0) {

		$result = $this->UsersClassEvaluation->useReplica()->find('all', array(
			'conditions' => array('UsersClassEvaluation.teacher_id' => $teacher_id),
			'fields' => array('DISTINCT UsersClassEvaluation.user_id')
		));

		if (!empty($result)) {
			return $result;
		} else {
			return false;
		}

	}
	/**
	 * Get Teacher Feature
	 * @param string $teacherId - teacher id
	 * @return Ambigous <NULL, string>
	 */
	private function __getTeacherFeature($teacherId = null, $teacher = null,$apiVersion = null,$nativeLanguage = null,$countryCodeIso = null) {

		$arrResult = array();
		$strData = null;
		$str = null;
		$teacherFeature = $this->TeacherFeature->useReplica()->find('first', array(
				'fields' => array(
						'TeacherFeature.new',
						'TeacherFeature.best_free_talk',
						'TeacherFeature.good_in_teaching_textbook',
						'TeacherFeature.suitable_for_intermediate_or_advance_students',
						'TeacherFeature.have_many_beginner_students',
						'TeacherFeature.suitable_for_children',
						'TeacherFeature.suitable_for_senior',
						'TeacherFeature.good_grammar_and_vocabulary',
						'TeacherFeature.pronunciation'
				),
				'conditions' => array('TeacherFeature.teacher_id' => $teacherId)
			)
		);

		if ($teacherFeature) {
			$feature = new TeacherFeatureTable($teacherFeature['TeacherFeature']);
			$setLocale = ($nativeLanguage != 'en') ? $countryCodeIso : 'eng' ;
			Configure::write('Config.language',$setLocale);
			$this->Session->write('Config.language',$setLocale);

			if ($feature->getNew()) {
				$str[] = __d('features','新しい');
			}
			if ($feature->getBestFreeTalk()) {
				$str[] = __d('features','フリートークが得意');
			}
			if ($feature->getGoodInTeachingTextbook()) {
				$str[] = __d('features','教材レッスンが得意');
			}
			if ($feature->getSuitableForIntermediateOrAdvanceStudents()) {
				$str[] = __d('features','中/上級者向き');
			}
			if ($feature->getHaveManyBeginnerStudents()) {
				$str[] = __d('features','初心者向き');
			}
			if ($feature->getSuitableForChildren()) {
				$str[] = __d('features','こども対応可');
			}
			if ($feature->getSuitableForSenior()) {
				$str[] = __d('features','シニア向き');
			}
			if ($feature->getGoodGrammarAndVocabulary()) {
				$str[] = __d('features','文法・ボキャブラリー');
			}
			if ($teacher->getJapaneseFlg()) {
				$str[] = __d('features','ヨーロッパ地域');
			}
			if ($feature->getPronunciation()) {
				$str[] = __d('features','発音');
			}
			$strData = $str;
		}

		return $strData;
	}

	private function checkUserToken($userIdToken) {
		return $this->User->useReplica()->find('first', array(
				'conditions' => array('api_token' => $userIdToken)
					)
				);
	}

	/**
	 * Get the list of image in teacher
	 * @param string $id - of the Teacher
	 * @return NULL|Ambigous <NULL, string>
	 */
	public function _getAlbum($id = null){

		$album = null;

		$result = $this->TeacherImage->useReplica()->find('all',array(
				'conditions' => array(
					'TeacherImage.teacher_id' => $id,
					'TeacherImage.is_profile' => 0,
					'OR' => array(
						'TeacherImage.approve_flg' => 1,
						'TeacherImage.approve_required' => 0
					)
				),
				'fields' => array(
						'TeacherImage.image',
						'FileStorage.url'
				),
				'joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'file_storage',
						'alias' => 'FileStorage',
						'conditions' => array(
							'TeacherImage.file_storage_id = FileStorage.id',
							'FileStorage.uploader_id' => $id,
							'FileStorage.uploader_type = 3'
						)
					)
				),
				'order' => array('TeacherImage.id DESC')
			)
		);

		if ($result) {
			foreach($result as $row){
				$teacher  = new TeacherImageTable($row['TeacherImage']);
				$album[] = $row['FileStorage']['url'];
				$teacher = null;
			}
		} else {
			return null;
		}

		return $album;
	}

	/**
	 * Get the list of available reservation
	 * @param unknown $user_id
	 * @param string $teacher_id
	 * @return NULL|Ambigous <NULL, string>
	 */
	public function _posibleReservationList($user_id, $teacher_id = null){
		$this->layout = "";
		$schedule = null;

		$times = array();
		for ($i = 0; $i <= 23; $i++) {
			$times[] = sprintf("%02d",$i) . ':00';
			$times[] = sprintf("%02d",$i) . ':30';
		}

		$days = array();
		$start = 0;
		$limitDays = 7;
		$reserved = @$this->LessonSchedule->findReserveListByTeacherIdAndLessondate(
				$user_id,
				$teacher_id,
				date('Ymd', strtotime("+" . $start . " days", time())),
				date('Ymd', strtotime("+" . ($limitDays+$start) . " days", time()))
		);

		if ($reserved == -1) {
			return $schedule;
		}

		for ($i=$start; $i<$limitDays+$start; $i++) {
			$days[] = array(
					'Y' => date('Y', strtotime("+" . $i . " days", time())),
					'm' => date('m', strtotime("+" . $i . " days", time())),
					'd' => date('d', strtotime("+" . $i . " days", time())),
			);
		}

		/** **/
		$hide_dates = $this->api->getHideDates($teacher_id);

		foreach ($days as $key => $day) {
			foreach ($times as $time) {
				$fDay = $day;
				$fTime = $time;
				list($hour,$minute) = explode(':', $time);

				$revisedTime = $fDay['Y'].$fDay['m'].$fDay['d'].strval($hour).strval($minute).'00';
				$fReservedTime = strtotime($fDay['Y'].'-'.$fDay['m'].'-'.$fDay['d'].' '.$fTime.':00');
				$fDateTime = strtotime(date('Y-m-d H:i:s'));

				$fDay['Y'].$fDay['m'].$fDay['d'].$hour.$minute.'00';
				$ls = $fDay['Y'].'-'.$fDay['m'].'-'.$fDay['d'];
				$moreThan10Min = false;
				if ($fDay['Y'].$fDay['m'].$fDay['d'].$hour.$minute > date('YmdHi', strtotime("+10 minutes", time()))) { // 過去
					$moreThan10Min = true;
				}

				if (array_key_exists($revisedTime, $reserved) &&
					!in_array($ls.' '. $fTime .':00', $hide_dates) &&
					!in_array($ls, $hide_dates))
				{
					if($fReservedTime > $fDateTime){
						if ($reserved[$revisedTime] == 9 && $moreThan10Min) {
							$schedule[] = $ls.' '. $fTime .':00';
						}
					}
				}
			}
		}
		return $schedule;
	}

	private function getJoinableShortLesson($data){
		$teacher = $data['Teacher'];
		$OnAir = $data['OnAir'];
		$resData = array(
			'teacherId' => $teacher->id,
			'userId' => $data['User']['id']
		);
		$reserveByMe = $this->reserveByMe($resData);

		if(empty($OnAir['status'])){
			$data = null;
		}elseif($OnAir['status'] == 3 && $OnAir['connect_flg'] == 1 && $data['TeacherStatus']->status == 1){
			$data = null;
		}elseif($OnAir['status'] == 2 && $OnAir['connect_flg'] == 1 && !$reserveByMe){
			$data = null;
		}elseif($OnAir['status'] == 2 && $OnAir['connect_flg'] == 1 && $this->preperationTime()){
			$data = null;
		}elseif($OnAir['status'] == 1 && $OnAir['connect_flg'] == 0){
			$data = null;
		}elseif($OnAir['status'] == 2 && $OnAir['connect_flg'] == 0){
			$data = null;
		}elseif($data['TeacherStatus']->status == 4 && $data['TeacherStatus']->remarks1 == 4){
			$data = null;
		}else{

			$lesson_time_stamp = floor(time() / (30 * 60)) * (30 * 60);
			$until_stamp = ceil(time() / (30 * 60)) * (30 * 60);
			$lesson_time = date('Y-m-d H:i:s',$lesson_time_stamp);
			$until = date('Y-m-d H:i:s',strtotime('-4 minutes',$until_stamp));

			$conditions = array(
				'LessonOnairsLog.teacher_id' => $teacher->id
			);
			if($OnAir['status'] == 2 && $OnAir['connect_flg'] == 1){
				$conditions['LessonOnairsLog.start_time >='] = $lesson_time;
			}

			$hasReservation = $this->api->getReservation(array(
				'LessonSchedule.teacher_id' => $teacher->id,
			));

			$hasNextReservation = $this->hasNextReservationLesson($teacher->id);

			//get next available schedule
			$nextSchedule = $this->LessonSchedule->getNextReservationTimeSlot();
			$checkNextSchedule = $this->ShiftWorkOn->checkDataExist( $teacher->id , $nextSchedule['start']);
			$checkScheduleCurrent = $this->ShiftWorkOn->checkDataExist( $teacher->id ,  date('Y-m-d H:i:s',$lesson_time_stamp));

			if (time() < strtotime($until) && $reserveByMe) {
				$data = array(
					'reason' => 'waiting_you',
					'until' => $until
				);
			}elseif(time() > $lesson_time_stamp && $hasNextReservation && !$hasReservation){
				$data = array(
					'reason' => 'next_reservation',
					'until' => $until
				);
			} else if (!$checkNextSchedule && $checkScheduleCurrent) {
				$data = array(
					'reason' => 'outside_working_hours',
					'until' =>  date('Y-m-d H:i:s',$until_stamp),
				);
			}else{
				$data = null;
			}
		}
		return $data;
	}

	private function hasNextReservationLesson($teacher_id){
		$lesson_time = ceil(time() / (30 * 60)) * (30 * 60);
		$lesson_time = date('Y-m-d H:i:s',$lesson_time);
		$conditions = array(
			'LessonSchedule.lesson_time' => $lesson_time,
			'LessonSchedule.teacher_id' => $teacher_id,
			'LessonSchedule.status' => 1
		);
		$schedule = $this->LessonSchedule->find('first',array(
			'fields' => array(
				'LessonSchedule.id'
			),
			'conditions' => $conditions,
			'recursive' => -1
			)
		);
		return isset($schedule['LessonSchedule']) ? true : false;
	}

	/**
	 * Check if if has reservation
	 * @param array $params
	 * @return boolean
	 */
	private function hasReservation($params) {
		$lesson_time = $this->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
		$lesson_time = date('Y-m-d H:i:s',$lesson_time);
		$conditions = array(
			'LessonSchedule.lesson_time' => $lesson_time,
			'LessonSchedule.teacher_id' => $params['teacher_id'],
			'LessonSchedule.status' => 1
		);
		$schedule = $this->LessonSchedule->find('first',array(
			'fields' => array('LessonSchedule.user_id'),
			'conditions' => $conditions,
			'recursive' => -1
			)
		);
		return isset($schedule['LessonSchedule']) ? true : false;
	}

	/**
	* reserveByMe, checks if the current time and teacher is reserved by current user
	* @param array $data
	* @param $data['teacherId'] -> teacher's id
	* @param $data['userId'] -> user's id
	* @return boolean
	*/
	private function reserveByMe($data){
		$lesson_time = $this->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
		$lesson_time = date('Y-m-d H:i:s',$lesson_time);
		$conditions = array(
			'LessonSchedule.lesson_time' => $lesson_time,
			'LessonSchedule.teacher_id' => $data['teacherId'],
			'LessonSchedule.status IN' => array(1,0),
			'LessonSchedule.user_id' => $data['userId']
		);
		$schedule = $this->LessonSchedule->find('first',array(
			'fields' => array('LessonSchedule.user_id'),
			'conditions' => $conditions,
			'recursive' => -1
			)
		);
		return isset($schedule['LessonSchedule']) ? true : false;
	}

	/**
	*	getTeacherReservationCoin, gets the teachers reservation coin
	*	@param $coinId -> int
	*	@return $coin -> int
	*/
	private function getTeacherReservationCoin($coinId){
		$coin = 0;
		$tRankCoin = $this->TeacherRankCoin->useReplica()->find('first', array(
			'conditions' => array(
				'TeacherRankCoin.id' => $coinId,
				'TeacherRankCoin.status' => 1
			)
		));

		$coin = isset($tRankCoin['TeacherRankCoin']['coins']) ? $tRankCoin['TeacherRankCoin']['coins'] : 0;
		return $coin;
	}

	/**
	* Remove specific tags with the content and can remove elements with attributes
	* parameters string $text and array $tags
	* return string $text
	*/
	function stripSelectedTagsContent($text, $tags = array()) {
		foreach($tags as $key => $val) {
			if(!is_array( $val )) {
				$text = preg_replace('/<' . $val . '[^>]*>([\s\S]*?)<\/' . $val . '[^>]*>/', '', $text);
			} else {
				$text = preg_replace('/<' . $val[0] . ' ' . $val[1] . '[^>]*>([\s\S]*?)<\/' . $val[0] . '[^>]*>/', '', $text);
			}
		}
		return $text;
	}

	private function getReserveAndCancelled($teacherId) {
		//params
		$param = array('teacher_id' => $teacherId);

		//get count finish reservation and cancelled reservations
		$thisMonthReservation = $this->LessonSchedule->getCurrentMonthReservationCount($param);
		$lastMonthReservation = $this->LessonSchedule->getLastMonthReservationCount($param);
		$thisMonthReservedCancellation = $this->LessonScheduleCancel->getCurrentReservationCancelledCount($param);
		$lastMonthReservedCancellation = $this->LessonScheduleCancel->getLastReservationCancelledCount($param);

		//get divisors
		$lastMonth = strtotime("first day of previous month");
		$thisMonthOnReservation = $this->LessonSchedule->getOnReservation($teacherId);
		$lastMonthOnReservation = $this->LessonSchedule->getOnReservation($teacherId, $lastMonth);

		$thisMonthDivisor = $thisMonthReservation + $thisMonthOnReservation + $this->LessonScheduleCancel->getCancellationRateDivisor($teacherId);
		$lastMonthDivisor = $lastMonthReservation + $lastMonthOnReservation + $this->LessonScheduleCancel->getCancellationRateDivisor($teacherId, $lastMonth);

		//compute cancellation rate
		$thisMonthCancellationPercentage = ($thisMonthDivisor == 0) ? 0 : (int)(($thisMonthReservedCancellation / $thisMonthDivisor) * 100);
		$lastMonthCancellationPercentage = ($lastMonthDivisor == 0) ? 0 : (int)(($lastMonthReservedCancellation / $lastMonthDivisor) * 100);

		return array(
			'this_month_reserved' => (int)$thisMonthReservation,
			'last_month_reserved' => (int)$lastMonthReservation,
			'this_month_cancellation_rate' => $thisMonthCancellationPercentage,
			'last_month_cancellation_rate' => $lastMonthCancellationPercentage
		);
	}

	/**
	* ISSUE: NC-3155
	* Returns current state status of teacher
	* parameters array of teachers information
	* @return integer
	*/
 	private function stateStatus( $params = array() ) {
		$result = 0;

		if( $params ) {

			$user = ( isset($params['user_data']) && count($params['user_data']) > 0 )? $params['user_data'] : false;
			$teacher = ( isset($params['teacher_data']) && count($params['teacher_data']) > 0 )? $params['teacher_data'] : false;
			$status = ( isset($params['status']) )? $params['status'] : false;
			$teacherStatus = ( $params['teacher_status'] != null )? $params['teacher_status'] : null;

			$lessonTime = $this->api->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
			$lessonTime = date('Y-m-d H:i:s',$lessonTime);

			$reservSql = $this->LessonSchedule->find( "first", array(
					"conditions" => array(
						"LessonSchedule.status" => 1,
						"LessonSchedule.user_id" => $user["id"],
						"LessonSchedule.teacher_id" => $teacher["id"],
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
					)
				)
			);

			// ------------------> Priority #1
			/*
				New registration / Sign in and proceed to the lesson
			*/

			if( $user == false ) {
				$result = Configure::read('user_detail_state_button.new_registration_or_sign_in_and_proceed_to_the_lesson');
				return $result;
			}

			// ------------------> Priority #2
			/*
				Re-subscribe and Try 7days free trial
			*/
			if( $user != false ) {
				// TODO : NC-3155 August 15, 2017
				// free user
				$userNotAdminFlg = ( $user['admin_flg'] != 1 ); //Overide payment if user is admin_flg = 1
				$freeUser = ( UserTable::userAdminStatus( $user['status'], $user['charge_flg'], $user['fail_flg'] , $user['hash16'], $user['id'] ) == 5 );
				$freeTrial = ( ($user['fail_flg'] == 0 && $user['double_check_flg'] != 2) );
				$noFreeTrial = ( ($user['fail_flg'] == 1 && $user['double_check_flg'] == 2) ||
								 ($user['fail_flg'] == 0 && $user['double_check_flg'] == 2) ||
								 ($user['fail_flg'] == 1 && $user['double_check_flg'] == 1)
								);

				if( $freeUser && $noFreeTrial && $userNotAdminFlg ) {
					// Re-subscribe: state = 12
					return Configure::read('user_detail_state_button.re_subscribe');
				}

				if( $freeUser && $freeTrial && $userNotAdminFlg ) {
					// Try 7days free trial: state = 13
					return Configure::read('user_detail_state_button.try_7days_free_trial');
				}

			}


			// ------------------> Priority #3
			/*
				Perform SMS authentication
			*/

			if( $user != false ) {

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
				if ( (($totalVerify) || ($smsThroughFlg)) == false ) {
					$result = Configure::read('user_detail_state_button.sms_authentication');
					return $result;
				}
			}

			// ------------------> Priority #4
			/*
				The reason why we set it to 4 is to enable you to deal with intervals of 24 hours
			*/

			if( $user != false && $teacher != false  ) {
				$secRemaining = $this->LessonOnairsLog->getLastLessonSecondsRemaining( $user['id'], $teacher['id'] );
				if( $secRemaining != 0 ) {
					$result = Configure::read('user_detail_state_button.last_lesson_is_being_processed');
					return $result;
				}
			}


			// ------------------> Priority #5
			/*
				------------- 5
				In order to re-enter to the reserved lesson and to continue the lesson currently being
				taught by switching instructors and terminals (PC ⇔ application etc.)
			*/

 			if( $user != false && $teacher != false && $status != false ) {

 				$checkOnair = $this->checkOnair( array(
						"user_id" => $user["id"],
						"teacher_id" => $teacher["id"],
						"counseling_flg" => $teacher["counseling_flg"],
						"status" => $status,
						"teacher_status" => $teacherStatus,
						'hasOtherReservation' => $params['hasOtherReservation'],
						'api_version' => $params['api_version']
					)
				);

				if( $checkOnair ) {
					return $checkOnair;
				}

			}


			// ------------------> Priority #6
			/*
				The reason for doing this ahead of isOnair is to prevent re-entry into the same lesson by changing the badge-incompatible teaching materials
			*/

			if( $user != false && $teacher != false && $status != false ) {
				if( $status == 1 ) { // blue button
					$checkSupport = $this->checkTextbookSupport( array( "user_id" => $user["id"], "teacher_id" => $teacher["id"], "reserve_data" => $reservSql ) );
					if( !$checkSupport ) {
						$result = Configure::read('user_detail_state_button.change_next_teaching_material');
						return $result;
					}
				}
			}



			// ------------------> Priority #7
			/*
				Proceed to counseling
			*/

 			if( $teacher != false && $status != false ) {

				if ( ($status == 1 || $status == 5) && $teacher["counseling_flg"] == 1 ) {
					return Configure::read('user_detail_state_button.proceed_to_counseling');
				}

			}

			// ------------------> Priority #8
			/*
				teacher status color
			*/

 			if( $status != false ) {
				if ( ($reservSql && $status == 1) || $status == 5 ) {
					// NC-3378 : teacher selected others during reservation lesson
					if ( $teacherStatus != null && $teacherStatus == '4' ) {
						return Configure::read('user_detail_state_button.busy');
					} else {
						$result = Configure::read('user_detail_state_button.go_to_reserved_lesson');
					}
				} elseif( $status == 1 ) {
					return Configure::read('user_detail_state_button.proceed_to_the_lesson_immediately');
				} elseif( $status == 2 ) {
					return Configure::read('user_detail_state_button.online_within_ten_minutes');
				} elseif( $status == 3 ) {
					return Configure::read('user_detail_state_button.busy');
				} elseif( $status == 4 ) {
					return Configure::read('user_detail_state_button.offline');
				}
			}

		}
		return $result;
	}
	/**
	* @Used in stateStatus()
	* Checking TeacherBadge for textbook material capability
	* parameters array of users and teachers information
	* @return boolean
	*/
	private function checkTextbookSupport( $params = array() ) {
		$seriesId = 0;
		$result = false;

		if( isset($params['user_id']) && isset($params['teacher_id']) ) {
			$teacherId = $params['teacher_id'];
			$userId = $params['user_id'];
			$reservSql = $params['reserve_data'];

			if( $reservSql ) {

				// --- Reservation
				$categoryId = $reservSql["TextbookCategory"]["id"];
				$categoryTypeId = $reservSql["TextbookCategory"]["type_id"];
				$textbookId = $reservSql["TextbookConnect"]["textbook_id"];

				if( $categoryTypeId == 1 ) { // course
					$seriesId = $this->Textbook->getTextbookSeriesId(array( 'textbook_id' => $textbookId ));
				} else { // series
					$seriesId = $categoryId;
				}

			} else {

				//  --- Lesson now
				$textbookParam = array(
					'select_method' => "first",
					"reservation_flag" => 0,
					'user_id' => $userId
				);
				$textbookData = $this->Textbook->getTextbooks($textbookParam);
				$lessonData = $textbookData["res_data"];
				$categoryId = $lessonData["TextbookCategory"]["id"];
				$categoryTypeId = $lessonData["TextbookCategory"]["type_id"];
				$textbookId = $lessonData["Textbook"]["id"];

				if( $categoryTypeId == 1 ) { // course
					$seriesId = $this->Textbook->getTextbookSeriesId(array( 'textbook_id' => $textbookId ));
				} else { // series
					$seriesId = $categoryId;
				}

			}

			// check teacher badge
			$checkBadge = $this->TeacherBadge->find("first", array(
					"conditions" => array(
						"TeacherBadge.teacher_id" => $teacherId,
						"TeacherBadge.textbook_category_id" => $seriesId
					),
					"fields" => array("TeacherBadge.id"),
					"recursive" => -1
				)
			);

			if( $checkBadge ) {
				$result = true;
			}

		}

		return $result;

	}
	/**
	* @Used in stateStatus()
	* Checking lessonOnair status
	* parameters array of users and teachers information
	* @return integer
	*/
  	private function checkOnair( $params = array() ) {
		$result = 0;

		if( isset($params['user_id']) && isset($params['teacher_id']) ) {
			$teacherId = $params['teacher_id'];
			$userId = $params['user_id'];
			$counselingFlg = $params['counseling_flg'];
			$status = $params['status'];
			$teacherStatus = $params['teacher_status'];


			$isOnair = 0; //0 is FALSE
			$onAir = $this->LessonOnair->find('first', array(
					'fields' => array(
						'LessonOnair.teacher_id',
						'LessonOnair.lesson_type',
						'TeacherStatus.status'
					),
					'conditions' => array('LessonOnair.user_id' => $userId),
					'joins' => array(
						array(
							'table' => 'teacher_status',
							'alias' => 'TeacherStatus',
							'type' => 'LEFT',
							'conditions' => 'LessonOnair.teacher_id = TeacherStatus.teacher_id'
						)
					)
			));

			if ($onAir) {
				if ($onAir['LessonOnair']['teacher_id'] == $teacherId) {

					// set to 2 when teacher state can lesson meaning standby, standbyforreserve and lesson, for button to be blue
					// else set to 0 to fallback to other status color
					if (in_array($onAir['TeacherStatus']['status'], array(1, 2, 3))) {
						$isOnair = 2;
					}

				} else {
					$isOnair = 1; //1 is TRUE
				}

				if ( $isOnair == 1 ) {
					// Priority #6
					//NC-4367 - [API] 予約レッスン時間内のダイアログ修正
					if($params['hasOtherReservation'] && $onAir['LessonOnair']['lesson_type'] == 2) {
						$result = Configure::read('user_detail_state_button.has_reservation_from_other_teacher');	
					} else {
						$result = Configure::read('user_detail_state_button.in_lesson_with_same_id');	
					}
				} elseif( $isOnair == 2 ) {
					$checkSupport = $this->checkTextbookSupport( array( "user_id" => $userId, "teacher_id" => $teacherId ) );

					// Priority #5
					if ( !$checkSupport ) {
						$result = Configure::read('user_detail_state_button.change_next_teaching_material');
					} else if ( $counselingFlg == 1 ) {
						$result = Configure::read('user_detail_state_button.proceed_to_counseling');
					} else if ( $status == 5 ) {
						// NC-3378 : teacher selected others during reservation lesson
						if ( $teacherStatus != null && $teacherStatus == '4' ) {
							return Configure::read('user_detail_state_button.busy');
						} else {
							$result = Configure::read('user_detail_state_button.go_to_reserved_lesson');
						}
					} else {
						$result = Configure::read('user_detail_state_button.proceed_to_the_lesson_immediately');
					}
				}

			}

		}

		return $result;
	}


}
