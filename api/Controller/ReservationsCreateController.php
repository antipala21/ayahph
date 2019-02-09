<?php
/*
  Reservation Create for API
  Author: John Mart Belamide
*/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class ReservationsCreateController extends AppController {
  public $uses = array(
		'Teacher',
		'LessonSchedule',
		'ShiftWorkOn',
		'TeacherRankCoin',
		'Textbook',
		'UsersPointHistory',
		'Payment'
  );

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Auth->allow(array('index'));
  }

  public function index() {

    @$request = json_decode($this->request->input(),true);

    $this->autoRender = false;
    $success = null;
    $errors = null;
    $action = null;

    $apiCommon = new ApiCommonController();
    if(!$request) {
      $response['error']['id'] = Configure::read('error.invalid_request');
      $response['error']['message'] = __('Invalid request');
    } else if (!isset($request['users_api_token']) || empty($request['users_api_token'])) {
      $response['error']['id'] = Configure::read('error.users_api_token_is_required');
      $response['error']['message'] = __('users_api_token is required');
    } else if (!is_string($request['users_api_token'])) {
      $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
      $response['error']['message'] = __('users_api_token must be string');
    } else if (!isset($request['teachers_id']) || empty($request['teachers_id'])) {
      $response['error']['id'] = Configure::read('error.teachers_id_is_required');
      $response['error']['message'] = __('teachers_id is required');
    } else if (!is_int($request['teachers_id'])) {
      $response['error']['id'] = Configure::read('error.teachers_id_must_be_integer');
      $response['error']['message'] = __('teachers_id must be integer');
    } else if (!isset($request['begin_at']) || empty($request['begin_at'])) {
      $response['error']['id'] = Configure::read('error.begin_at_is_required');
      $response['error']['message'] = __('begin_at is required');
  } else if(!isset($request['connect_id']) || empty($request['connect_id'])) {
	  $response['error']['id'] = Configure::read('error.connect_id_is_required');
	  $response['error']['message'] = __('connect_id is required');
  } else if (isset($request['user_status']) && ($request['user_status'] < 0 || $request['user_status'] > 9)) {
      $response['error']['id'] = Configure::read('error.user_status_invalid');
      $response['error']['message'] = __('user_status is invalid');    
  } else {
	  
	  $User = $apiCommon->validateToken($request['users_api_token']);

    if (isset($request['user_status'])) { 
        //check mismatch
        $params = array('status' => $request['user_status'], 'user' => $User);
        $mismatch = UserTable::checkStatusMatch($params);
        if ($mismatch['fail']) {
            return json_encode($mismatch['data']);
        }
    }

      $this->Teacher->openDBReplica();
      $Teacher = $this->Teacher->findById($request['teachers_id']);
      $this->Teacher->closeDBReplica();
      if (!$User) {
        $response['error']['id'] = Configure::read('error.invalid_api_token');
        $response['error']['message'] = __('Invalid users_api_token');
      } else  if (!$Teacher) {
        $response['error']['id'] = Configure::read('error.invalid_teachers_id');
        $response['error']['message'] = __('Invalid teachers_id');
      } else if ($apiCommon->checkBlocked($request['teachers_id'],$User['id'])) {
        $response['error']['id'] = Configure::read('error.missing_teacher');
        $response['error']['message'] = __($apiCommon->missing_teacher);
      } else if (
        isset($request['users_api_token']) && isset($request['teachers_id']) &&
        isset($request['begin_at']) && isset($request['connect_id'])
      ) {
          $this->LessonSchedule->validate['begin_at'] = array(
            'rule' => array('datetime'),
            'message' => __('Invalid date format')
          );
          $this->LessonSchedule->Set(array('begin_at' => $request['begin_at']));

          $this->userId   = $User['id'];
          $teacherId      = $request['teachers_id'];
          $dateAndTime    = $request['begin_at'];
          $response['error']['id'] = Configure::read('error.reservation_time_is_already_scheduled_on_other_teacher');
          $response['error']['message'] = __('Reservation time is already scheduled on other teacher.');

          if (!$this->LessonSchedule->validates() || !$apiCommon->validateDate($request['begin_at'])) {
            $response['error']['id'] = Configure::read('error.invalid_date_format');
            $response['error']['message'] = __('Invalid date format');
          } else {
            $request['begin_at'] = substr($request['begin_at'],0,17).'00';
            $minutes = substr($request['begin_at'],14,5);
            if (empty($teacherId) || empty($dateAndTime) || empty($this->userId)) {  
              $response['error']['id'] = Configure::read('error.api_token_teacher_and_datetime_schedule_must_not_empty');
              $response['error']['message'] = __('Api Token,Teacher and Datetime Schedule must not empty.');
            } else if($minutes != '30:00' && $minutes != '00:00') {
              $response['error']['id'] = Configure::read('error.invalid_time_format');
              $response['error']['message'] = __('Invalid time format');
            } else if (strtotime($request['begin_at']) < time()) {
              $response['error']['id'] = Configure::read('error.reservation_time_has_already_passed');
              $response['error']['message'] = __('Reservation time has already passed');
            } else if($this->isConflict($teacherId,$request['begin_at'])) {
              $response['error']['id'] = Configure::read('error.schedule_is_conflict');
              $response['error']['message'] = __('Schedule is conflict.');
            } else if(!$this->isScheduleOn($teacherId,$request['begin_at'])) {
              $response['error']['id'] = Configure::read('error.schedule_is_not_available_for_reservation');
              $response['error']['message'] = __('Schedule is not available for reservation');
            } else {
				
				#get teacher coin
				$teacherCoin = $this->getTeacherCoin($Teacher['Teacher']['rank_coin_id']);
				
				#set connectId, counselingFlg
				$connectId = $request['connect_id'];
				$counselingFlg = $Teacher['Teacher']['counseling_flg'];

				// set findTBResult flag if textbook is found
				$findTBResult = false;

				# check allowed textbook
				$getTextbookArr = array(
					'select_method' => 'first',
					'teacher_id' => $teacherId,
					'env_flag' => 'reservation',
					'user_id' => $this->userId,
					'auto_select' => 'off',
					'connect_id' => $connectId
				);
				$textbookData = $this->Textbook->getTextbooks($getTextbookArr);
				$textbookArr = $textbookData['res_data'];
				if ($textbookArr) {
					$textbook = $textbookArr;
					if ($textbook['TextbookCategory']['display_flag'] == 1) {
						$findTBResult = true;

						//additional conditions for callan
						if ($textbook['TextbookCategory']['textbook_category_type'] == 2) {
							// has finished entry level
							$hasFinishedCallanEntry = ClassRegistry::init('User')->hasFinishedCallanLevelCheck(array('user_id' => $this->userId)); # check if user has finished callan entry level lesson

							// check if has reserved entry level
							$hasReservedCallanLevelCheck = ClassRegistry::init('LessonSchedule')->hasReservedCallanLevelCheck(array('user_id' => $this->userId)); # check user has past reservation of callan
						
							if ($textbook['Textbook']['callan_level_check'] == 1 && ($hasFinishedCallanEntry || $hasReservedCallanLevelCheck)) {
								$response['error']['id'] = Configure::read('error.you_already_finished_or_reserved_the_lesson_callan_level_check');
								$response['error']['message'] = __('Can\'t reserve, You already finished or reserved the lesson Callan Level Check');
								return json_encode($response);
							} elseif ($textbook['Textbook']['callan_level_check'] == 0 && !$hasFinishedCallanEntry) {
								$response['error']['id'] = Configure::read('error.you_have_not_finished_the_lesson_callan_level_check');
								$response['error']['message'] = __('Can\'t reserve, You have not finished the lesson Callan Level Check');
								return json_encode($response);
							}
						}
					}
				}

				if ($findTBResult) {
                    $categoryLanguage = Configure::read('supported_textbook_language');
					$categoryType = $textbook['TextbookCategory']['textbook_category_type'];
                    $language = $User['native_language2'];
                    //use language translation
                    if (isset($categoryLanguage[$language])) {
                        $categoryName = isset($textbook['TextbookCategory'][$categoryLanguage[$language].'name']) ? $textbook['TextbookCategory'][$categoryLanguage[$language].'name'] : $textbook['name'];
                        $textbookName = isset($textbook['Textbook']['name_'.$language])? $textbook['Textbook']['name_'.$language] : $textbook['Textbook']['name'];
                        if($textbook['TextbookCategory']['type_id'] == 1) {
                            $chapterName = $textbook['TextbookSubcategory']['badge'].'-'. $textbook['Textbook']['index_position'].' : '.$textbookName;
                        } else {
                            $chapterName = $textbook['Textbook']['index_position'].' : '.$textbookName;

                        }
                    } else {
      					//NC-3344
      					$categoryName = $textbook['TextbookCategory'][$categoryLanguage[$language].'name']; 
      					if($textbook['TextbookCategory']['type_id'] == 1) {
      						$chapterName = $textbook['TextbookSubcategory']['badge'].'-'. $textbook['Textbook']['index_position'].' : '.$textbook['Textbook']['name'];
      					} else {
      						$chapterName = $textbook['Textbook']['index_position'].' : '.$textbook['Textbook']['name'];
      					}
                    }
				} else {
					$response['error']['id'] = Configure::read('error.invalid_connect_id');
					$response['error']['message'] = 'Invalid connect_id';
					return json_encode($response);
				}

				$reserveCoinParams = array(
					'teacher_id' => $teacherId,
					'teacher_coin' => $teacherCoin,
					'connect_id' => $connectId
				);

				// check if callan_half_price is not applicable
				$userLatestPoints = $this->UsersPointHistory->find('first', array(
					'conditions' => array('UsersPointHistory.user_id' => $User['id']),
					'fields' => array('UsersPointHistory.point', 'UsersPointHistory.point_old', 'UsersPointHistory.kbn'),
					'order' => 'UsersPointHistory.created DESC',
					'recursive' => -1
				));

				$userPayment = $this->Payment->find('first', array(
					'conditions' => array('Payment.user_id' => $User['id']),
					'fields' => array('Payment.form_type'),
					'order' => 'Payment.created DESC',
					'recursive' => -1
				));
				
				if (
					$userPayment['Payment']['form_type'] == Configure::read('payment_credit_coin_purchase') &&
					in_array($User['card_company'], array(Configure::read('card_company.apple'), Configure::read('card_company.google'))) &&
					($userLatestPoints['UsersPointHistory']['point'] - $userLatestPoints['UsersPointHistory']['point_old']) <= $teacherCoin &&
          $userLatestPoints['UsersPointHistory']['kbn'] == 7 // coin purchase
				) {
					$reserveCoinParams['not_callan_half_price'] = true;
				}

				# compute coin for reservation
				$reserveCoin = $this->LessonSchedule->getReserverCoin($reserveCoinParams);

				# save reserve data
				$reserveData = array(
          'is_application' => true,
					'user_id' => $this->userId,
					'teacher_id' => $teacherId,
					'reserve_time' => $dateAndTime,
					'connect_id' => $connectId,
					'counseling_flg' => $counselingFlg,
					'reserve_coin' => $reserveCoin['totalReserveCoin'],
                    'callan_halfprice_flg' => $reserveCoin['callanHalfPriceFlg'],
                    'rank_coin_id' => $reserveCoin['rankCoinId'],
					'category_type' => $categoryType, //used for UsersPoint
					'category_name' => $categoryName,
					'chapter_name' => $chapterName
				);
                
            //NC-3375 - reservation rule
            $res = $this->limitwarning(array(
                'userId' => $this->userId, 
                'teacherId' => $teacherId,
                'lessonDate' => date('Y-m-d', strtotime($dateAndTime))
            ));
            if ($res) {	
                if ($res == 2) {
                    $response['error']['id'] = Configure::read('error.the_all_reservation_limit_has_been_reached');
                    $response['error']['message'] =__('All of your reservation limit has been reached.');
                } elseif ($res == 3) {
                    $response['error']['id'] = Configure::read('error.the_each_teacher_reservation_limit_has_been_reached');
                    $response['error']['message'] =__('The reservation limit for this teacher has been reached.');
                }
                return json_encode($response);
            } else {
                $res = @$this->LessonSchedule->addReserve($reserveData);
            }
              switch ($res) {
                // 成功
                case '1':
                  //NC-4522 add alert if teacher has reservation
                  $teacherReservations = array();
                  $memcached = new myMemcached();
                  if($memcached->get('teacherHasReservation_' . $teacherId)) {
                      $teacherReservations = $memcached->get('teacherHasReservation_' . $teacherId); 
                  }
                  array_push($teacherReservations, true);
                  $memcached->set(array(
                      'key' => 'teacherHasReservation_'.$teacherId,
                      'value' => $teacherReservations,
                      'expire' => 604800
                  ));
                  $response = array('created' => true);
                  break;
                case '-1':
                 //'予約済み
                //   $response['error']['message'] = __('Data Creation Failure'); pc side message
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
                // 10分以内のレッスンの場合
                case '-2':
                  $response['error']['id'] = Configure::read('error.reservation_is_possible_until_ten_minutes_before');
                  $response['error']['message'] = __("It could not be reserved. Reservation is possible until 10 minutes before.");
                  break;
                // ポイント不足
                case '-3':
                  $response['error']['id'] = Configure::read('error.you_do_not_have_enough_coins_for_reservation');
                  $response['error']['message'] = __("You do not have enough coins for reservation.");
                  break;
                default:
                  $response['error']['id'] = Configure::read('error.the_begin_time_you_specified_is_already_scheduled');
                  $response['error']['message'] =__('The begin time you specified is already scheduled.');
                  break;
              }
            }
          }   
      } else {
        $response['error']['id'] = Configure::read('error.invalid_request');
        $response['error']['message'] = __('Invalid request');
      }
    }
    return json_encode($response);
  }

  /**
  * Check if this schedule is "On" by this teacher
  * @param int $teacher_id, int $schedule
  * @return boolean
  */
  public function isScheduleOn($teacherId,$schedule) {

    $data = $this->ShiftWorkOn->useReplica()->find('first',array(
      'conditions' => array(
          array('teacher_id'  => $teacherId),
          array('lesson_time' => $schedule)
          )
        )
      );
    if($data) {
      return true;
    } else {
      return false;
    }
  }

  /**
  * Check is schedule can have a conflict
  * @param int $teacherId,string $schedule
  * @return boolean
  */
  public function isConflict($teacherId,$schedule) {
    
    $dateTimeNow = date('Y-m-d H:i:s');
    $dateTimeNow = date('Y-m-d H:i:s',strtotime($dateTimeNow)+3600);
    $minutes = (substr($dateTimeNow,14,2) - 30 >= 0) ? '30' : '00';
    $currentDateTimeFinished = substr($dateTimeNow,0,14).$minutes.':00';
    $currentDateTimeFinished = date('Y-m-d H:i:s',strtotime($currentDateTimeFinished) + 1800);
    if($currentDateTimeFinished == $schedule) {
      $date = substr($schedule, 0,10);
      $time = substr($schedule, 11,8);
      $time = strtotime($time) - 1800;
      $startTime = $date.' '.date('H:i:s',$time);
      $conflictTime = $time + 360;
      $conflictDateTime = $date.' '.date('H:i:s',$conflictTime);
      $conditions = array(
        'AND' => array(
          array(
            array('teacher_id'          => $teacherId),
            array('lesson_time'         => $startTime),
            array('chat_start_time >='  => $conflictDateTime),
            'OR' => array(
              array('chat_end_time >' => date('Y-m-d H:i:s',strtotime($schedule)-300)),
              array('chat_end_time'   => NULL)
            )
          )
        )
      );
      $reservations = $this->LessonSchedule->find('first',array(
        'conditions' => $conditions
        )
      );
      if($reservations) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  /**
  * getTeacherCoin, gets the teacher's reservation coin based from the TeacherRankCoin table
  * @param int $rankCoinId
  * @return int $rankCoin -> the teacher's reservation coin or the teacher's rank coin
  */
  private function getTeacherCoin($rankCoinId){
	  $rankCoin = $this->TeacherRankCoin->useReplica()->find('first', array(
		  'conditions' => array(
			  'TeacherRankCoin.id' => $rankCoinId,
			  'TeacherRankCoin.status' => 1
		  )
	  ));
	  return ($rankCoin) ? $rankCoin['TeacherRankCoin']['coins'] : 0;
  }

    /**
    * validate reservation first
    * @param userId , teacherId
    * @return int 
    */
    private function limitwarning($params = array()) {
        if (empty($params['userId']) || empty($params['teacherId'])) {
            return 0;
        }
        $userId = $params['userId'];
        $teacherId = $params['teacherId'];
        //count reservation
        $totalReservation = $this->LessonSchedule->countUserTotalReservation(array('userId' => $userId));
        if ($totalReservation >= 20) {
            return 2;
        } 
        //count reservation for a teacher
        $countTeacherReserved = $this->LessonSchedule->countUserReservationForTeacher(array(
            'teacherId' => $teacherId, 
            'userId' => $userId,
            'lessonDate' => $params['lessonDate']
        ));
        if ($countTeacherReserved >= 4) {
            return 3;
        }
        return 0;
    }
}