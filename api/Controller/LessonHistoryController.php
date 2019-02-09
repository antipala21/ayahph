<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myMemcached', 'Lib');
class LessonHistoryController extends AppController {

  public $uses = array(
      'User',
      'LessonOnairsLog',
      'Teacher',
      'Timezone'
  );
  //lesson history default per page
  public $limit = 10;

  //language parameter for textbook
  public $urlLang = NULL;

  public function beforeFilter() {
      parent::beforeFilter();
      $this->autoRender = false;
      $this->Auth->allow('index', 'historyCount');
  }

  /**
  * Lesson History
  * @access url : https://nativecamp.net/api/lesson/history
  * @param : users_api_token, year, month, pagination
  * @return json : history,datetime, teacher_id, teacher_name, teacher_image, textbook, textbook_url, chathash, message_flg, chatlog_flg, review
  */
  public function index() {
    $response = array();
    $validationCheck = $this->validationCheck();
    $request = $validationCheck['request'];
    if ($validationCheck['validation']) {
      $page = !isset($request['pagination']) ? 1 : $request['pagination'];
      $api = new ApiCommonController();
      $userData = $api->validateToken($request['users_api_token']);
      //invalid user
      if (!$userData) {
        $response['error']['id'] = Configure::read('error.invalid_api_token');
        $response['error']['message'] = 'Invalid users_api_token';
      } elseif(!isset($request['start_date']) || empty($request['start_date']) || !isset($request['end_date']) || empty($request['end_date'])) {
        $response['error']['id'] = Configure::read('error.start_date_and_end_date_is_required');
        $response['error']['message'] = __('start_date and end_date is required');
      } elseif(!$api->validateDate($request['start_date']) || !$api->validateDate($request['end_date'])) {
        $response['error']['id'] = Configure::read('error.invalid_date_format');
        $response['error']['message'] = __('invalid date format');
      } else {

        $offset = ($page - 1) * $this->limit;
        $user = new UserTable($userData);

        $conditions = array(
          'LessonOnairsLog.user_id' => $user->id,
          'LessonOnairsLog.connect_id IS NOT NULL',
          'LessonOnairsLog.start_time >=' => $request['start_date'],
          'LessonOnairsLog.end_time <=' => $request['end_date']
        );
        

        $histories = $this->LessonOnairsLog->find('all', array(
          'fields' => array(
            'Teacher.id',
            'Teacher.name',
            'Teacher.jp_name',
            'Teacher.image_url',
            'Teacher.counseling_flg',
            'Textbook.name',
            'Textbook.name_ko',
            'Textbook.name_th',
            'Textbook.name_eng',
            'Textbook.html_directory',
            'Textbook.chapter_id',
            'Textbook.alt_html_directory',
            'Textbook.alt_chapter_id',
            'Textbook.licenser',
            'LessonOnairsLog.start_time',
            'LessonOnairsLog.chat_hash',
            'LessonOnairsLog.lesson_memo_disp_flg',
            'LessonOnairsLog.lesson_memo',
            'LessonOnairsLog.lesson_schedule_id',
            'LessonOnairsLog.user_id',
            'LessonOnairsLog.lesson_system_trouble',
            'LessonOnairsLog.connect_id',
            'UsersClassEvaluation.rate',
            'UsersClassEvaluation.sparta',
            'UsersClassEvaluation.polite',
            'UsersClassEvaluation.humor',
            'UsersClassEvaluation.friendly',
            'UsersClassEvaluation.smile',
            'UsersClassEvaluation.user_comment',
            'UserCoupon.id',
            'TextbookSubcategory.name',
            'TextbookSubcategory.korean_name',
            'TextbookSubcategory.thai_name',
            'TextbookSubcategory.english_name',
            'TextbookCategory.name',
            'TextbookCategory.korean_name',
            'TextbookCategory.thai_name',
            'TextbookCategory.english_name',
            'TextbookCategory.type_id',
            'TextbookCategory.image_big_url',
            'LessonTrackLog.id',
            'LessonTrackLog.lesson_number'
          ),
          'joins' => array(
            array(
              'table' => 'teachers',
              'alias' => 'Teacher',
              'type' => 'LEFT',
              'conditions' => 'LessonOnairsLog.teacher_id = Teacher.id'
            ),
            array(
              'table' => 'textbook_connects',
              'alias' => 'TextbookConnect',
              'type' => 'LEFT',
              'conditions' => 'LessonOnairsLog.connect_id = TextbookConnect.id'
            ),
            array(
              'table' => 'textbooks',
              'alias' => 'Textbook',
              'type' => 'LEFT',
              'conditions' => 'TextbookConnect.textbook_id = Textbook.id'
            ),
            array(
              'table' => 'users_class_evaluations',
              'alias' => 'UsersClassEvaluation',
              'type' => 'LEFT',
              'conditions' => 'LessonOnairsLog.chat_hash = UsersClassEvaluation.chat_hash'
            ),
            array(
              'table' => 'user_coupons',
              'alias' => 'UserCoupon',
              'type' => 'LEFT',
              'conditions' => 'LessonOnairsLog.lesson_schedule_id = UserCoupon.reservation_id'
            ),
      array(
        'table' => 'textbook_categories',
        'alias' => 'TextbookCategory',
        'type' => 'LEFT',
        'conditions' => 'TextbookConnect.category_id = TextbookCategory.id'
      ),
      array(
        'table' => 'textbook_subcategories',
        'alias' => 'TextbookSubcategory',
        'type' => 'LEFT',
        'conditions' => 'TextbookConnect.subcategory_id = TextbookSubcategory.id'
      ),
          array(
            'type' => 'LEFT',
            'table' => 'lesson_track_logs',
            'alias' => 'LessonTrackLog',
            'conditions' => 'LessonOnairsLog.chat_hash = LessonTrackLog.chat_hash'
          )
          ),
          'conditions' => $conditions,
          'limit' => ($this->limit + 1),
          'offset' => $offset,
          'order' => 'LessonOnairsLog.start_time DESC',
          'recursive' => -1
        ));
        if ($histories) {
          $hasNext = count($histories) == ($this->limit + 1) ? true : false;
          //remove the last data
          if ($hasNext) {
            array_pop($histories);
          }
          $result['history'] = array();

          //default counselor data
          $counselorDetail = $this->Teacher->getDefaultCounselorDetail();
          // set another value for textbook type
          $textbookType = array(
              1 => 'course',
              2 => 'category'
          );

          //blocked teacher_id
          $blockList = BlockListTable::getBlocks($user->id);

          //format data
          foreach ($histories as $key => $history) {
            $lessonDetail = new LessonOnairsLogTable($history['LessonOnairsLog']);
            $teacherDetail = new TeacherTable($history['Teacher']);
            $reviewDetail = new UsersClassEvaluationTable($history['UsersClassEvaluation']);
            $couponDetail = new UserCouponTable($history['UserCoupon']);
            $lessonTrackLog = new LessonTrackLogTable($history['LessonTrackLog']);

            if ($teacherDetail->counseling_flg) {
                $teacherDetail = new TeacherTable($counselorDetail['Teacher']);
            }

            // same format with api lesson message todays lesson
            $lesson = json_decode($lessonDetail->lesson_memo,true);
            $todaysLesson = ($lesson) ? json_encode($lesson) : '';
            if ( isset($lesson['message_1']) ) {
              $todaysLesson = $this->getFirstIndexValue($lesson['message_1']);
            }
            //change data type in v17 above
    				$checkCountryCode = ($user->native_language2 == 'ja' || $user->native_language2 == '') ? 'ja' : $user->native_language2;
    				$getUserSettingLanguage = ( $checkCountryCode == 'ja') ? $teacherDetail->jp_name : '' ;
    				switch ($user->native_language2) {
    					case 'ja':
    						$courseTitle = $history['TextbookCategory']['name'];
    						$subcategory = $history['TextbookSubcategory']['name'];
    						$chapter = $history['Textbook']['name'];
    					break;
    					case 'ko':
    						$courseTitle = $history['TextbookCategory']['korean_name'];
    						$subcategory = $history['TextbookSubcategory']['korean_name'];
    						$chapter = $history['Textbook']['name_ko'];
    					break;
    					case 'th':
    						$courseTitle = $history['TextbookCategory']['thai_name'];
    						$subcategory = $history['TextbookSubcategory']['thai_name'];
    						$chapter = $history['Textbook']['name_th'];
    					break;
    					default:
    						$courseTitle = $history['TextbookCategory']['english_name'];
    						$subcategory = $history['TextbookSubcategory']['english_name'];
    						$chapter = $history['Textbook']['name_eng'];
    					break;
    				}
    				$textBookCourseTitle = $courseTitle;

    				# get textbook order number
    				$memcached = new myMemcached();
    				$cachedKey = Configure::read('textbook_names_cache_key');
    				$textbookNameCachedArr = $memcached->get($cachedKey);
    				$orderDataArr = ($textbookNameCachedArr != null && is_array($textbookNameCachedArr)) ? $textbookNameCachedArr : null;
    				$connectId = $history['LessonOnairsLog']['connect_id'];
    				$orderNum = isset($orderDataArr[$connectId]['order']) ? $orderDataArr[$connectId]['order'] : null ;

    				# format textbook name
    				$chapterName = isset($chapter) ? $orderNum . $chapter : null;
    				$dash = (isset($subcategory) && isset($chapterName)) ? ' - ' : '';
    				$textBook = $subcategory . $dash . $chapterName;

                    //add language value
                    $this->urlLang = !empty($user->native_language2) ? $user->native_language2 : NULL;

    				$messageFlg = (int)$lessonDetail->lesson_memo_disp_flg;
    				$chatlogFlg = ChatHistoryTable::lessonHasChat($lessonDetail->chat_hash);
    				$chatlogFlg = (int)$chatlogFlg;
      			
      			$message = $todaysLesson ? $todaysLesson : '';
      			$result['history'][$key] = array(
      				'datetime' => date('Y-m-d H:i:s', strtotime($lessonDetail->start_time)),
      				'teacher_id' => (int)$teacherDetail->id,
      				'teacher_name' => $getUserSettingLanguage,
      				'teacher_name_ja' => $teacherDetail->jp_name,
      				'teacher_name_eng' => $teacherDetail->name,
      				'lesson_type' => is_null($lessonDetail->lesson_schedule_id) || ($lessonDetail->lesson_schedule_id == 0) ? 1 : (is_null($couponDetail->id) ? 2 : 3),
      				'teacher_image' => $teacherDetail->getImageUrl(),
      				'textbook_course_title' => $textBookCourseTitle,
      				'textbook' => $textBook,
      				'textbook_url' => $this->parseTBURL(array(
      				'class' => $history['Textbook']['html_directory'],
      				'chapter' => $history['Textbook']['chapter_id'],
      				'main_html_directory' => $history['Textbook']['html_directory'],
      				'main_chapter' => $history['Textbook']['chapter_id'],
      				'alt_html_directory' => $history['Textbook']['alt_html_directory'],
      				'alt_chapter' => $history['Textbook']['alt_chapter_id'],
      				'env_flag' => 'lesson_now',
      				'textbook_category_type' => NULL,
      				'user_id' => $history['LessonOnairsLog']['user_id'],
      				'licenser' => $history['Textbook']['licenser']
      			)),
      				'textbook_image' => $history['TextbookCategory']['image_big_url'],
      				'chathash' => $lessonDetail->chat_hash,
      				'message_flg' => $messageFlg,
      				'message' => $message,
      				'chatlog_flg' => $chatlogFlg,
      				'lesson_number' => isset($lessonTrackLog->lesson_number) ? $lessonTrackLog->lesson_number : null,
      				'review' => (int) $reviewDetail->getReviewRating(),
      				'review_comment' => !empty($reviewDetail->user_comment) ? $reviewDetail->user_comment : '',
                      'blocked_by_teacher_flg' => (isset($blockList[$teacherDetail->id]) ? 1 : 0)
      			);
            //NC-4451 add review button
            $start_time = $lessonDetail->start_time;
            $dateNow = date('Y-m-d H:i:s');
            $time_difference = strtotime($dateNow) - strtotime($start_time);

            if ($lessonDetail->lesson_system_trouble) {
                $result['history'][$key]['review_button'] = 4;
            } else if ($result['history'][$key]['review'] == 0 && $time_difference > 86400) {
                $result['history'][$key]['review_button'] = 3;
            } else if ($result['history'][$key]['review']) {
                $result['history'][$key]['review_button'] = 2;
            } else if ($result['history'][$key]['review'] == 0) {
                $result['history'][$key]['review_button'] = 1;
            }
  					unset($result['history'][$key]['teacher_name_ja']);
  					$result['history'][$key]['counseling_flg'] = intval($teacherDetail->counseling_flg);
          }
          $response = $result;
          $response['has_next'] = $hasNext;
        } else {
          $response = array('result' => false);
        }
      }
    } else {
      $response = $validationCheck['response'];
    }
    return json_encode($response);
  }

