<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class TeachersRankingController extends AppController {

	public $uses = array('User', 'Teacher', 'TeacherRanking', 'LessonOnair', 'TeacherStatus');
	public $limit = 50;
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
    $result = array();
		$this->autoRender = false;
		@$data = json_decode($this->request->input(),true);
		$inputs = array();
		if ($data) {
			foreach($data as $key => $value) {
				$inputs[$key] = $value;
			}
		}
		$api = new ApiCommonController();
		if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
			$result['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$result['error']['message'] =  __('users_api_token must be string');
		} else if (isset($data['users_api_token']) && trim($data['users_api_token']) == "") {
			$result['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$result['error']['message'] =  __('users_api_token can not be empty');
    //sort
    } else if (!isset($data['sort'])) {
      $result['error']['id'] = Configure::read('error.sort_is_required');
      $result['error']['message'] = __('sort is required');
		} else if (!is_integer($data['sort'])) {
      $result['error']['id'] = Configure::read('error.sort_must_be_integer');
      $result['error']['message'] = __('sort must be integer');
    //year
    } else if (isset($data['year']) && !is_integer($data['year'])) {
      $result['error']['id'] = Configure::read('error.year_must_be_integer');
      $result['error']['message'] = __('year must be integer');
    //month
    } else if (isset($data['month']) && !is_integer($data['month'])) {
      $result['error']['id'] = Configure::read('error.month_must_be_integer');
      $result['error']['message'] = __('month must be integer');
    //month_part
    } else if (isset($data['month_part']) &&!is_integer($data['month_part'])) {
      $result['error']['id'] = Configure::read('error.month_part_must_be_integer');
      $result['error']['message'] = __('month_part must be integer');
    } else if (isset($data['month_part']) && ((int)$data['month_part'] != 1 && (int)$data['month_part'] != 2)) {
      $result['error']['id'] = Configure::read('error.invalid_month_part');
      $result['error']['message'] = __('month_part must 1 or 2 only');
    } elseif (isset($data['api_version']) && is_string($data['api_version'])) {
      $result['error']['id'] = Configure::read('error.api_version_must_be_integer');
      $result['error']['message'] = __('api version must not be string');  
    } else {
			$api = new ApiCommonController();
			$userData = (!empty($data['users_api_token'])) ? $api->validateToken($data['users_api_token']) : null;
      if (!empty($data['users_api_token']) && !$userData) {
        $result['error']['id'] = Configure::read('error.invalid_api_token');
        $result['error']['message'] =  __('User not found for the given api token.');
      } else {
        $user = new UserTable($userData);
        $currentYear = date('Y');
        $currentMonth = date('n');
				$currentDay = date('j');
				
				if (empty($data['year']) && empty($data['month']) && empty($data['month_part'])) {//if year month and month_part not supplied fetch the latest record for the date
					if ($currentDay <= 15) {
						$day = 16;
						$month = $currentMonth - 1;
						if ($month == 0) {
							$month = 12;
							$year = $currentYear - 1;
						} else {
							$month = $month;
							$year = $currentYear;
						}
					} else {
						$day = 1;
						$month = $currentMonth;
						$year = $currentYear;
					}
				} else {//since date is given we cannot compute for last record
					$year = isset($data['year']) ? $data['year'] : $currentYear;
					$month = isset($data['month']) ? $data['month'] : $currentMonth;
					if (isset($data['month_part'])) {
						if ($data['month_part'] == 1) {
							$day = 1;
						} else {
							$day = 16;
						}
					} else {
						if ($currentDay <= 15) {
							$day = 1;
						} else {
							$day = 16;
						}
					}
				}
				
        //set start date
        $startDate = date('Y-m-d', strtotime($year . '/' . $month . '/' . $day));
  			$conditions = array(
          'TeacherRanking.rank_in_flag' => 1,
          'TeacherRanking.dummy_data_flag' => 0,         
          'TeacherRanking.start_date' => $startDate,
		  'TeacherRanking.rate IS NOT NULL'
        );

        //check what order
        switch ($data['sort']) {
          case 2 : 
            $order = "TeacherRanking.lesson_count DESC, TeacherRanking.rate DESC, TeacherRanking.reserve_count DESC, Teacher.id ASC";
            break;
          case 3 : 
            $order = "TeacherRanking.reserve_count DESC, TeacherRanking.rate DESC, TeacherRanking.lesson_count DESC, Teacher.id ASC";
            break;
          default:
            $order = "TeacherRanking.rate DESC, TeacherRanking.lesson_count DESC, TeacherRanking.reserve_count DESC, Teacher.id ASC"; 
            break;
        }

        $joins = array(
          array(
            'table' => 'teachers',
            'alias' => 'Teacher',
            'type' => 'LEFT',
            'conditions' => 'TeacherRanking.teacher_id = Teacher.id'
          )
        );

        $fields = array(
          'CountryCode.id',
          'CountryCode.country_name',
          'Teacher.native_speaker_flg',
          'Teacher.homeland2',
          'Teacher.id',
          'Teacher.name',
          'Teacher.jp_name',
          'Teacher.image_url',
          'Teacher.beginner_teacher_flg',
          'Teacher.callan_halfprice_flg',
          'TeacherRanking.rate',
          'TeacherRanking.lesson_count',
          'TeacherRanking.reserve_count'
        );

        if ($userData) {
          $joins[] = array(
            'table' => 'users_favorites',
            'alias' => 'UsersFavorite',
            'type' => 'LEFT',
            'conditions' => 'TeacherRanking.teacher_id = UsersFavorite.teacher_id AND UsersFavorite.user_id = ' . $user->id
          );
          $fields[] = 'UsersFavorite.id';
        }
        $joins[] = array(
          'table' => 'country_codes',
          'alias' => 'CountryCode',
          'conditions' => 'CountryCode.id = Teacher.homeland2',
          'type' => 'INNER'
        );
        //fetch teacher rankings
  			$ranks = $this->TeacherRanking->find('all', array(
          'fields' => $fields,
          'joins' => $joins, 
          'conditions' => $conditions,
					'group' => 'Teacher.id',
          'order' => $order,
          'limit' => $this->limit
        ));
        $i = 1;
        $apiVersion = isset($data['api_version'])? $data['api_version'] : 0;

		if ($ranks) {
			foreach ($ranks as $rank) {
				$teacherDetail = new TeacherTable($rank['Teacher']);
				$teacherRanking = new TeacherRankingsTable($rank['TeacherRanking']);
				$countries = strtolower($rank['CountryCode']['country_name']);
				$explodeCountries = explode(' ', $countries);
				$implode = implode('_',$explodeCountries);
				
				//if api version is greater than or equal to 17
				if ( $user ) {
					$checkCountryCode = (!$user->native_language2) ? 'ja' : $user->native_language2;
					$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $teacherDetail->jp_name : '';
				} elseif ( isset($data['user_language']) && strlen($data['user_language']) > 0 ) {
					$checkCountryCode = !in_array( $data['user_language'], array("ja","ko","th") ) ? "en" : $data['user_language'] ;
					$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $teacherDetail->jp_name : '';
				} else {
					$checkCountryCode = "ja";
					$getUserSettingLanguage = $teacherDetail->jp_name;
				}

        //blocked teacher_id
        $blockList = BlockListTable::getBlocks($user->id);

				$result[$i] = array(
					"id" => $teacherDetail->id,
					"name" => $getUserSettingLanguage,
					"name_ja" => $teacherDetail->jp_name,
					"name_eng" => $teacherDetail->name,
					"status" => $this->getTeacherStatus($teacherDetail->id, (!empty($user->id)) ? $user->id : null),
					"rating" => is_null($teacherRanking->rate) || $teacherRanking->rate == 0 ? null : number_format($teacherRanking->rate,2),
					"lessons" => (int)$teacherRanking->lesson_count,
					"reserves" => (int)$teacherRanking->reserve_count,
					"favorite" => isset($rank['UsersFavorite']['id']) ? true : false,
					"nationality_id" =>(int)$rank['CountryCode']['id'],
					"native_speaker_flg" => (int)$teacherDetail->native_speaker_flg, //waiting redstar feedback
					"image_main" =>  $teacherDetail->getImageUrl(),
					'country_image' => FULL_BASE_URL."/user/images/flag/".$implode.".png",
					"callan_discount_flg" => $teacherDetail->callan_halfprice_flg ? true : false,
					"beginner_teacher_flg" => $teacherDetail->beginner_teacher_flg,
                    "blocked_by_teacher_flg" => (isset($blockList[$teacherDetail->id]) ? 1 : 0)
				);
        unset($result[$i]['name_ja']); 
				$i++;
			}
		} else {
			return;
		}
			}
    }
		return json_encode($result);
  }
  
  /**
  * get teacher status
  * @param INT id - teacher_id
  * @return INT status - teacher status
  * status:  1=standby, 2=preparing (old-reserved), 3=lesson, 4=offline, 5=reserved
  */
  private function getTeacherStatus($teacher_id, $user_id) {
      //lesson onair
      $onairStatus = $this->LessonOnair->find('first', array(
          'fields' => array(
            'LessonOnair.status',
            'LessonOnair.connect_flg',
            'LessonOnair.lesson_type',
            'LessonOnair.user_id'
          ),
          'conditions' => array('LessonOnair.teacher_id' => $teacher_id),
          'recursive' => -1
          ));
      //teacher_status if login or break
      $teacherStatus = $this->TeacherStatus->find('first', array(
          'fields' => array(
                'TeacherStatus.status', 
                'TeacherStatus.remarks1', 
                'TeacherStatus.remarks2'
              ),
          'conditions' => array(
						'TeacherStatus.teacher_id' => $teacher_id,
						'TeacherStatus.status' => 4
					),
          'recursive' => -1
          ));
			$teacherStatus = isset($teacherStatus['TeacherStatus']) ? $teacherStatus['TeacherStatus'] : array();
      //api common
      $api = new ApiCommonController();

      // don't query if user_id is null or no user logged in
      if ($user_id != null) {
        // get reservation
        $nextReservation = $api->getReservation(array(
          'LessonSchedule.teacher_id' => $teacher_id,
          'LessonSchedule.user_id' => $user_id
        ));
      } else {
        $nextReservation = false;
      }
      //set my parameter
      $data = array(
        'LessonOnair' => $onairStatus['LessonOnair'],
        'Teacher' => new TeacherTable(array('id' => $teacher_id)),
        'TeacherStatus' => new TeacherStatusTable($teacherStatus),
        'nextReservation' => isset($nextReservation['teacher_id']) ? $nextReservation['teacher_id'] : 0,
        'userId' => $user_id
      );
      return $api->teacherStatusColor($data);
   }

}//end of class