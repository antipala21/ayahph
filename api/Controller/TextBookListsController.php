<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TextBookListsController extends AppController {
	public $uses = array(
		'User',
		'Textbook',
		'UsersLastViewedTextbook',
		'LessonSchedule',
		'TextbookConnect',
		'TextbookCategoryLevel',
		'TextbookCategory',
		'TeacherBadge',
		'CountryCode'
	);

	//language parameter
	public $language = NULL;

	private $lessonType = array(
		1 => 'lesson_now',
		2 => 'reservation'
	);
	
	private $lessonUserID = null;
	
	private $lastViewedBooksArr = null;

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			'index',
			'reservationTextbook',
			'info',
			'all'
		);
		$this->mem = new myMemcached();
	}

	public function index() {
		header('Content-Type: text/html;charset=utf-8');
		$this->autoRender = false;
		$this->request->data = json_decode($this->request->input(), true);

		$response = array();
		$api = new ApiCommonController();
		if (
			isset($this->request->data['users_api_token']) &&
			!is_string($this->request->data['users_api_token'])
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
			return json_encode($response);
		} else if (
			isset($this->request->data['users_api_token']) &&
			trim($this->request->data['users_api_token']) == ""
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		} else if (
			isset($this->request->data['users_api_token']) &&
			!$api->validateToken($this->request->data['users_api_token'])
		) {
			$response['error']['id'] = Configure::read('error.invalid_api_token');
			$response['error']['message'] = __('Invalid users_api_token');
			return json_encode($response);
		} else if (isset($this->request->data['teachers_id']) && !$api->validateTeachersId($this->request->data['teachers_id'])) {
			$response['error']['id'] = Configure::read('error.invalid_teachers_id');
			$response['error']['message'] = __($api->error);
			return json_encode($response);
		} else if (isset($this->request->data['lesson_type'])) {
			if (!is_int($this->request->data['lesson_type'])) {
				$response['error']['id'] = Configure::read('error.lesson_type_must_be_integer');
				$response['error']['message'] = __('lesson_type must be integer');
				return json_encode($response);
			} else if (!in_array($this->request->data['lesson_type'], array(1,2))) {
				$response['error']['id'] = Configure::read('error.invalid_lesson_type');
				$response['error']['message'] = __('lesson_type must be 1 or 2');
				return json_encode($response);
			}
		}

		$this->User->hasMany = array();
		$this->User->hasOne = array();

		$users = null;
		if (!empty($this->request->data['users_api_token'])) {
			$this->User->openDBReplica();
			$users = $this->User->find('first', array(
				'fields' => array('User.id'),
				'conditions' => array('User.api_token' => $this->request->data['users_api_token']),
				)
			);
			$this->User->closeDBReplica();
		}

		$user = (!empty($users)) ? $users['User']['id'] : null;
		$this->userId = $user;
		$teacher = isset($this->request->data['teachers_id']) ? $this->request->data['teachers_id']: '';
	
		// set lesson user ID for parsing
		$this->lessonUserID = $user;
		
		$lastViewed = $this->lastViewedDate($user);
		$this->lastViewedBooksArr = $lastViewed;

		if ($teacher) {		
			// filter textbook with teacher badge for dropwdown only : display all textbooks for Textbook page	
			$this->TeacherBadge->openDBReplica();	
			$textbookList = $this->TeacherBadge->getTeacherBadge(array('teacher_id' => $teacher));
			$this->TeacherBadge->closeDBReplica();
		} else {		
			$textbookList = array();		
		}
		
		// get preselection textbook
		$response['preselection_textbook'] = $this->getPreselectionTextbook($user);
		
		$req = $this->request->data;
		if (!empty($teacher) && isset($req['lesson_type'])) {
			// get last viewed textbook
			$response['last_viewed_textbook'] = $this->getLastViewedTextbook($user, $teacher, $textbookList);
			// $response['last_viewed_textbook'] = null;
			$getAllTextbooks = $this->getTextbookList($user, $teacher, $req['lesson_type'], $lastViewed);
		} else {
			$response['last_viewed_textbook'] = $this->getLastViewedTextbook($user);
			// $response['last_viewed_textbook'] = null;
			$getAllTextbooks = $this->getTextbookList(null, null, null, $lastViewed);
		}
		// get reservation textbook if today is the schedule lesson reservation
		$getReservationBook = $this->getReservationTextbook($user, $lastViewed);
		$response['reservation_textbook'] = $getReservationBook;
		// textbooks list for course
		$response['course'] = $getAllTextbooks['course'];
		// textbooks list for category
		$response['category'] = $getAllTextbooks['category'];
		return json_encode($response);
	}

	public function lastViewedDate($user) {
		if (empty($user)) { return null; }
		$usersLastViewed = $this->UsersLastViewedTextbook->find('all', array(
			'fields' => array(
				'UsersLastViewedTextbook.connect_id',
				'UsersLastViewedTextbook.last_viewed_date'
			),
			'conditions' => array(
				'UsersLastViewedTextbook.user_id' => $user,
				'UsersLastViewedTextbook.preset' => 0
			),
			'order' => 'UsersLastViewedTextbook.last_viewed_date DESC'
		));
		$userslastview = array();
		foreach ($usersLastViewed as $val) {
			$ulv = $val['UsersLastViewedTextbook'];
			$userslastview[$ulv['connect_id']] = ($ulv['last_viewed_date']) ? $ulv['last_viewed_date'] : null;
		}
		return $userslastview;
	}
	/**
	* info(), gets the user's preselected and lastViewed textbook
	* @param str users_api_token
	* @return array $textbook
	*/
	public function info(){
		header('Content-Type: text/html;charset=utf-8');
		$this->autoRender = false;
		$this->request->data = json_decode($this->request->input(), true);

		$response = array();
		$api = new ApiCommonController();
		
		if (
			isset($this->request->data['users_api_token']) &&
			!is_string($this->request->data['users_api_token'])
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
			return json_encode($response);
		} else if ( !isset($this->request->data['users_api_token']) ) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		}  else if (
			isset($this->request->data['users_api_token']) &&
			trim($this->request->data['users_api_token']) == ""
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		}	
		$users = null;
		$users['User'] = $api->validateToken($this->request->data['users_api_token']);
		if (!$users['User']) {
			$response['error']['id'] = Configure::read('error.invalid_api_token');
			$response['error']['message'] = __('Invalid users_api_token');
			return json_encode($response);
		}
		$this->language = $nativeLang = (isset($users['User']['native_language2']) && $users['User']['native_language2']) ? $users['User']['native_language2'] : 'ja';
		$user = (!empty($users)) ? $users['User']['id'] : null;
		$userCLC = (!empty($users)) ? $users['User']['callan_level_check'] : null;
		$lastViewed = $this->lastViewedDate($user);
		$this->lastViewedBooksArr = $lastViewed;
		// set lesson user ID for parsing
		$this->lessonUserID = $user;
		
		$response['preselection_textbook'] = $this->getPreset($user,$this->request->data['api_version'],$nativeLang);
		$response['last_viewed_textbook'] = $this->getLastView($user,$this->request->data['api_version'],$nativeLang);
		$response['callan_status'] = $this->infoCallanStatus(array(
				"user_id" => $user,
				"level_check" => $userCLC,
			)
		);
		return json_encode($response);
	}
	/**
	* reservationTextbook(), gets the user's reservation textbook
	* @param str users_api_token
	* @return array $textbook
	*/
	public function reservationTextbook() {
		header('Content-Type: text/html;charset=utf-8');
		$this->autoRender = false;
		$this->request->data = json_decode($this->request->input(), true);

		$response = array();
		$api = new ApiCommonController();
		
		if (
			isset($this->request->data['users_api_token']) &&
			!is_string($this->request->data['users_api_token'])
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
			return json_encode($response);
		}  else if ( !isset($this->request->data['users_api_token']) ) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		} else if (
			isset($this->request->data['users_api_token']) &&
			trim($this->request->data['users_api_token']) == ""
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		} 
		$users = null;
		$users['User'] = $api->validateToken($this->request->data['users_api_token']);
		if (!$users['User']) {
			$response['error']['id'] = Configure::read('error.invalid_api_token');
			$response['error']['message'] = __('Invalid users_api_token');
			return json_encode($response);
		}
		$this->language = $nativeLang = (isset($users['User']['native_language2']) && $users['User']['native_language2']) ? $users['User']['native_language2'] : 'ja';
		$user = (!empty($users)) ? $users['User']['id'] : null;
		$lastViewed = $this->lastViewedDate($user);
		$this->lastViewedBooksArr = $lastViewed;
		// set lesson user ID for parsing
		$this->lessonUserID = $user;
		
		$getReservationBook = $this->getReserve($user, $lastViewed,$this->request->data['api_version'],$nativeLang);
		$response['reservation_textbook'] = $getReservationBook;
		return json_encode($response);
	}
	/**
	* all(), gets the user's list of textbook[course and category(series)]
	* @param str users_api_token
	* @return array $textbook
	*/
	public function all() {
		header('Content-Type: text/html;charset=utf-8');
		$this->autoRender = false;
		$this->request->data = json_decode($this->request->input(), true);

		$response = array();
		$api = new ApiCommonController();
		$lang = null;
		
		if (
			isset($this->request->data['users_api_token']) &&
			!is_string($this->request->data['users_api_token'])
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
			return json_encode($response);
		} else if (
			isset($this->request->data['users_api_token']) &&
			trim($this->request->data['users_api_token']) == ""
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		} 

		$users = null;
		if (isset($this->request->data['users_api_token'])) {
			$users['User'] = $api->validateToken($this->request->data['users_api_token']);
			if (!$users['User']) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');
				return json_encode($response);
			}
		}
		$apiVersion = (isset($this->request->data['api_version']) && $this->request->data['api_version'])? $this->request->data['api_version'] : 0;
		$nativeLang = (isset($users['User']['native_language2']) && $users['User']['native_language2']) ? $users['User']['native_language2'] : 'ja';
		$user = (!empty($users)) ? $users['User']['id'] : null;
		$cachedKey = Configure::read('textbook_api_option'); 
		if( !$this->mem->get($cachedKey) ){
			$getAllTextbooks = $this->getList($user,null,null,$this->request->data['api_version'],$nativeLang);
			// textbooks list for course
			$response['course'] = $getAllTextbooks['course'];
			// textbooks list for category
			$response['category'] = $getAllTextbooks['category'];
			
		} else {
			if ( $this->mem->get($cachedKey) != NULL && is_array( $this->mem->get($cachedKey) ) ) {
				$response = $this->mem->get($cachedKey);
			}
		}

		$lang = 'ja' ;
		// Not logged in
		if ( !isset($this->request->data['users_api_token']) && isset($this->request->data['user_language']) ) {
			$lang = strtolower($this->request->data['user_language']);
		}

		if(isset($this->request->data['users_api_token']) && $this->request->data['users_api_token']) {
			$lang = $nativeLang;
		}

		$insertParam = array(
			"user_id" => $user,
			"data" => $response,
			"lang" => $lang
		);
		$response = $this->insertDynamicTextbookVariables($insertParam);

		return json_encode($response);
	}
	/**
	* getPreset(), supplies data to info() func 
	* @param str users_api_token
	* @return array $textbook
	*/
	private function getPreset($userID,$apiVersion,$nativeLanguage) {
		if (empty($userID)) { return null; }
		$textbookArr = array();
		$presetParams = array("user_id" => $userID);

		// Set default textbook
		if ( in_array( $nativeLanguage, array("ko","th") ) ) {
			$conditionParam = array("UsersLastViewedTextbook.user_id" => $userID); // condition param
			$countTextbookViewed = $this->UsersLastViewedTextbook->find("count",array( "conditions" => $conditionParam ) );
			$checkTextbookViewedData = $this->UsersLastViewedTextbook->find("first",array(
					"fields" => array(
						"UsersLastViewedTextbook.id",
						"TextbookCategory.reservation_flg"
					),
					"conditions" => $conditionParam,
					"order" => array('UsersLastViewedTextbook.modified DESC'),
					'joins' => array(
						array(
							'table' => 'textbook_connects',
							'alias' => 'TextbookConnect',
							'type' => 'LEFT',
							'conditions' => array('TextbookConnect.id = UsersLastViewedTextbook.connect_id')
						),
						array(
							'table' => 'textbook_categories',
							'alias' => 'TextbookCategory',
							'type' => 'LEFT',
							'conditions' => array('TextbookCategory.id = TextbookConnect.category_id')
						)
					)
				)
			);

			if ( $countTextbookViewed > 0 ) { // if user has reservation only textbook history
				if ( isset($checkTextbookViewedData["TextbookCategory"]["reservation_flg"]) && $checkTextbookViewedData["TextbookCategory"]["reservation_flg"] == 1 ) {
					$presetParams["default_category_id"] = Configure::read('textbook_default_series_category_id');
				}
			} else { // if user has no textbook history yet
				$presetParams["default_category_id"] = Configure::read('textbook_default_series_category_id');
			}
		}

		$presetBook = $this->UsersLastViewedTextbook->getPresetTextbook($presetParams);
		$textbookType = $presetBook['textbook_type'];
		$textbookCategoryType = $presetBook['textbook_category_type'];
		
		$textbookInfo = $presetBook['textbook_info'];
		
		$connectId = $textbookInfo['TextbookConnect']['id'];
		$chapterId = $textbookInfo['Textbook']['chapter_id'];
		$lessonTextId = $textbookInfo['Textbook']['id'];
		
		$badgeId = ( $textbookInfo['TextbookCategory']['type_id'] == 1 )? $this->Textbook->getTextbookSeriesId( array( "textbook_id" => $textbookInfo['Textbook']['id']) ) : $textbookInfo['TextbookCategory']['id'] ;
		
		$textbookParam = array(
			"category_id" => $textbookInfo['TextbookCategory']['id'],
			"indexCounterDisplay" => $textbookInfo['TextbookCategory']['index_counter_display'],
			"subcategory_id" =>  $textbookInfo['TextbookSubcategory']['id'],
			"connect_id" => $textbookInfo['TextbookConnect']['id']
		);
		if ($textbookType == 1) {
			// course
			$textbookArr['type'] = 'course';
			switch ($nativeLanguage) {
				case 'ja':
					$courseTitle = $textbookInfo['TextbookCategory']['name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name'];
				break;
				case 'ko':
					$courseTitle = $textbookInfo['TextbookCategory']['korean_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['korean_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_ko'];
				break;
				case 'th':
					$courseTitle = $textbookInfo['TextbookCategory']['thai_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['thai_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_th'];
				break;
				default:
					$courseTitle = $textbookInfo['TextbookCategory']['english_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['english_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_eng'];
				break;
			}
			$textbookArr['course_title'] = $courseTitle;
			$textbookArr['course_title_en'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['course_name_en'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$textbookArr['course_name'] = $subcategory;
			$textbookName = $textbookNameTitle;
			$textbookArr['image_url'] = $textbookInfo['TextbookCategory']['image_big_url'];
			$order = $this->listOrder($textbookParam, 1);
			$textbookArr['title'] = $order.$textbookName;
		} else {
			// category
			$textbookArr['type'] = 'category';
			switch ($nativeLanguage) {
				case 'ja':
					$courseTitle = $textbookInfo['TextbookCategory']['name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name'];
				break;
				case 'ko':
					$courseTitle = $textbookInfo['TextbookCategory']['korean_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['korean_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_ko'];
				break;
				case 'th':
					$courseTitle = $textbookInfo['TextbookCategory']['thai_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['thai_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_th'];
				break;
				default:
					$courseTitle = $textbookInfo['TextbookCategory']['english_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['english_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_eng'];
				break;
			}
			$textbookArr['category_title'] = $courseTitle;
			$textbookArr['category_title_en'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['category_name_en'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$textbookArr['category_name'] = $subcategory;
			$textbookName = $textbookNameTitle;
			$textbookArr['image_url'] = $textbookInfo['TextbookCategory']['image_big_url'];
			$order = $this->listOrder($textbookParam, 2, null);
			$textbookArr['title'] = $order.$textbookName;
		} 
		$lastOpened = isset($this->lastViewedBooksArr[$connectId]) ? $this->lastViewedBooksArr[$connectId]: null;
		$textbookArr['category_id'] = (int)$textbookInfo['TextbookCategory']['id'];
		$textbookArr['url'] = $this->parseTBURL(array(
			'class' => $textbookInfo['Textbook']['html_directory'],
			'chapter' => $textbookInfo['Textbook']['chapter_id'],
			'main_html_directory' => $textbookInfo['Textbook']['ori_html_directory'],
			'main_chapter' => $textbookInfo['Textbook']['ori_chapter_id'],
			'alt_html_directory' => $textbookInfo['Textbook']['alt_html_directory'],
			'alt_chapter' => $textbookInfo['Textbook']['alt_chapter_id'],
			'textbook_category_type' => $textbookInfo['TextbookCategory']['textbook_category_type'],
			'user_id' => $userID,
			'licenser' => $textbookInfo['Textbook']['licenser']
		));
		$textbookArr['last_opened_at'] = $lastOpened;
		$textbookArr['connect_id'] = $connectId ? (int)$connectId : null;
		$textbookArr['lesson_text_id'] = $lessonTextId ? (int)$lessonTextId : null;
		$textbookArr['chapter_id'] = $chapterId ? (int)$chapterId : null;
		$textbookArr['textbook_type'] = $textbookType ? (int)$textbookType: null;
		$textbookArr['textbook_category_type'] = (int)$textbookCategoryType;
		$textbookArr['badge_id'] = (int)$badgeId;
		$textbookArr['only_reservation'] = (int)$textbookInfo['TextbookCategory']['reservation_flg'];
		$crParam = array(
			"category_id" => $textbookInfo['TextbookCategory']['id'],
			"textbook_category_type" => $textbookInfo['TextbookCategory']['textbook_category_type']
		);
		$textbookArr['changeable_range'] = TextbookTable::changeableRange($crParam);
		$textbookArr['can_reserve_premium_plan_free'] = $this->isCanReserveTextbook($textbookArr['category_id']);

		return $textbookArr;
	}
	/**
	* getLastView(), supplies data to info() func 
	* @param str users_api_token
	* @return array $textbook
	*/
	private function getLastView($userID,$apiVersion,$nativeLanguage){
		if (empty($userID)) { return null; }

		$countTextbookViewed = $this->UsersLastViewedTextbook->find("count",array( "conditions" => array("UsersLastViewedTextbook.user_id" => $userID,"UsersLastViewedTextbook.preset" => 0) ) );
		if ( $countTextbookViewed == 0 && strlen($nativeLanguage)) {
			return null;
		}

		// check last viewed textbook or preset
		$lnParams = array(
			"select_method" => "first",
			"env_flag" => "lesson_now",
			"preset" => "off",
			"user_id" => $userID
		);
		$getLNTextbookData = $this->Textbook->getTextbooks($lnParams);
		$textbookInfo = $getLNTextbookData['res_data'];
		
		$badgeId = ( $textbookInfo['TextbookCategory']['type_id'] == 1 )? $this->Textbook->getTextbookSeriesId( array( "textbook_id" => $textbookInfo['Textbook']['id']) ) : $textbookInfo['TextbookCategory']['id'] ;
		
		$textbookArr = null;
		$textbookParam = array(
			"category_id" => $textbookInfo['TextbookCategory']['id'],
			"indexCounterDisplay" => $textbookInfo['TextbookCategory']['index_counter_display'],
			"subcategory_id" =>  $textbookInfo['TextbookSubcategory']['id'],
			"connect_id" => $textbookInfo['TextbookConnect']['id']
		);
		// course
		if ($textbookInfo['TextbookCategory']['type_id'] == 1){
			// course
			$textbookArr['type'] = 'course';
			
			switch ($nativeLanguage) {
				case 'ja':
					$courseTitle = $textbookInfo['TextbookCategory']['name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name'];
				break;
				case 'ko':
					$courseTitle = $textbookInfo['TextbookCategory']['korean_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['korean_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_ko'];
				break;
				case 'th':
					$courseTitle = $textbookInfo['TextbookCategory']['thai_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['thai_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_th'];
				break;
				default:
					$courseTitle = $textbookInfo['TextbookCategory']['english_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['english_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_eng'];
				break;
			}
			$textbookArr['course_title'] = $courseTitle;
			$textbookArr['course_name'] = $subcategory;
			$textbookArr['course_title_en'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['course_name_en'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$textbookName = $textbookNameTitle;
			
			$textbookArr['image_url'] = $textbookInfo['TextbookCategory']['image_big_url'];
			$order = $this->listOrder($textbookParam, 1);
			$textbookArr['title'] = $order.$textbookName;
		} else {
			// category
			$textbookArr['type'] = 'category';
			
			switch ($nativeLanguage) {
				case 'ja':
					$courseTitle = $textbookInfo['TextbookCategory']['name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name'];
				break;
				case 'ko':
					$courseTitle = $textbookInfo['TextbookCategory']['korean_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['korean_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_ko'];
				break;
				case 'th':
					$courseTitle = $textbookInfo['TextbookCategory']['thai_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['thai_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_th'];
				break;
				default:
					$courseTitle = $textbookInfo['TextbookCategory']['english_name'];
					$subcategory = $textbookInfo['TextbookSubcategory']['english_name'];
					$textbookNameTitle = $textbookInfo['Textbook']['name_eng'];
				break;
			}
			$textbookArr['category_title'] = $courseTitle;
			$textbookArr['category_name'] = $subcategory;
			$textbookArr['category_title_en'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['category_name_en'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$textbookName = $textbookNameTitle;
			
			$textbookArr['image_url'] = $textbookInfo['TextbookCategory']['image_big_url'];
			$order = $this->listOrder($textbookParam, 2, null);
			$textbookArr['title'] = $order.$textbookName;
		}
	
		$textbookArr['category_id'] = (int)$textbookInfo['TextbookCategory']['id'];
		$textbookArr["url"] = $this->parseTBURL(array(
			'class' => $textbookInfo['Textbook']['html_directory'],
			'chapter' => $textbookInfo['Textbook']['chapter_id'],
			'main_html_directory' => $textbookInfo['Textbook']['ori_html_directory'],
			'main_chapter' => $textbookInfo['Textbook']['ori_chapter_id'],
			'alt_html_directory' => $textbookInfo['Textbook']['alt_html_directory'],
			'alt_chapter' => $textbookInfo['Textbook']['alt_chapter_id'],
			'textbook_category_type' => $textbookInfo['TextbookCategory']['textbook_category_type'],
			'user_id' => $userID,
			'licenser' => $textbookInfo['Textbook']['licenser']
		));
		$textbookArr["last_opened_at"]= isset($this->lastViewedBooksArr[$textbookInfo['TextbookConnect']['id']]) ? $this->lastViewedBooksArr[$textbookInfo['TextbookConnect']['id']]: null;
		$textbookArr["connect_id"] = (int)$textbookInfo['TextbookConnect']['id'];
		$textbookArr["lesson_text_id"] = (int)$textbookInfo['Textbook']['id'];
		$textbookArr["chapter_id"] = (int)$textbookInfo['Textbook']['chapter_id'];
		$textbookArr["textbook_type"] = (int)$textbookInfo['TextbookCategory']['type_id'];
		$textbookArr['textbook_category_type'] = (int)$textbookInfo['TextbookCategory']['textbook_category_type'];
		$textbookArr['badge_id'] = (int)$badgeId;
		$textbookArr['only_reservation'] = (int)$textbookInfo['TextbookCategory']['reservation_flg'];
		$crParam = array(
			"category_id" => $textbookInfo['TextbookCategory']['id'],
			"textbook_category_type" => $textbookInfo['TextbookCategory']['textbook_category_type']
		);
		$textbookArr['changeable_range'] = TextbookTable::changeableRange($crParam);
		$textbookArr['can_reserve_premium_plan_free'] = $this->isCanReserveTextbook($textbookArr['category_id']);

		return $textbookArr;
	}
	/**
	* getReserve(), supplies data to reservationTextbook() func 
	* @param str users_api_token
	* @return array $textbook
	*/
	private function getReserve($user, $lastViewed,$apiVersion,$nativeLang) {
		if (empty($user)) { return null; }
		$response = array();
		$currentResTimeStart = myTools::getCurrentReservationTime();
		$currentResTimeEnd = date("Y-m-d H:i:00", strtotime($currentResTimeStart . " +" . Configure::read('lesson_time') . " minutes"));
		$getLessonSchedule = $this->LessonSchedule->find('first', array(
			'fields' => array(
				'LessonSchedule.lesson_time',
				'LessonSchedule.connect_id'
			),
			'conditions' => array(
				'LessonSchedule.lesson_time >=' => $currentResTimeStart,
				'LessonSchedule.lesson_time <=' => $currentResTimeEnd,
				'LessonSchedule.user_id' => $user
			),
			'order' => array(
				'LessonSchedule.lesson_time' => 'ASC'
			),
			'recursive' => -1
		));
		if (
			$getLessonSchedule &&
			isset($getLessonSchedule['LessonSchedule']) &&
			strtotime($currentResTimeEnd) >= time()
		) {
			$connectID = $getLessonSchedule['LessonSchedule']['connect_id'];
			$textbook = $this->TextbookConnect->find('first', array(
				'joins' => array(
					array(
						'table' => 'textbook_categories',
						'alias' => 'TextbookCategory',
						'type' => 'INNER',
						'conditions' => array('TextbookCategory.id = TextbookConnect.category_id')
					),
					array(
						'table' => 'textbook_subcategories',
						'alias' => 'TextbookSubcategory',
						'type' => 'INNER',
						'conditions' => array('TextbookSubcategory.id = TextbookConnect.subcategory_id')
					),
					array(
						'table' => 'textbooks',
						'alias' => 'Textbook',
						'type' => 'INNER',
						'conditions' => array('Textbook.id = TextbookConnect.textbook_id')
					)
				),
				'fields' => array(
					'TextbookConnect.id',
					'TextbookCategory.type_id',
					'TextbookCategory.id',
					'TextbookCategory.name',
					'TextbookCategory.thai_name',
					'TextbookCategory.korean_name',
					'TextbookCategory.english_name',
					'TextbookCategory.textbook_category_type',
					'TextbookCategory.reservation_flg',
					'TextbookCategory.image_big_url',
					'TextbookSubcategory.id',
					'TextbookSubcategory.name',
					'TextbookSubcategory.thai_name',
					'TextbookSubcategory.korean_name',
					'TextbookSubcategory.english_name',
					'Textbook.id',
					'Textbook.name',
					'Textbook.name_eng',
					'Textbook.name_ko',
					'Textbook.name_th',
					'Textbook.status',
					'Textbook.html_directory',
					'Textbook.alt_html_directory',
					'Textbook.chapter_id',
					'Textbook.alt_chapter_id',
					'Textbook.licenser'
				),
				'conditions' => array(
					'TextbookConnect.id' => $connectID
				)
			));
			
			$badgeId = ( $textbook['TextbookCategory']['type_id'] == 1 )? $this->Textbook->getTextbookSeriesId( array( "textbook_id" => $textbook['Textbook']['id']) ) : $textbook['TextbookCategory']['id'] ;
			
			$textbookParam = array(
				"category_id" => $textbook['TextbookCategory']['id'],
				"indexCounterDisplay" => $textbook['TextbookCategory']['index_counter_display'],
				"subcategory_id" =>  $textbook['TextbookSubcategory']['id'],
				"connect_id" => $textbook['TextbookConnect']['id']
			);

			if ($textbook['TextbookCategory']['type_id'] == 1) {
				$response['type'] = 'course';
				switch ($nativeLang) {
					case 'ja':
						$courseTitle = $textbook['TextbookCategory']['name'];
						$subcategory = $textbook['TextbookSubcategory']['name'];
						$textbookNameTitle = $textbook['Textbook']['name'];
					break;
					case 'ko':
						$courseTitle = $textbook['TextbookCategory']['korean_name'];
						$subcategory = $textbook['TextbookSubcategory']['korean_name'];
						$textbookNameTitle = $textbook['Textbook']['name_ko'];
					break;
					case 'th':
						$courseTitle = $textbook['TextbookCategory']['thai_name'];
						$subcategory = $textbook['TextbookSubcategory']['thai_name'];
						$textbookNameTitle = $textbook['Textbook']['name_th'];
					break;
					default:
						$courseTitle = $textbook['TextbookCategory']['english_name'];
						$subcategory = $textbook['TextbookSubcategory']['english_name'];
						$textbookNameTitle = $textbook['Textbook']['name_eng'];
					break;
				}
				$response['course_title'] = $courseTitle;
				$response['course_name'] = $subcategory;
				$response['course_title_en'] = $textbook['TextbookCategory']['english_name'];
				$response['course_name_en'] = $textbook['TextbookSubcategory']['english_name'];
				$textbookName = $textbookNameTitle;
				$response['image_url'] = $textbook['TextbookCategory']['image_big_url'];
				$order = $this->listOrder($textbookParam, 1);
				$response['title'] = $order.$textbookName;
			} else {
				$response['type'] = 'category';
				switch ($nativeLang) {
					case 'ja':
						$courseTitle = $textbook['TextbookCategory']['name'];
						$subcategory = $textbook['TextbookSubcategory']['name'];
						$textbookNameTitle = $textbook['Textbook']['name'];
					break;
					case 'ko':
						$courseTitle = $textbook['TextbookCategory']['korean_name'];
						$subcategory = $textbook['TextbookSubcategory']['korean_name'];
						$textbookNameTitle = $textbook['Textbook']['name_ko'];
					break;
					case 'th':
						$courseTitle = $textbook['TextbookCategory']['thai_name'];
						$subcategory = $textbook['TextbookSubcategory']['thai_name'];
						$textbookNameTitle = $textbook['Textbook']['name_th'];
					break;
					default:
						$courseTitle = $textbook['TextbookCategory']['english_name'];
						$subcategory = $textbook['TextbookSubcategory']['english_name'];
						$textbookNameTitle = $textbook['Textbook']['name_eng'];
					break;
				}
				$response['category_title'] = $courseTitle;
				$response['category_name'] = $subcategory;
				$response['category_title_en'] = $textbook['TextbookCategory']['english_name'];
				$response['category_name_en'] = $textbook['TextbookSubcategory']['english_name'];
				$textbookName = $textbookNameTitle;
				$response['image_url'] = $textbook['TextbookCategory']['image_big_url'];
				$order = $this->listOrder($textbookParam, 2, null);
				$response['title'] = $order.$textbookName;
			}
			
			$response['category_id'] = (int)$textbook['TextbookCategory']['id'];
			$response['url'] = $this->parseTBURL(array(
				'class' => $textbook['Textbook']['html_directory'],
				'chapter' => $textbook['Textbook']['chapter_id'],
				'main_html_directory' => $textbook['Textbook']['html_directory'],
				'main_chapter' => $textbook['Textbook']['chapter_id'],
				'alt_html_directory' => $textbook['Textbook']['alt_html_directory'],
				'alt_chapter' => $textbook['Textbook']['alt_html_directory'],
				'textbook_category_type' => $textbook['TextbookCategory']['textbook_category_type'],
				'user_id' => $user,
				'licenser' => $textbook['Textbook']['licenser']
			));
			$response['last_opened_at'] = isset($lastViewed[$connectID]) ? $lastViewed[$connectID]: null;
			$response['connect_id'] = (int)$connectID;
			$response['lesson_text_id'] = (int)$textbook['Textbook']['id'];
			$response['chapter_id'] = (int)$textbook['Textbook']['chapter_id'];
			$response['textbook_type'] = (int)$textbook['TextbookCategory']['type_id'];
			$response['textbook_category_type'] = (int)$textbook['TextbookCategory']['textbook_category_type'];
			$response['badge_id'] = (int)$badgeId;
			$response['only_reservation'] = (int)$textbook['TextbookCategory']['reservation_flg'];

			// NC-4657 : changeable range
			$crParam = array(
				"category_id" => $textbook['TextbookCategory']['id'],
				"textbook_category_type" => $textbook['TextbookCategory']['textbook_category_type']
			);
			$response['changeable_range'] = TextbookTable::changeableRange($crParam);
			$response['can_reserve_premium_plan_free'] = $this->isCanReserveTextbook($response['category_id']);
		} else {
			$response = null;
		}
		return $response;
	}
	/**
	* getList(), supplies data to all() func 
	* @param str users_api_token
	* @return array $textbook
	*/

	private function getList($userID = null, $teacherID = null, $lessonType = null,$apiVersion = null,$nativeLanguage = null){
		# get textbooks for 'lesson_now' or 'reservation'
		# get lesson type
		$flag = ($lessonType) ? $this->lessonType[$lessonType] : null;
		
		# courses
		$getCourseArr = array(
			'teacher_id' => $teacherID,
			'env_flag' => $flag,
			'user_id' => $userID,
			'textbook_type' => 1,
			'arrange_data' => 'tree'
		);
		$textbooksCourse = $this->Textbook->getTextbooks($getCourseArr);
		$textbooksCou = $this->arrangeTb(1, $textbooksCourse['res_data'], $userID,$apiVersion,$nativeLanguage);
		# category
		$getCategoryArr = array(
			'teacher_id' => $teacherID,
			'env_flag' => $flag,
			'user_id' => $userID,
			'textbook_type' => 2,
			'arrange_data' => 'tree'
		);
		$textbooksCategory = $this->Textbook->getTextbooks($getCategoryArr);
		$categoriesArr = $categoriesArrTextbook = array();
		// filter textbook category
		foreach ($textbooksCategory['res_data'] as $key){
			if (!in_array($key['TextbookCategory']['id'], $categoriesArr)){
				$categoriesArr[] = $key['TextbookCategory']['id'];
				$categoriesArrTextbook[] = $key;
			}
		}
		$textbooksCat = $this->arrangeTb(2, $categoriesArrTextbook, $userID,$apiVersion,$nativeLanguage);
		return array(
			'course' => $textbooksCou,
			'category' => $textbooksCat
		);
	}
	/**
	* arrangeTb(), supplies and arrange textbook data to getList() func 
	* @param str users_api_token
	* @return array $textbook
	*/
	private function arrangeTb($type, $textbooks, $userID,$apiVersion,$nativeLanguage){
		
		$countryCodeIso = $this->CountryCode->useReplica()->find('first', array(
			'fields' => array(
				'CountryCode.iso_639_1',
				'CountryCode.iso_639_2',
			),
			'conditions' => array('CountryCode.iso_639_1' => $nativeLanguage),
			)
		);
		
		if ($type == 1) {
			$textbooksCourse = $textbooks;

			# course
			foreach ($textbooksCourse as $val){
				$textbooksCourseArr['course_title'] = $val['TextbookCategory']['name'];
				$textbooksCourseArr['course_title_en'] = $val['TextbookCategory']['english_name'];
				
				switch ($nativeLanguage) {
					case 'ja':
						$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
						$courseTitle = $val['TextbookCategory']['name'];
						$descriptionVal = $val['TextbookCategory']['description'];
					break;
					case 'ko':
						$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
						$courseTitle = $val['TextbookCategory']['korean_name'];
						$descriptionVal = $val['TextbookCategory']['description_korean'];
					break;
					case 'th':
						$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
						$courseTitle = $val['TextbookCategory']['thai_name'];
						$descriptionVal = $val['TextbookCategory']['description_thai'];
					break;
					default:
						$setLocale = 'eng';
						$courseTitle = $val['TextbookCategory']['english_name'];
						$descriptionVal = $val['TextbookCategory']['description_english'];
					break;
				}
				$textbooksCourseArr['course_title'] = $courseTitle;
				$descriptionTextbooksCategory = $descriptionVal;
				
				$textbooksCourseArr['only_reservation'] = (int)$val['TextbookCategory']['reservation_flg'];
				$categoryId = (int)$val['TextbookCategory']['id'];
				//NC-3731 - add category ID to API textbook all
				$textbooksCourseArr['category_id'] = $categoryId;
				//get full description
				$description = $descriptionTextbooksCategory;
				// get lesson count
				$matches = null;
				preg_match('#<span[^<>]*>([\d,]+).*?</span>#', $description, $matches); 
				$textbooksCourseArr['lesson_count'] = (isset($matches) && is_numeric($matches[1]) == true) ? $matches[1] : null;
				//get description text only
				$break = explode("<br>", trim($description));
				$split = explode("</span>", $break[0]);

				 if ($matches != null && is_numeric($matches[1])) {
					$textbooksCourseArr['description'] = isset($split[2]) ? strip_tags(trim($split[2])) : null;
				 } else {
					$textbooksCourseArr['description'] = isset($break[0]) ? strip_tags(trim($break[0])) : null;
				}

				// get description url 
				$match = null;
				preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $description, $match);	
				$url = explode("user", $match[2][0]);
				$textbooksCourseArr['description_url'] = (isset($match) && $match[2] != null) ? FULL_BASE_URL .'/user/mobapp'.$url[1] : null;

				// get description url title
				if ( $textbooksCourseArr['description_url'] == null ) {

					$textbooksCourseArr['description_url_title'] = null;
				} else {
					preg_match('/<a[^>]*>(.*?)<\/a>/i', $break[1], $matches);
					Configure::write('Config.language',$setLocale );
					$this->Session->write('Config.language',$setLocale);
					$textbooksCourseArr['description_url_title'] = (isset($matches[1])) ? strip_tags(trim($matches[1].__d('default','について'))) : null;
				}

				$textbooksCourseArr['image_big'] = isset($val['TextbookCategory']['image_big_url']) ? $val['TextbookCategory']['image_big_url'] : null;
				$textbooksCourseArr['level_id'] = ($this->categoryLevels($categoryId) != null) ? $this->categoryLevels($categoryId) : null;
				$textbookCategoryType = (int)$val['TextbookCategory']['textbook_category_type'];
				$indexCounterDisplay = $val['TextbookCategory']['index_counter_display'];
				$textbooksCourseArr['courses'] = array();
				if ($val['TextbookSubcategory']){
					$curDownTotal = count($val['TextbookSubcategory']);
					$cur = 0;
					foreach ($val['TextbookSubcategory'] as $key){
						$cur++;
						if (!is_null($key['name']) && !is_null($key['english_name'])) {
							$course = array();
							$count = 1;			
							$course['course_name'] = $key['name'];
							$course['course_name_en'] = $key['english_name'];
							
							switch ($nativeLanguage) {
								case 'ja':
									$courseTitle = $key['name'];
								break;
								case 'ko':
									$courseTitle = $key['korean_name'];
								break;
								case 'th':
									$courseTitle = $key['thai_name'];
								break;
								default:
									$courseTitle = $key['english_name'];
								break;
							}
							$course['course_name'] = $courseTitle;
							
							if ($key['textbooks']){
								$countDownTotal = count($key['textbooks']);
								$countTotal = count($key['textbooks']);
								$textbooks = array();
								foreach ($key['textbooks'] as $chapter){
									$paramArrCourse = array(
										"cur" => $cur,
										"count" => $count,
										"curDownTotal" => $curDownTotal,
										"countDownTotal" => $countDownTotal,
										"countTotal" => $countTotal,
										"textbook_type" => 1, // course
										"indexCounterDisplay" => $indexCounterDisplay
									);
									$order = TextbookTable::orderingCounterIndex($paramArrCourse);
									$countDownTotal--;
									$count++;
									$canReserve = $this->isCanReserveTextbook($categoryId);
									
									switch ($nativeLanguage) {
										case 'ja':
											$chapterTitle = $order.$chapter['name'];
										break;
										case 'ko':
											$chapterTitle = $order.$chapter['name_ko'];
										break;
										case 'th':
											$chapterTitle = $order.$chapter['name_th'];
										break;
										default:
											$chapterTitle = $order.$chapter['name_eng'];
										break;
									}
									$titleChapter = $chapterTitle;
									
									$ttextbooksInfo = array(
										"title" => $titleChapter,
										"category_id" => $categoryId,
										"url" => $this->parseTBURL(array(
											'class' => $chapter['html_directory'],
											'chapter' => $chapter['chapter_id'],
											'main_html_directory' => $chapter['ori_html_directory'],
											'main_chapter' => $chapter['ori_chapter_id'],
											'alt_html_directory' => $chapter['alt_html_directory'],
											'alt_chapter' => $chapter['alt_chapter_id'],
											'textbook_category_type' => $textbookCategoryType,
											'user_id' => $userID,
											'licenser' => $chapter['licenser']
										)),
										"last_opened_at"  => null,
										"connect_id"  => (int)$chapter['connect_id'],
										"lesson_text_id"  => (int)$chapter['id'],
										"chapter_id"  => (int)$chapter['chapter_id'],
										"textbook_type"  => 1,
										"badge_id"  => $this->Textbook->getTextbookSeriesId( array( "textbook_id" => $chapter['id']) ),
										"textbook_category_type"  => $textbookCategoryType,
										"can_reserve_premium_plan_free" => $canReserve
									);
									$textbooks[] = $ttextbooksInfo;
								
								}
								$course['textbooks'] = $textbooks;
							}
							$textbooksCourseArr['courses'][] =  $course;
						}
						$curDownTotal--;
					}
				}
				$coursesDetailArray[] = $textbooksCourseArr;
			}
			return $coursesDetailArray;
		} else if ($type == 2){
			$categories = $textbooks;
			# category
			foreach ($categories as $val){
				$textbookCatArr['category_title'] = $val['TextbookCategory']['name'];
				$textbookCatArr['category_title_en'] = $val['TextbookCategory']['english_name'];
				
				switch ($nativeLanguage) {
					case 'ja':
						$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
						$categoryTitle = $val['TextbookCategory']['name'];
						$descriptionVal = $val['TextbookCategory']['description'];
					break;
					case 'ko':
						$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
						$categoryTitle = $val['TextbookCategory']['korean_name'];
						$descriptionVal = $val['TextbookCategory']['description_korean'];
					break;
					case 'th':
						$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
						$categoryTitle = $val['TextbookCategory']['thai_name'];
						$descriptionVal = $val['TextbookCategory']['description_thai'];
					break;
					default:
						$setLocale = 'eng';
						$categoryTitle = $val['TextbookCategory']['english_name'];
						$descriptionVal = $val['TextbookCategory']['description_english'];
					break;
				}
				$textbookCatArr['category_title'] = $categoryTitle;
				$descriptionTextbooksCategory = $descriptionVal;
				
				$textbookCatArr['only_reservation'] = (int)$val['TextbookCategory']['reservation_flg'];
				$categoryId = (int)$val['TextbookCategory']['id'];
				//NC-3731 - add category ID to API textbook all
				$textbookCatArr['category_id'] = $categoryId;
				//get full description
				$description = $descriptionTextbooksCategory;
				// get lesson count
				$matches = null;
				preg_match('#<span[^<>]*>([\d,]+).*?</span>#', $description, $matches); 
				$textbookCatArr['lesson_count'] = (isset($matches) && is_numeric($matches[1]) == true) ? $matches[1] : null;
				//get description text only
				$break = explode("<br>", trim($description));
				$split = explode("</span>", $break[0]);
				if ($matches != null && is_numeric($matches[1])) {
					$textbookCatArr['description'] = isset($split[2]) ? strip_tags(trim($split[2])) : null;
				} else {
					$textbookCatArr['description'] = isset($break[0]) ? strip_tags(trim($break[0])) : null;
				}

				// get description url 
				$match = null;
				preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $description, $match);	
				$url = explode("user", $match[2][0]);
				$textbookCatArr['description_url'] = (isset($match) && $match[2] != null) ?  FULL_BASE_URL .'/user/mobapp'.$url[1] : null;

				// get description url title
				if ( $textbookCatArr['description_url'] == null ) {

					$textbookCatArr['description_url_title'] = null;
				} else {
					preg_match('/<a[^>]*>(.*?)<\/a>/i', $break[1], $matches);
					Configure::write('Config.language',$setLocale );
					$this->Session->write('Config.language',$setLocale);
					$textbookCatArr['description_url_title'] = (isset($matches[1])) ? strip_tags(trim($matches[1].__d('default','について'))) : null;
				}

				$textbookCatArr['image_big'] = isset($val['TextbookCategory']['image_big_url']) ? $val['TextbookCategory']['image_big_url'] : null;
				$textbookCatArr['level_id'] = ($this->categoryLevels($categoryId) != null) ? $this->categoryLevels($categoryId) : null;
				$textbookCategoryType = (int)$val['TextbookCategory']['textbook_category_type'];
				$indexCounterDisplay = $val['TextbookCategory']['index_counter_display'];
				$textbookCatArr['categories'] = array();
				if ($val['TextbookSubcategory']){
					foreach ($val['TextbookSubcategory'] as $key){
						if (!is_null($key['name']) && !is_null($key['english_name'])) {
							$cat = array();
							$ccatTextbook = array();
							$cat['category_name'] = $key['name'];
							$cat['category_name_en'] = $key['english_name'];
							
							switch ($nativeLanguage) {
								case 'ja':
									$categoryName = $key['name'];
								break;
								case 'ko':
									$categoryName = $key['korean_name'];
								break;
								case 'th':
									$categoryName = $key['thai_name'];
								break;
								default:
									$categoryName = $key['english_name'];
								break;
							}
							$cat['category_name'] = $categoryName;
							
							$count = 1;
							if ($key['textbooks']){
								$countDownTotal = count($key['textbooks']);
								foreach ($key['textbooks'] as $chapter){
									$paramArrSeries = array(
										"count" => $count,
										"countDownTotal" => $countDownTotal,
										"textbook_type" => 2, // course
										"indexCounterDisplay" => $indexCounterDisplay
									);
									$order = TextbookTable::orderingCounterIndex($paramArrSeries);
									$countDownTotal--;
									$canReserve = $this->isCanReserveTextbook($categoryId);
									
									switch ($nativeLanguage) {
										case 'ja':
											$chapterTitle = $order.$chapter['name'];
										break;
										case 'ko':
											$chapterTitle = $order.$chapter['name_ko'];
										break;
										case 'th':
											$chapterTitle = $order.$chapter['name_th'];
										break;
										default:
											$chapterTitle = $order.$chapter['name_eng'];
										break;
									}
									$titleChapter = $chapterTitle;
									
									$ttextbooksInfo = array(
										"title" => $titleChapter,
										"category_id" => $categoryId,
										"url" => $this->parseTBURL(array(
											'class' => $chapter['html_directory'],
											'chapter' => $chapter['chapter_id'],
											'main_html_directory' => $chapter['ori_html_directory'],
											'main_chapter' => $chapter['ori_chapter_id'],
											'alt_html_directory' => $chapter['alt_html_directory'],
											'alt_chapter' => $chapter['alt_chapter_id'],
											'textbook_category_type' => $textbookCategoryType,
											'user_id' => $userID,
											'licenser' => $chapter['licenser']
										)),
										"last_opened_at"  => null,
										"connect_id"  => (int)$chapter['connect_id'],
										"lesson_text_id"  => (int)$chapter['id'],
										"chapter_id"  => (int)$chapter['chapter_id'],
										"textbook_type"  => 2,
										"badge_id"  => $val['TextbookCategory']['id'],
										"textbook_category_type"  => $textbookCategoryType,
										"can_reserve_premium_plan_free" => $canReserve
									);

									$ccatTextbook[] = $ttextbooksInfo;
								}
								$cat['textbooks'] = $ccatTextbook;
							}
							$textbookCatArr['categories'][] = $cat;
						}
					}
				}
				$categoryTextbooks[] = $textbookCatArr;
			}

			return $categoryTextbooks;
		}
		return array();
	}
	/**
	* getCallanLevelCheckStatus(), supplies data to arrangeTb() func 
	* @param str users_api_token
	* @return array $textbook
	*/	
	private function getCallanLevelCheckStatus( $param = array() ) {
		$result = null;
		$levelCheck = $getLessonSched = false;
		$userId = isset($param['user_id'])? $param['user_id'] : null;
		$connectId = isset($param['connect_id'])? $param['connect_id'] : null;

		if( $userId != null && $connectId != null ) {
			
			// check user's callan_level_check
			$getUserLC = $this->User->find("first",array(
					"conditions" => array( "User.id" => $userId ),
					"fields" => array( "User.callan_level_check" ),
					"recursive" => -1
				)
			);

			if( $getUserLC['User']['callan_level_check'] == 2 ) {
				$levelCheck = true;
			}
			
			// check user's lesson schedule
			$getLessonSched = $this->LessonSchedule->find('first', array(
					'fields' => array(
						'LessonSchedule.lesson_time',
						'LessonSchedule.connect_id'
					),
					'conditions' => array(
						'LessonSchedule.lesson_time >=' => date("Y-m-d H:i:s", time()),
						'LessonSchedule.user_id' => $userId,
						'LessonSchedule.connect_id' => $connectId
					),
					'order' => array(
						'LessonSchedule.lesson_time' => 'ASC'
					),
					'recursive' => -1
				)
			);
			

			// Callan Level check status
			if( !$levelCheck && !$getLessonSched ) {
				// level check is not done , level check is not reserved
				$result = 1;
			} elseif( !$levelCheck && $getLessonSched ) {
				// level check is not done , level check is reserved now
				$result = 2;
			} elseif( $levelCheck ) {
				// level check is done 
				$result = 3;
			}
		}
		return $result;
	}
	/**
	* infoCallanStatus(), supplies data to info() func 
	* @param str users_api_token
	* @return array $textbook
	*/	
	private function infoCallanStatus($param = array()){
		$result = 0;
		$levelCheck = $getLessonSched = false;
		
		$userId = isset($param['user_id'])? $param['user_id'] : null ;
		$levelCheckData = isset($param['level_check'])? $param['level_check'] : null ;

		if( $userId != null && $levelCheckData != null ){
			// callan level check
			if( $levelCheckData == 2 ) {
				$levelCheck = true;
			}
			
			// check user's lesson schedule
			$getLessonSched = $this->LessonSchedule->find('first', array(
					'fields' => array( 'LessonSchedule.id' ),
					'conditions' => array(
						'LessonSchedule.lesson_time >=' => date("Y-m-d H:i:s", time()),
						'LessonSchedule.user_id' => $userId,
						'TextbookCategory.status' => 1,
						'TextbookCategory.textbook_category_type' => 2,
						'Textbook.callan_level_check' => 1
					),
					'joins' => array(
						array(
							'table' => 'textbook_connects',
							'alias' => 'TextbookConnect',
							'type' => 'LEFT',
							'conditions' => array('TextbookConnect.id = LessonSchedule.connect_id')
						),						
						array(
							'table' => 'textbook_categories',
							'alias' => 'TextbookCategory',
							'type' => 'LEFT',
							'conditions' => array('TextbookCategory.id = TextbookConnect.category_id')
						),						
						array(
							'table' => 'textbooks',
							'alias' => 'Textbook',
							'type' => 'LEFT',
							'conditions' => array('Textbook.id = TextbookConnect.textbook_id')
						),
					),
					'order' => array(
						'LessonSchedule.lesson_time' => 'ASC'
					)
				)
			);
			
			// Callan Level check status
			if( !$levelCheck && !$getLessonSched ) {
				// level check is not done , level check is not reserved
				$result = 1;
			} elseif( !$levelCheck && $getLessonSched ) {
				// level check is not done , level check is reserved now
				$result = 2;
			} elseif( $levelCheck ) {
				// level check is done 
				$result = 3;
			}
			
		}
		return $result;
	}
	/**
	* insertUserTextbookLog(), supplies data to all() func 
	* @param str users_api_token
	* @return array $textbook
	*/	
	private function insertUserTextbookLog($param = array()) {
		$result = array();
		$data = isset($param['data'])? $param['data'] : array() ;
		$userId = isset($param['user_id'])? $param['user_id'] : null ;
		if ( count($data) > 1 && is_array($data) ) {
			
			if ($userId != null) {
				// get userLastViewed 
				$getLastLog = $this->UsersLastViewedTextbook->find("list",array(
					"conditions" => array(
						"UsersLastViewedTextbook.user_id" => $userId,
						"UsersLastViewedTextbook.preset" => 0
					),
					"fields" => array(
						'UsersLastViewedTextbook.connect_id',
						'UsersLastViewedTextbook.last_viewed_date'
					),
					"recursive" => -1
				));
				$arrLastLog = $getLastLog;
			}
			
			foreach( $data as $bookTypeIndex => $bookTypeValue ){
				foreach($bookTypeValue as $catIndex => $catValue){
					$catNameMarker = ($bookTypeIndex == "course")?"courses":"categories";
					foreach($catValue[$catNameMarker] as $subCatIndex => $subCatValue){
						foreach($subCatValue['textbooks'] as $textbookIndex => $textbookValue){

							if ($userId != null) {
								if( count($arrLastLog) > 0 ) {
									if( in_array( $textbookValue['connect_id'], array_keys($arrLastLog) ) ) {
										$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['last_opened_at'] = $arrLastLog[$textbookValue['connect_id']];
									}
								}
							}
							
							$categoryId = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['category_id'];
							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['can_reserve_premium_plan_free'] = $this->isCanReserveTextbook($categoryId);
							

							if ($userId != null) { 
								// callan level check
								if( $textbookValue['textbook_category_type'] == 2 && $textbookValue['chapter_id'] == 1 ){
									$lvlCheckParam = array(
										"user_id" => $userId,
										"connect_id" => $textbookValue['connect_id']
									);
									$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['callan_status'] = $this->getCallanLevelCheckStatus($lvlCheckParam);
								}
								$oldUrl = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['url'];
								$newUrl = $this->insertUserIdUrl( array(
										"user_id" => $userId,
										"old_url" => $oldUrl
									) 
								);
								$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['url'] = $newUrl;
							}
							
						}
					}
				}
			}
			$result = $data;
		}

		return $result;
	}
	/**
	* isCanReserveTextbook, check if the user is allowed to reserve the textbook
	* @param int $textbookCategoryId
	* @return int $canReserve
	*/
	private function isCanReserveTextbook($textbookCategoryId) {
		$canReserve = 1;
		if (in_array($textbookCategoryId, Configure::read('eiken_category_ids'))) {
			$canReserve = 0;
		}
		return $canReserve;
	}
	/*
	* insertDynamicTextbookVariables(), supplies data to all() func 
	* @param array
	* @return array $textbook
	*/	
	private function insertDynamicTextbookVariables($param = array()) {
		$result = array();
		$data = isset($param['data'])? $param['data'] : array() ;
		$userId = isset($param['user_id'])? $param['user_id'] : null ;
		$lang = isset($param['lang'])? strtolower($param['lang']) : "ja" ;
		$catX = 0;

		$countryCodeIso = $this->CountryCode->useReplica()->find("first",array(
				"conditions" => array( "CountryCode.iso_639_1" => $lang ),
				"fields" => array("CountryCode.iso_639_2"),
				"recursive" => -1
			)
		);

		switch ($lang) {
			case 'ja':
				$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
			break;
			case 'ko':
				$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
			break;
			case 'th':
				$setLocale = $countryCodeIso["CountryCode"]["iso_639_2"];
			break;
			default:
				$setLocale = 'eng';
			break;
		}

		Configure::write('Config.language',$setLocale);
		$this->Session->write('Config.language',$setLocale);

		if ( count($data) > 0 && is_array($data) ) {
			// Db Translation
			$langParam = array("lang"=> $lang);
			$catLangArr = TextbookTable::catDbTranslationArr($langParam); // note : array( catId => array(id => value, name => value, description => value ) )
			$subLangArr = TextbookTable::subDbTranslationArr($langParam); // note : array( subCatId =>  value )
			$txtLangArr = TextbookTable::txtDbTranslationArr($langParam); // note : array( catId => array(id => value, name => value, description => value ) )

			if ( $userId != null || strlen($userId) > 0 ) { // users textbook log date
				// get userLastViewed 
				$getLastLog = $this->UsersLastViewedTextbook->useReplica()->find("list",array(
					"conditions" => array(
						"UsersLastViewedTextbook.user_id" => $userId,
						"UsersLastViewedTextbook.preset" => 0
					),
					"fields" => array(
						'UsersLastViewedTextbook.connect_id',
						'UsersLastViewedTextbook.last_viewed_date'
					),
					"recursive" => -1
				));
				$arrLastLog = $getLastLog;
			}

			// NC-4896 : Selected textbooks for Thailand(th) and Korea(ko)
			if ($lang == 'th' || $lang == 'ko' ) {
				unset($data['course']);
			}

			foreach( $data as $bookTypeIndex => $bookTypeValue ){
				// Textbook category
				foreach($bookTypeValue as $catIndex => $catValue){
					$newSubCatArr = array(); // NC-4896-asia-api
					$textbookCategoryName = "";
					$catNameMarker = ($bookTypeIndex == "course")?"courses":"categories";
					$catId = $data[$bookTypeIndex][$catIndex]["category_id"];

					

					if ( $catNameMarker == "courses" ) {

						$data[$bookTypeIndex][$catIndex]["course_title_en"] = $data[$bookTypeIndex][$catIndex]["course_english_title"];
						unset($data[$bookTypeIndex][$catIndex]["course_english_title"]); 

						$data[$bookTypeIndex][$catIndex]["course_title"] = isset($catLangArr[$catId]["name"]) ? $catLangArr[$catId]["name"] : null ;
						$textbookCategoryName = $data[$bookTypeIndex][$catIndex]["course_title"];
					}
					if ( $catNameMarker == "categories" ) {

						$data[$bookTypeIndex][$catIndex]["category_title_en"] = $data[$bookTypeIndex][$catIndex]["category_english_title"];
						unset($data[$bookTypeIndex][$catIndex]["category_english_title"]); 

						$data[$bookTypeIndex][$catIndex]["category_title"] = isset($catLangArr[$catId]["name"]) ? $catLangArr[$catId]["name"] : null ;
						$textbookCategoryName = $data[$bookTypeIndex][$catIndex]["category_title"];

					}
					
					$data[$bookTypeIndex][$catIndex]["description"] = isset($catLangArr[$catId]["description"]) ? strip_tags(trim($catLangArr[$catId]["description"])) : null ;
					// remove store url for thailand and korea
					if ($lang == 'th' || $lang == 'ko' ) {
						$data[$bookTypeIndex][$catIndex]["store_url"] = null;
					}

					// NC-5115
					if ( $lang == "en" ) {
						$data[$bookTypeIndex][$catIndex]["description_url_title"] = isset( $data[$bookTypeIndex][$catIndex]["description_url_title"] ) && strlen($data[$bookTypeIndex][$catIndex]["description_url_title"]) > 0 ? "about ".$textbookCategoryName : null ;
					} else {
						$data[$bookTypeIndex][$catIndex]["description_url_title"] = isset( $data[$bookTypeIndex][$catIndex]["description_url_title"] ) && strlen($data[$bookTypeIndex][$catIndex]["description_url_title"]) > 0 ? $textbookCategoryName.__d('default','について') : null ;
					}
					

					// Texbook subcategories
					foreach($catValue[$catNameMarker] as $subCatIndex => $subCatValue){
						$subCatId = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["sub_cat_id"];

						if ( $catNameMarker == "courses" ) {

							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["course_name_en"] = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["course_english_name"];
							unset($data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["course_english_name"]);
							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["course_name"] = isset($subLangArr[$subCatId]) ? $subLangArr[$subCatId] : null ;
						}
						if ( $catNameMarker == "categories" ) {
							
							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["category_name_en"] = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["category_english_name"];
							unset($data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["category_english_name"]);

							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]["category_name"] = isset($subLangArr[$subCatId]) ? $subLangArr[$subCatId] : null ;
						}

						// Textbook chapters
						foreach($subCatValue['textbooks'] as $textbookIndex => $textbookValue){
							$textId = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['lesson_text_id'];
							$textTitle = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['title'];
							$getIndexNum = self::getIndexNum(array("title" => $textTitle));
							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['title'] = isset($txtLangArr[$textId]) ? $getIndexNum.$txtLangArr[$textId] : null ;
							if ( $userId != null || strlen($userId) > 0 ) { // users textbook log date
								if( count($arrLastLog) > 0 ) {
									if( in_array( $textbookValue['connect_id'], array_keys($arrLastLog) ) ) {
										$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['last_opened_at'] = $arrLastLog[$textbookValue['connect_id']];
									}
								}
								// callan level check
								if( $textbookValue['textbook_category_type'] == 2 && $textbookValue['chapter_id'] == 1 ){
									$lvlCheckParam = array(
										"user_id" => $userId,
										"connect_id" => $textbookValue['connect_id']
									);
									$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['callan_status'] = $this->getCallanLevelCheckStatus($lvlCheckParam);
								}
								$oldUrl = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['url'];
								$newUrl = $this->insertUserIdUrl( array(
										"user_id" => $userId,
										"old_url" => $oldUrl
									) 
								);
								$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['url'] = $newUrl;
								
							}
							//append language parameter
							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['url'] .= ($lang == 'ja')? '' : '&la=' . $lang;
							$categoryId = $data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['category_id'];
							$data[$bookTypeIndex][$catIndex][$catNameMarker][$subCatIndex]['textbooks'][$textbookIndex]['can_reserve_premium_plan_free'] = $this->isCanReserveTextbook($categoryId);
						}
					}

					// NC-4896 : Selected textbooks for Thailand(th) and Korea(ko)
					if ( $catNameMarker == "categories" ) {
						$textbookCategoryIdArr = Configure::read('allowed_textbooks_for_foreign');

						if ($lang == 'th' || $lang == 'ko' ) {
							
							if ( in_array($catId, $textbookCategoryIdArr) ) {
								$newSubCatArr = $data[$bookTypeIndex][$catIndex];
								$data[$bookTypeIndex][$catX++] = $newSubCatArr;
							}

							if ( !in_array($catIndex, $newSubCatArr) ) {
								unset( $data[$bookTypeIndex][$catIndex] );
							}
						}

					}

				}
			}
			$result = $data;
		}
		return $result;
	}
	/**
	* getPreselectionTextbook, gets the user's preselected textbook
	* @param int $userID, user's id
	* @return array $textbook
	*/
	private function getPreselectionTextbook($userID){
		if (empty($userID)) { return null; }
		$textbookArr = array();
		$presetParams = array("user_id" => $userID);
		$presetBook = $this->UsersLastViewedTextbook->getPresetTextbook($presetParams);
		$textbookType = $presetBook['textbook_type'];
		$textbookCategoryType = $presetBook['textbook_category_type'];
		
		$textbookInfo = $presetBook['textbook_info'];
		
		$connectId = $textbookInfo['TextbookConnect']['id'];
		$chapterId = $textbookInfo['Textbook']['chapter_id'];
		$lessonTextId = $textbookInfo['Textbook']['id'];
		
		$textbookParam = array(
			"category_id" => $textbookInfo['TextbookCategory']['id'],
			"indexCounterDisplay" => $textbookInfo['TextbookCategory']['index_counter_display'],
			"subcategory_id" =>  $textbookInfo['TextbookSubcategory']['id'],
			"connect_id" => $textbookInfo['TextbookConnect']['id']
		);
		if ($textbookType == 1) {
			// course
			$textbookArr['type'] = 'course';
			$textbookArr['course_title'] = $textbookInfo['TextbookCategory']['name'];
			$textbookArr['course_english_title'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['course_name'] = $textbookInfo['TextbookSubcategory']['name'];
			$textbookArr['course_english_name'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$order = $this->listOrder($textbookParam, 1);
		} else {
			// category
			$textbookArr['type'] = 'category';
			$textbookArr['category_title'] = $textbookInfo['TextbookCategory']['name'];
			$textbookArr['category_english_title'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['category_name'] = $textbookInfo['TextbookSubcategory']['name'];
			$textbookArr['category_english_name'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$order = $this->listOrder($textbookParam, 2, null);
		} 
		$lastOpened = isset($this->lastViewedBooksArr[$connectId]) ? $this->lastViewedBooksArr[$connectId]: null;
		$textbookArr['title'] = $order.$textbookInfo['Textbook']['name'];
		$textbookArr['category_id'] = (int)$textbookInfo['TextbookCategory']['id'];
		$textbookArr['url'] = $this->parseTBURL(array(
			'class' => $textbookInfo['Textbook']['html_directory'],
			'chapter' => $textbookInfo['Textbook']['chapter_id'],
			'main_html_directory' => $textbookInfo['Textbook']['ori_html_directory'],
			'main_chapter' => $textbookInfo['Textbook']['ori_chapter_id'],
			'alt_html_directory' => $textbookInfo['Textbook']['alt_html_directory'],
			'alt_chapter' => $textbookInfo['Textbook']['alt_chapter_id'],
			'textbook_category_type' => $textbookInfo['TextbookCategory']['textbook_category_type'],
			'user_id' => $userID,
			'licenser' => $textbookInfo['Textbook']['licenser']
		));
		$textbookArr['last_opened_at'] = $lastOpened;
		$textbookArr['connect_id'] = $connectId ? (int)$connectId : null;
		$textbookArr['lesson_text_id'] = $lessonTextId ? (int)$lessonTextId : null;
		$textbookArr['chapter_id'] = $chapterId ? (int)$chapterId : null;
		$textbookArr['textbook_type'] = $textbookType ? (int)$textbookType: null;
		$textbookArr['textbook_category_type'] = (int)$textbookCategoryType;
		$textbookArr["display_flag"] = true;
		
		return $textbookArr;
	}

	private function getDisplayFlag($textbooks, $teacher = null, $textbookList = array()) {

		$displayFlag = ($teacher)?((in_array($textbooks, $textbookList['textbookIdArray'])) ? true : false) : true;
		return $displayFlag;
	}

	/**
	* getReservationTextbook, get the reserved textbook
	* @param int $user
	* @param array $lastViewed -> lastviewed dates of textbooks
	* @return array $response -> textbook information
	*/
	private function getReservationTextbook($user, $lastViewed, $teacher = null, $textbookList = array()) {
		if (empty($user)) { return null; }
		$response = array();
		$currentResTimeStart = myTools::getCurrentReservationTime();
		$currentResTimeEnd = date("Y-m-d H:i:00", strtotime($currentResTimeStart . " +" . Configure::read('lesson_time') . " minutes"));
		$getLessonSchedule = $this->LessonSchedule->find('first', array(
			'fields' => array(
				'LessonSchedule.lesson_time',
				'LessonSchedule.connect_id'
			),
			'conditions' => array(
				'LessonSchedule.lesson_time >=' => $currentResTimeStart,
				'LessonSchedule.lesson_time <=' => $currentResTimeEnd,
				'LessonSchedule.user_id' => $user
			),
			'order' => array(
				'LessonSchedule.lesson_time' => 'ASC'
			),
			'recursive' => -1
		));
		
		if (
			$getLessonSchedule &&
			isset($getLessonSchedule['LessonSchedule']) &&
			strtotime($currentResTimeEnd) >= time()
		) {
			$connectID = $getLessonSchedule['LessonSchedule']['connect_id'];
			$textbook = $this->TextbookConnect->find('first', array(
				'joins' => array(
					array(
						'table' => 'textbook_categories',
						'alias' => 'TextbookCategory',
						'type' => 'INNER',
						'conditions' => array('TextbookCategory.id = TextbookConnect.category_id')
					),
					array(
						'table' => 'textbook_subcategories',
						'alias' => 'TextbookSubcategory',
						'type' => 'INNER',
						'conditions' => array('TextbookSubcategory.id = TextbookConnect.subcategory_id')
					),
					array(
						'table' => 'textbooks',
						'alias' => 'Textbook',
						'type' => 'INNER',
						'conditions' => array('Textbook.id = TextbookConnect.textbook_id')
					)
				),
				'fields' => array(
					'TextbookConnect.id',
					'TextbookCategory.type_id',
					'TextbookCategory.id',
					'TextbookCategory.name',
					'TextbookCategory.english_name',
					'TextbookCategory.textbook_category_type',
					'TextbookSubcategory.id',
					'TextbookSubcategory.name',
					'TextbookSubcategory.english_name',
					'Textbook.id',
					'Textbook.name',
					'Textbook.status',
					'Textbook.html_directory',
					'Textbook.alt_html_directory',
					'Textbook.chapter_id',
					'Textbook.alt_chapter_id',
					'Textbook.licenser'
				),
				'conditions' => array(
					'TextbookConnect.id' => $connectID
				)
			));
			$textbookParam = array(
				"category_id" => $textbook['TextbookCategory']['id'],
				"indexCounterDisplay" => $textbook['TextbookCategory']['index_counter_display'],
				"subcategory_id" =>  $textbook['TextbookSubcategory']['id'],
				"connect_id" => $textbook['TextbookConnect']['id']
			);

			if ($textbook['TextbookCategory']['type_id'] == 1) {
				$response['type'] = 'course';
				$response['course_title'] = $textbook['TextbookCategory']['name'];
				$response['course_english_title'] = $textbook['TextbookCategory']['english_name'];
				$response['course_name'] = $textbook['TextbookSubcategory']['name'];
				$response['course_english_name'] = $textbook['TextbookSubcategory']['english_name'];
				$order = $this->listOrder($textbookParam, 1);
			} else {
				$response['type'] = 'category';
				$response['category_title'] = $textbook['TextbookCategory']['name'];
				$response['category_english_title'] = $textbook['TextbookCategory']['english_name'];
				$response['category_name'] = $textbook['TextbookSubcategory']['name'];
				$response['category_english_name'] = $textbook['TextbookSubcategory']['english_name'];
				$order = $this->listOrder($textbookParam, 2, null);
			}
			$response['title'] = $order.$textbook['Textbook']['name'];
			$response['category_id'] = (int)$textbook['TextbookCategory']['id'];
			$response['url'] = $this->parseTBURL(array(
				'class' => $textbook['Textbook']['html_directory'],
				'chapter' => $textbook['Textbook']['chapter_id'],
				'main_html_directory' => $textbook['Textbook']['html_directory'],
				'main_chapter' => $textbook['Textbook']['chapter_id'],
				'alt_html_directory' => $textbook['Textbook']['alt_html_directory'],
				'alt_chapter' => $textbook['Textbook']['alt_html_directory'],
				'textbook_category_type' => $textbook['TextbookCategory']['textbook_category_type'],
				'user_id' => $user,
				'licenser' => $textbook['Textbook']['licenser']
			));
			$response['last_opened_at'] = isset($lastViewed[$connectID]) ? $lastViewed[$connectID]: null;
			$response['connect_id'] = (int)$connectID;
			$response['lesson_text_id'] = (int)$textbook['Textbook']['id'];
			$response['chapter_id'] = (int)$textbook['Textbook']['chapter_id'];
			$response['textbook_type'] = (int)$textbook['TextbookCategory']['type_id'];
			$response['textbook_category_type'] = (int)$textbook['TextbookCategory']['textbook_category_type'];
			$response['display_flag'] = $this->getDisplayFlag($textbook['Textbook']['id'], $teacher, $textbookList);
		} else {
			$response = null;
		}
		return $response;
	}
	
	/**
	* getTextbookList, get the textbooks
	* @param int $userID -> user's ID, gets the allowed textbook for the user
	* @param int $teacherID -> teacher's ID, gets the allowed textbook for teacher
	* @param int $lessonType -> lesson type, 1: lesson_now, 2: reservation
	*/
	private function getTextbookList($userID = null, $teacherID = null, $lessonType = null){
		# get textbooks for 'lesson_now' or 'reservation'
		# get lesson type
		$flag = ($lessonType) ? $this->lessonType[$lessonType] : null;
		
		# courses
		$getCourseArr = array(
			'teacher_id' => $teacherID,
			'env_flag' => $flag,
			'user_id' => $userID,
			'textbook_type' => 1,
			'arrange_data' => 'tree'
		);
		$textbooksCourse = $this->Textbook->getTextbooks($getCourseArr);
		$textbooksCou = $this->arrangeTextbooks(1, $textbooksCourse['res_data'], $userID);
		# category
		$getCategoryArr = array(
			'teacher_id' => $teacherID,
			'env_flag' => $flag,
			'user_id' => $userID,
			'textbook_type' => 2,
			'arrange_data' => 'tree'
		);
		$textbooksCategory = $this->Textbook->getTextbooks($getCategoryArr);
		$categoriesArr = $categoriesArrTextbook = array();
		// filter textbook category
		foreach ($textbooksCategory['res_data'] as $key){
			if (!in_array($key['TextbookCategory']['id'], $categoriesArr)){
				$categoriesArr[] = $key['TextbookCategory']['id'];
				$categoriesArrTextbook[] = $key;
			}
		}
		$textbooksCat = $this->arrangeTextbooks(2, $categoriesArrTextbook, $userID);
		return array(
			'course' => $textbooksCou,
			'category' => $textbooksCat
		);
	}

	/**
	* arrangeTextbooks, arrange the array of textbooks
	* @param int $type -> 1: course, 2: category
	* @param array $textbooks -> array of textbooks base on type of textbooks
	*/
	private function arrangeTextbooks($type, $textbooks, $userID){
		if ($type == 1) {
			$textbooksCourse = $textbooks;
			# course
			foreach ($textbooksCourse as $val){
				$textbooksCourseArr['course_title'] = $val['TextbookCategory']['name'];
				$textbooksCourseArr['course_english_title'] = $val['TextbookCategory']['english_name'];
				$categoryId = (int)$val['TextbookCategory']['id'];
				//get full description
				$description = $val['TextbookCategory']['description'];
				// get lesson count
				$matches = null;
				preg_match('#<span[^<>]*>([\d,]+).*?</span>#', $description, $matches); 
				$textbooksCourseArr['lesson_count'] = (isset($matches) && is_numeric($matches[1]) == true) ? $matches[1] : null;
				//get description text only
				$break = explode("<br>", trim($description));
				$split = explode("</span>", $break[0]);

				 if ($matches != null && is_numeric($matches[1])) {
					$textbooksCourseArr['description'] = isset($split[2]) ? strip_tags(trim($split[2])) : null;
				 } else {
					$textbooksCourseArr['description'] = isset($break[0]) ? strip_tags(trim($break[0])) : null;
				}

				// get description url 
				$match = null;
				preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $description, $match);	
				$url = explode("user", $match[2][0]);
				$textbooksCourseArr['description_url'] = (isset($match) && $match[2] != null) ? FULL_BASE_URL .'/user/mobapp'.$url[1] : null;

				// get description url title
				if ( $textbooksCourseArr['description_url'] == null ) {

					$textbooksCourseArr['description_url_title'] = null;
				} else {
					preg_match('/<a[^>]*>(.*?)<\/a>/i', $break[1], $matches);
					$textbooksCourseArr['description_url_title'] = (isset($matches[1])) ? strip_tags(trim($matches[1].'について')) : null;
				}

				$textbooksCourseArr['image_big'] = isset($val['TextbookCategory']['image_big_url']) ? $val['TextbookCategory']['image_big_url'] : null;
				$textbooksCourseArr['level_id'] = ($this->categoryLevels($categoryId) != null) ? $this->categoryLevels($categoryId) : null;
				$textbooksCourseArr['display_flag'] = (bool) $val['TextbookCategory']['display_flag'];
				$courseDisplayFlag = $textbooksCourseArr['display_flag'];
				$textbookCategoryType = (int)$val['TextbookCategory']['textbook_category_type'];
				$textbooksCourseArr['courses'] = array();
				if ($val['TextbookSubcategory']){
					$cur = 0;			
					foreach ($val['TextbookSubcategory'] as $key){
						$cur++;
						if (!is_null($key['name']) && !is_null($key['english_name'])) {
							$course = array();
							$count = 1;			
							$course['course_name'] = $key['name'];
							$course['course_english_name'] = $key['english_name'];
							if ($key['textbooks']){
								$textbooks = array();
								foreach ($key['textbooks'] as $chapter){
									$lastOpened = isset($this->lastViewedBooksArr[$chapter['connect_id']]) ? $this->lastViewedBooksArr[$chapter['connect_id']]: null;
									$displayFlg = isset($chapter['display_flag']) ? $chapter['display_flag'] : false;
									$displayFlg = !$courseDisplayFlag ? false : $displayFlg;
									$order = isset($cur) && !$cur == null ? $cur.'-'.$count.':' : '1'.'-'.$count.':';
									$count++;
									$ttextbooksInfo = array(
										"title" => $order.$chapter['name'],
										"category_id" => $categoryId,
										"url" => $this->parseTBURL(array(
											'class' => $chapter['html_directory'],
											'chapter' => $chapter['chapter_id'],
											'main_html_directory' => $chapter['ori_html_directory'],
											'main_chapter' => $chapter['ori_chapter_id'],
											'alt_html_directory' => $chapter['alt_html_directory'],
											'alt_chapter' => $chapter['alt_chapter_id'],
											'textbook_category_type' => $textbookCategoryType,
											'user_id' => $userID,
											'licenser' => $chapter['licenser']
										)),
										"last_opened_at"  => $lastOpened,
										"connect_id"  => (int)$chapter['connect_id'],
										"lesson_text_id"  => (int)$chapter['id'],
										"chapter_id"  => (int)$chapter['chapter_id'],
										"textbook_type"  => 1,
										"textbook_category_type"  => $textbookCategoryType,
										"display_flag" => (bool) $displayFlg
									);
									$textbooks[] = $ttextbooksInfo;
								
								}
								$course['textbooks'] = $textbooks;
							}
							$textbooksCourseArr['courses'][] =  $course;
						}
					}
				}
				$coursesDetailArray[] = $textbooksCourseArr;
			}
			return $coursesDetailArray;
		} else if ($type == 2){
			$categories = $textbooks;
			# category
			foreach ($categories as $val){
				$textbookCatArr['category_title'] = $val['TextbookCategory']['name'];
				$textbookCatArr['category_english_title'] = $val['TextbookCategory']['english_name'];
				$categoryId = (int)$val['TextbookCategory']['id'];
				//get full description
				$description = $val['TextbookCategory']['description'];
				// get lesson count
				$matches = null;
				preg_match('#<span[^<>]*>([\d,]+).*?</span>#', $description, $matches); 
				$textbookCatArr['lesson_count'] = (isset($matches) && is_numeric($matches[1]) == true) ? $matches[1] : null;
				//get description text only
				$break = explode("<br>", trim($description));
				$split = explode("</span>", $break[0]);
				if ($matches != null && is_numeric($matches[1])) {
					$textbookCatArr['description'] = isset($split[2]) ? strip_tags(trim($split[2])) : null;
				} else {
					$textbookCatArr['description'] = isset($break[0]) ? strip_tags(trim($break[0])) : null;
				}
				
				// get description url 
				$match = null;
				preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $description, $match);	
				$url = explode("user", $match[2][0]);
				$textbookCatArr['description_url'] = (isset($match) && $match[2] != null) ?  FULL_BASE_URL .'/user/mobapp'.$url[1] : null;

				// get description url title
				if ( $textbookCatArr['description_url'] == null ) {

					$textbookCatArr['description_url_title'] = null;
				} else {
					preg_match('/<a[^>]*>(.*?)<\/a>/i', $break[1], $matches);
					$textbookCatArr['description_url_title'] = (isset($matches[1])) ? strip_tags(trim($matches[1].'について')) : null;
				}

				$textbookCatArr['image_big'] = isset($val['TextbookCategory']['image_big_url']) ? $val['TextbookCategory']['image_big_url'] : null;
				$textbookCatArr['level_id'] = ($this->categoryLevels($categoryId) != null) ? $this->categoryLevels($categoryId) : null;
				$textbookCatArr['display_flag'] = (bool) $val['TextbookCategory']['display_flag'];
				$courseDisplayFlag = $textbookCatArr['display_flag'];
				$textbookCategoryType = (int)$val['TextbookCategory']['textbook_category_type'];
				$textbookCatArr['categories'] = array();
				if ($val['TextbookSubcategory']){
					foreach ($val['TextbookSubcategory'] as $key){
						if (!is_null($key['name']) && !is_null($key['english_name'])) {
							$cat = array();
							$ccatTextbook = array();
							$cat['category_name'] = $key['name'];
							$cat['category_english_name'] = $key['english_name'];
							$count = 1;
							if ($key['textbooks']){
								foreach ($key['textbooks'] as $chapter){
									$lastOpened = isset($this->lastViewedBooksArr[$chapter['connect_id']]) ? $this->lastViewedBooksArr[$chapter['connect_id']]: null;
									$displayFlg = isset($chapter['display_flag']) ? $chapter['display_flag'] : false;
									$displayFlg = !$courseDisplayFlag ? false : $displayFlg;
									$order = isset($count) && !$count == null ? $count.':': null; 
									$count++;
									$ttextbooksInfo = array(
										"title" => $order.$chapter['name'],
										"category_id" => $categoryId,
										"url" => $this->parseTBURL(array(
											'class' => $chapter['html_directory'],
											'chapter' => $chapter['chapter_id'],
											'main_html_directory' => $chapter['ori_html_directory'],
											'main_chapter' => $chapter['ori_chapter_id'],
											'alt_html_directory' => $chapter['alt_html_directory'],
											'alt_chapter' => $chapter['alt_chapter_id'],
											'textbook_category_type' => $textbookCategoryType,
											'user_id' => $userID,
											'licenser' => $chapter['licenser']
										)),
										"last_opened_at" => $lastOpened,
										"connect_id"  => (int)$chapter['connect_id'],
										"lesson_text_id"  => (int)$chapter['id'],
										"chapter_id"  => (int)$chapter['chapter_id'],
										"textbook_type"  => 2,
										"textbook_category_type"  => $textbookCategoryType,
										"display_flag" => (bool) $displayFlg
									);
									$ccatTextbook[] = $ttextbooksInfo;
								}
								$cat['textbooks'] = $ccatTextbook;
							}
							$textbookCatArr['categories'][] = $cat;
						}
					}
				}
				$categoryTextbooks[] = $textbookCatArr;
			}

			return $categoryTextbooks;
		}
		return array();
	}
	
	/**
	* getLastViewedTextbook, get's the last viewed textbook of the user
	* it will depend based on the teacher and lesson_type [lesson or reservation]
	* @param int $userID, user's id to authenticate
	* @param int $teacherID, teacher's id:326
	* @param int $flag -> lesson_type [1 : lesson , 2:reservation]
	* @return array $textbookArr
	*/
	private function getLastViewedTextbook($userID, $teacherID = null, $textbookList = array()){
		if (empty($userID)) { return null; }
		$data = array(
			'user_id' => $userID,
			'type' => 'all',
			'excludeReserve' => true,
			'includePreset' => false
		);

		$lastViewed = $this->getLatestLastViewedTextbook($userID, $teacherID, $textbookList);
		if ($lastViewed) {
			return $lastViewed;
		}
		// check last viewed textbook or preset
		$lnParams = array(
			"select_method" => "first",
			"env_flag" => "lesson_now",
			"user_id" => $userID,
			"teacher_id" => $teacherID
		);
		$getLNTextbookData = $this->Textbook->getTextbooks($lnParams);
		$textbookInfo = $getLNTextbookData['res_data'];

		$textbookArr = null;
		$textbookParam = array(
			"category_id" => $textbookInfo['TextbookCategory']['id'],
			"indexCounterDisplay" => $textbookInfo['TextbookCategory']['index_counter_display'],
			"subcategory_id" =>  $textbookInfo['TextbookSubcategory']['id'],
			"connect_id" => $textbookInfo['TextbookConnect']['id']
		);
		// course
		if ($textbookInfo['TextbookCategory']['type_id'] == 1){
			$textbookArr['type'] = "course";
			$textbookArr['course_title'] = $textbookInfo['TextbookCategory']['name'];
			$textbookArr['course_english_title'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['course_name'] = $textbookInfo['TextbookSubcategory']['name'];
			$textbookArr['course_english_name'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$order = $this->listOrder($textbookParam, 1);
		} else {
			// category
			$textbookArr['type'] = "category";
			$textbookArr['category_title'] = $textbookInfo['TextbookCategory']['name'];
			$textbookArr['category_english_title'] = $textbookInfo['TextbookCategory']['english_name'];
			$textbookArr['category_name'] = $textbookInfo['TextbookSubcategory']['name'];
			$textbookArr['category_english_name'] = $textbookInfo['TextbookSubcategory']['english_name'];
			$order = $this->listOrder($textbookParam, 2, null);
		}
	
		$textbookArr['title'] = $order.$textbookInfo['Textbook']['name'];
		$textbookArr['category_id'] = (int)$textbookInfo['TextbookCategory']['id'];
		$textbookArr["url"] = $this->parseTBURL(array(
			'class' => $textbookInfo['Textbook']['html_directory'],
			'chapter' => $textbookInfo['Textbook']['chapter_id'],
			'main_html_directory' => $textbookInfo['Textbook']['ori_html_directory'],
			'main_chapter' => $textbookInfo['Textbook']['ori_chapter_id'],
			'alt_html_directory' => $textbookInfo['Textbook']['alt_html_directory'],
			'alt_chapter' => $textbookInfo['Textbook']['alt_chapter_id'],
			'textbook_category_type' => $textbookInfo['TextbookCategory']['textbook_category_type'],
			'user_id' => $userID,
			'licenser' => $textbookInfo['Textbook']['licenser']
		));
		$textbookArr["last_opened_at"]= isset($this->lastViewedBooksArr[$textbookInfo['TextbookConnect']['id']]) ? $this->lastViewedBooksArr[$textbookInfo['TextbookConnect']['id']]: null;
		$textbookArr["connect_id"] = (int)$textbookInfo['TextbookConnect']['id'];
		$textbookArr["lesson_text_id"] = (int)$textbookInfo['Textbook']['id'];
		$textbookArr["chapter_id"] = (int)$textbookInfo['Textbook']['chapter_id'];
		$textbookArr["textbook_type"] = (int)$textbookInfo['TextbookCategory']['type_id'];
		$textbookArr['textbook_category_type'] = (int)$textbookInfo['TextbookCategory']['textbook_category_type'];
		$textbookArr["display_flag"] = $this->getDisplayFlag($textbookInfo['Textbook']['id'], $teacherID, $textbookList);
		return $textbookArr;
	}

	private function getLatestLastViewedTextbook($userID, $teacher = null, $textbookList = array()){
		if (empty($userID)) { return null; }
		$response = array();
		$lastViewedTB = $this->UsersLastViewedTextbook->find('first', array(
			'fields' => array(
				'UsersLastViewedTextbook.connect_id',
				'UsersLastViewedTextbook.last_viewed_date'
			),
			'conditions' => array(
				'UsersLastViewedTextbook.user_id' => $userID,
				'UsersLastViewedTextbook.preset' => 0
			),
			'order' => 'UsersLastViewedTextbook.last_viewed_date DESC'
		));
		if ($lastViewedTB) {
			$lastViewedTB = $lastViewedTB['UsersLastViewedTextbook'];
			$connectId = $lastViewedTB['connect_id'];
			$textbook = $this->TextbookConnect->find('first', array(
				'joins' => array(
					array(
						'table' => 'textbook_categories',
						'alias' => 'TextbookCategory',
						'type' => 'INNER',
						'conditions' => 'TextbookCategory.id = TextbookConnect.category_id'
					),
					array(
						'table' => 'textbook_subcategories',
						'alias' => 'TextbookSubcategory',
						'type' => 'INNER',
						'conditions' => 'TextbookSubcategory.id = TextbookConnect.subcategory_id'
					),
					array(
						'table' => 'textbooks',
						'alias' => 'Textbook',
						'type' => 'INNER',
						'conditions' => 'Textbook.id = TextbookConnect.textbook_id'
					)
				),
				'fields' => array(
					'TextbookConnect.id',
					'TextbookCategory.type_id',
					'TextbookCategory.id',
					'TextbookCategory.name',
					'TextbookCategory.english_name',
					'TextbookCategory.textbook_category_type',
					'TextbookSubcategory.id',
					'TextbookSubcategory.name',
					'TextbookSubcategory.english_name',
					'Textbook.id',
					'Textbook.chapter_id',
					'Textbook.alt_chapter_id',
					'Textbook.name',
					'Textbook.status',
					'Textbook.html_directory',
					'Textbook.alt_html_directory',
					'Textbook.licenser'
				),
				'conditions' => array(
					'TextbookConnect.id' => $connectId
				)
			));
			$textbookParam = array(
				"category_id" => $textbook['TextbookCategory']['id'],
				"indexCounterDisplay" => $textbook['TextbookCategory']['index_counter_display'],
				"subcategory_id" =>  $textbook['TextbookSubcategory']['id'],
				"connect_id" => $textbook['TextbookConnect']['id']
			);
			if ($textbook['TextbookCategory']['type_id'] == 1){
				$response['type'] = 'course';
				$response['course_title'] = $textbook['TextbookCategory']['name'];
				$response['course_english_title'] = $textbook['TextbookCategory']['english_name'];
				$response['course_name'] = $textbook['TextbookSubcategory']['name'];
				$response['course_english_name'] = $textbook['TextbookSubcategory']['english_name'];
				$order = $this->listOrder($textbookParam, 1);

			} else {
				$response['type'] = 'category';
				$response['category_title'] = $textbook['TextbookCategory']['name'];
				$response['category_english_title'] = $textbook['TextbookCategory']['english_name'];
				$response['category_name'] = $textbook['TextbookSubcategory']['name'];
				$response['category_english_name'] = $textbook['TextbookSubcategory']['english_name'];
				$order = $this->listOrder($textbookParam, 2, null);
			}
			$response['title'] = $order.$textbook['Textbook']['name'];
			$response['category_id'] = (int)$textbook['TextbookCategory']['id'];
			$response['url'] = $this->parseTBURL(array(
				'class' => $textbook['Textbook']['html_directory'],
				'chapter' => $textbook['Textbook']['chapter_id'],
				'main_html_directory' => $textbook['Textbook']['html_directory'],
				'main_chapter' => $textbook['Textbook']['chapter_id'],
				'alt_html_directory' => $textbook['Textbook']['alt_html_directory'],
				'alt_chapter' => $textbook['Textbook']['alt_chapter_id'],
				'textbook_category_type' => $textbook['TextbookCategory']['textbook_category_type'],
				'user_id' => $userID,
				'licenser' => $textbook['Textbook']['licenser']
			));
			$response['last_opened_at'] = ($lastViewedTB['last_viewed_date']) ? $lastViewedTB['last_viewed_date'] : null;
			$response['connect_id'] = (int)$connectId;
			$response['lesson_text_id'] = (int)$textbook['Textbook']['id'];
			$response['chapter_id'] = (int)$textbook['Textbook']['chapter_id'];
			$response['textbook_type'] = (int)$textbook['TextbookCategory']['type_id'];
			$response['textbook_category_type'] = (int)$textbook['TextbookCategory']['textbook_category_type'];
			$response['display_flag'] = $this->getDisplayFlag($textbook['Textbook']['id'], $teacher, $textbookList);
		}
		return $response;
	}
	
	/**
	 * parse url for textbook
	 */
	private function parseTBURL($tbData = array()){
		// set environment flags
		$tbData['env_flag'] = isset($this->request->data['lesson_type']) ? $this->request->data['lesson_type'] : NULL;
		
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
		$mainURL .= '&user_id=' . $this->lessonUserID;
		$mainURL .= '&licenser=' . $tbData['licenser'];

		//add language parameter
		if ($this->language && $this->language != 'ja') {
      		$mainURL .= '&la=' . $this->language;
    	}
		
		// return main url
		return $mainURL;
	}

	/**
	 * get category Levels
	 * @param int $id, textbook_category.id 
	 */
	private function categoryLevels($id) {
	
	$level = $this->TextbookCategoryLevel->find('all', 
		array(
			'conditions' => array( 
			'TextbookCategoryLevel.category_id' => $id,
			'TextbookCategoryLevel.subcategory_id ' => null,
		),
			'fields' => array('TextbookCategoryLevel.level_id','TextbookCategoryLevel.category_id'),		
	));		

	$arr = array();

	foreach($level as $key => $item) {
		$items = $item['TextbookCategoryLevel']; 
		$arr[$items['category_id']][$key] = $item;
	}

 	$levels = array();
 	$catLevels = array();

 	if ($arr != null && $id != null ) {
 		foreach ($arr[$id] as $value) {
			$levels[]= (int)$value['TextbookCategoryLevel']['level_id'];
		}
		$catLevels = $levels;
 	} else {
 		$catLevels = null;
 	}
 	return $catLevels; 
	}

	/**
	 * get number order for list
	 * @param array $data [connect_id, textbookcategory_id, subcategory_id],
	 *  int $type [1 = course, 2 = category]
	 */
	private function listOrder($data = array(), $type) { 
		$order = null;
		$data['platform'] = "api";
		if((isset($data) && $data != null) && (isset($type) && $type != null)) {
			$counter = $this->Textbook->findIndexPosition($data);
			$badge = $this->Textbook->subCatBadgeIndex($data);
			if ($type == 1) {
				if ( isset($data["indexCounterDisplay"]) && $data["indexCounterDisplay"] != 2 ) { // 0(ASC) or 1(DESC)
					// Desc
					$order = (isset($badge) && isset($counter) && (!$badge == null && !$counter == null))  ? $badge.'-'. $counter.':': '1'.'-'. $counter.':'; 	
				} else {
					// No display
					$order = null;
				}
				
			} else {
				if ( isset($data["indexCounterDisplay"]) && $data["indexCounterDisplay"] != 2 ) { // 0(ASC) or 1(DESC)
					// Desc
					$order = isset($counter) && !$counter == null ? $counter.':': null; 
				} else {
					// No display
					$order = null;
				}
			}
		}
		
		return $order;
	}
	private function insertUserIdUrl( $param = array() ){
		$url = null;
		if( (isset($param["user_id"]) && !empty($param["user_id"])) && (isset($param["old_url"]) && !empty($param["old_url"])) ){
			
			$userId = $param["user_id"];
			$oldUrl = $param["old_url"];
			$pattern = 'user_id='.$userId.'&';
			$repPattern = 'user_id=&';
			
			if (strpos($oldUrl, $repPattern) !== false) {
				$url =str_replace($repPattern,$pattern,$oldUrl);
			} else {
				$url = $oldUrl;
			}
		}
		
		return $url;
	}

	/**
	*getImageUrl, Get specific image URL for textbook	
	*@return array $getImageUrl
	* 1- 初心者コース(before lesson) for course
	* 45 - 初めてのレッスン (first lesson) for category
	*/
	private function getImageUrl() {
		$this->TextbookCategory->openDBReplica();
		$getImageUrl = $this->TextbookCategory->find('list', array(
			'fields' => array(
				'TextbookCategory.id',
				'TextbookCategory.image_big_url'
			),
			'conditions' => array(
				'TextbookCategory.id' => array(1,45)
			)
		));
		$this->TextbookCategory->closeDBReplica();
		return (!empty($getImageUrl)) ? $getImageUrl : null;
	}

	// NC-5160 : get index numbering
	private function getIndexNum($params=array()) {
		$result = null;
		$strSep = " : ";
		if ( isset($params["title"]) && strlen($params["title"]) ) {
			$title = $params["title"];
			if (strpos($title, $strSep) !== false) {
				$strArr = explode($strSep,$title);
				if ( count($strArr) > 1 ) {
					$result = $strArr[0].$strSep;
				}
			}
		}
		return $result;
	}
}