  /**
  * validation for request
  * @return boolean
  */
  private function validationCheck() {
    $response = array();
    $request = json_decode($this->request->input(), true);
    if (!$request) {
      $response['error']['id'] = Configure::read('error.invalid_request');
      $response['error']['message'] = __('Invalid request.');
    } else {
      if (empty($request['users_api_token']) || !isset($request['users_api_token'])) {
        $response['error']['id'] = Configure::read('error.users_api_token_is_required');
        $response['error']['message'] = __('The users_api_token is required.');
      } elseif (isset($request['pagination']) && !is_integer($request['pagination'])) {
        $response['error']['id'] = Configure::read('error.pagination_must_be_integer');
        $response['error']['message'] = __('Pagination must be integer ');
      } elseif (isset($request['api_version']) && is_string($request['api_version'])) {
        $response['error']['id'] = Configure::read('error.api_version_must_be_integer');
        $response['error']['message'] = __('api version must not be string');
      }
    }
    return array(
      'validation' => empty($response) ? true : false,
      'response' => $response,
      'request' => $request
    );
  }

  /**
  * Lesson Hitory count
  *
  */
  public function historyCount() {
    $request = json_decode($this->request->input(), true);
    $response = array();
    if (!$request) {
      $response['error']['id'] = Configure::read('error.invalid_request');
      $response['error']['message'] = __('Invalid request.');
    } elseif (empty($request['users_api_token']) || !isset($request['users_api_token'])) {
      $response['error']['id'] = Configure::read('error.users_api_token_is_required');
      $response['error']['message'] = __('The users_api_token is required.');
    }

    if (!$response) {
      $api = new ApiCommonController();
      $userData = $api->validateToken($request['users_api_token']);
      if (!$userData) {
        $response['error']['id'] = Configure::read('error.invalid_api_token');
        $response['error']['message'] = 'Invalid users_api_token';
      } else {
        $user = new UserTable($userData);
        
        // check for timezone ------
        if ( empty($user->timezone_id) || $user->timezone_id == NULL ) {

          // Default timezone JP
          $lessonLogs = $this->LessonOnairsLog->find('all', array(
            'fields' => "DATE_FORMAT(LessonOnairsLog.start_time, '%Y/%m') as yearMonth, COUNT(*) as historyCount, sum(case when TIME_TO_SEC(TIMEDIFF(LessonOnairsLog.end_time , LessonOnairsLog.start_time)) > 0 then TIME_TO_SEC(TIMEDIFF(LessonOnairsLog.end_time , LessonOnairsLog.start_time)) else 0 end) as total_lesson_time",
            'conditions' => array(
              'LessonOnairsLog.user_id' => $user->id,
              'LessonOnairsLog.connect_id IS NOT NULL',
              'LessonOnairsLog.start_time IS NOT NULL',
              'LessonOnairsLog.end_time IS NOT NULL'
            ),
            'group' => 1,
            'order' => 'yearMonth DESC'
          ));
          if ($lessonLogs) {
            foreach ($lessonLogs as $lessonLog) {
              $result[] = array(
                'time' => $lessonLog[0]['yearMonth'],
                'count' => $lessonLog[0]['historyCount'],
                'total_lesson_time' => ((int)$lessonLog[0]['total_lesson_time'] * 1000)
              );
            }
            $response['lessonCount'] = $result;
          } else {
            $response = array('result' => false);
          }

        } else {

          // Timezone is set according to users timezone
          $tadjustParam = array(
            "timezone_id" => $user->timezone_id,
            "dst_flag" => isset($user->timezone_dst_flg) ? $user->timezone_dst_flg : 0 ,
          );

          $timezoneAdjust = self::timezoneAdjustment($tadjustParam);
          $minuteAdjust = isset($timezoneAdjust["minute"]) ? $timezoneAdjust["minute"] : 0 ;

          $arrResLogs = array();
          $lessonLogs = $this->LessonOnairsLog->find('all', array(
            'fields' => array(
              "LessonOnairsLog.id",
              "LessonOnairsLog.start_time",
              "LessonOnairsLog.end_time",
            ),
            'conditions' => array(
              'LessonOnairsLog.user_id' => $user->id,
              'LessonOnairsLog.connect_id IS NOT NULL',
              'LessonOnairsLog.start_time IS NOT NULL',
              'LessonOnairsLog.end_time IS NOT NULL'
            ),
            'order' => "LessonOnairsLog.start_time DESC",
            'recursive' => -1
          ));

          if ($lessonLogs) {
            foreach ($lessonLogs as $key => $value) {
              $logId = $value["LessonOnairsLog"]["id"];
              $startTime = $value["LessonOnairsLog"]["start_time"];
              $endTime = $value["LessonOnairsLog"]["end_time"];
              $strMinuteAdjustment = strtotime($startTime . " {$minuteAdjust} minute");
              $ym = date('Y/m', $strMinuteAdjustment);
              $startTimeAdjusted = date('Y-m-d H:i:s', $strMinuteAdjustment);
              $endTimeAdjusted = date('Y-m-d H:i:s', strtotime($endTime . " {$minuteAdjust} minute"));

              // calcutate time diff
              $date1=date_create($startTimeAdjusted);
              $date2=date_create($endTimeAdjusted);
              $timeCount = self::dateIntervalToSec($date1,$date2);

              // Per month data arrangement
              if ( !isset($arrResLogs[$ym]) ) {
                $arrResLogs[$ym]["time"] = $ym;
                $arrResLogs[$ym]["count"] = 1;
                $arrResLogs[$ym]["total_lesson_time"] = $timeCount;
              } else {
                $arrResLogs[$ym]["count"] = $arrResLogs[$ym]["count"] + 1;
                $arrResLogs[$ym]["total_lesson_time"] = $arrResLogs[$ym]["total_lesson_time"] + $timeCount;
              }
            }

            // loop to millisec
            foreach ($arrResLogs as $keyLog => $valueLog) {
              $arrResLogs[$keyLog]["total_lesson_time"] = $arrResLogs[$keyLog]["total_lesson_time"] * 1000;
            }

            $arrResLogs = array_values($arrResLogs);
            $response['lessonCount'] = $arrResLogs;

          } else {
            $response = array('result' => false);
          }
        }
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
   * parse url for textbook
   */
  private function parseTBURL($tbData = array()){
    // set data
    $mainURL = FULL_BASE_URL . '/user/sp/textbook/view?';
    $mainURL .= 'class=' . $tbData['class'];
    $mainURL .= '&chapter=' . $tbData['chapter'];
    $mainURL .= '&main_html_directory=' . $tbData['main_html_directory'];
    $mainURL .= '&main_chapter=' . $tbData['main_chapter'];
    $mainURL .= '&alt_html_directory=' . $tbData['alt_html_directory'];
    $mainURL .= '&alt_chapter=' . $tbData['alt_chapter'];
    $mainURL .= '&env_flag=' . $tbData['env_flag'];
    $mainURL .= '&textbook_category_type=' . $tbData['textbook_category_type'];
    $mainURL .= '&user_id=' . $tbData['user_id'];
    $mainURL .= '&licenser=' . $tbData['licenser'];
    //append textbook language
    if ($this->urlLang) {
        $mainURL .= '&la=' . $this->urlLang;
    }
    // return main url
    return $mainURL;
  }

  private function timezoneAdjustment($params=array()){
    $result = array();
    if ( isset($params["timezone_id"]) ) {

        $getTimezoneInfo = $this->Timezone->find("first",array(
            "conditions" => array(
              "Timezone.id" => $params["timezone_id"],
              "Timezone.status" => 1
            ),
            "fields" => array( "Timezone.jp_time_diff" )
          )
        );
        $jpTimeDiff = $getTimezoneInfo["Timezone"]["jp_time_diff"];

        // check daily saving time +60 mins
        if ( isset($params["dst_flag"]) && $params["dst_flag"] == 1 ) {
          $jpTimeDiff = $jpTimeDiff + 60;
        }

        $result["minute"] = $jpTimeDiff;
    }

    return $result;
  }
  private function dateIntervalToSec($start,$end){ // as datetime object returns difference in seconds
    $diff = $start->diff($end);
    $diff_sec = $diff->format('%r').( // prepend the sign - if negative, change it to R if you want the +, too
      ($diff->s)+ // seconds (no errors)
      (60*($diff->i))+ // minutes (no errors)
      (60*60*($diff->h))+ // hours (no errors)
      (24*60*60*($diff->d))+ // days (no errors)
      (30*24*60*60*($diff->m))+ // months (???)
      (365*24*60*60*($diff->y)) // years (???)
    );
    return $diff_sec;
  }
}
