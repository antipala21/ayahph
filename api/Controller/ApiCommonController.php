<?php
App::uses('Controller', 'Controller');
class ApiCommonController extends Controller{
	public $uses = array(
		'User',
		'DeviceToken',
		'LessonSchedule',
		'LessonOnairsLog',
		'ShiftWorkHideDate',
		'Teacher'
	);
	public $error;
	public $missing_teacher = "The teacher id have entered is missing (Error01)";
	/**
	* Validates token if its empty or existing
	* @param String token.
	* @return an error or token details.
	**/
	public function validateToken($token) {
		$user 	= "";
		if (trim($token) == "") {
			$this->error = __('users_api_token is required');
		} else {
			$user = $this->findApiToken($token);
			if ($user == false) {
				$this->error = __('Invalid users_api_token');
			} else if (!is_string($token)) {
				$this->error = __('users_api_token must be string');
			}
		}
		return empty($this->error) ? $user : false;
	}

	/**
	* Validates Teacher if its empty or existing
	* @param Int id
	* @return an error if there is one.
	**/
	public function validateTeachersId($id) {

		if (trim($id) == "") {
			$this->error = __('teachers_id is required');
		} else if (!is_int($id)) {
			$this->error = __('teachers_id must be integer');
		} else if (!$this->findTeachersId($id)) {
			$this->error = __('Invalid teachers_id');
		}
		return empty($this->error)? true : false;
	}


	/**
	* Validate Device type value
	* @param string $id
	* @return boolean
	*/
	public function validateDeviceType($id){
		if (trim($id) == "") {
			$this->error = __('device_type is required');
		} else if (!is_int($id)) {
			$this->error = __('device_type must be integer');
		} else if ($id !== 1 && $id !== 2){
			$this->error = __("device_type must 1 be or 2 value.");
		}
		return empty($this->error)? true : false;
	}

	/**
	* Check if Device Token Exists
	* @param string $api_token
	* @return boolean
	*/
	public function validateDeviceToken($device_token = null){

		if ($device_token == "") {
			return $this->error = __('device_token is required');
		} else {
			$result = $this->DeviceToken->useReplica()->find('first',
				array(
					'fields' => 'DeviceToken.device_token',
					'conditions' => array('device_token' => $device_token)
				)
			);
			if (empty($result)) {
				return $this->error = __('Invalid device_token');
			} else if ($result['DeviceToken']['device_token'] != $device_token) {
				return $this->error = __('Invalid device_token');
			}
		}
		return empty($this->error)? true : false;
	}

	/**
	* Check if Api Token Exists
	* @param string $api_token
	* @return boolean false/array $result['User']
	*/
	public function findApiToken($api_token = null) {
		$this->User->recursive = -1;
		$result = $this->User->useReplica()->find('first', array(
			'conditions' => array('User.api_token' => $api_token),
			'fields' => array(
				'User.api_token',
				'User.id',
				'User.parent_id',
				'User.admin_flg',
				'User.charge_flg ',
				'User.fail_flg ',
				'User.created',
				'User.email',
				'User.gender',
				'User.birthday',
				'User.status',
				'User.nickname',
				'User.image_url',
				'User.hash16',
				'User.campaign_id',
				'User.next_charge_date',
				'User.sms_through_flg',
				'User.sns_topic_arn',
				'User.double_check_flg',
				'User.birthday_show_flg',
				'User.card_company',
				'User.magazine_flg',
				'User.timezone_id',
				'User.timezone_dst_flg',
				'User.reservation_mail_flg',
				'User.reservation_cancel_mail_flg',
				'User.heavy_user_flg', // NC-3966,
				'User.callan_level_check', // NC-3959
				'User.counseling_attended_flg', //NC-4380
				'User.country_code',
				'User.device', // NC-4452
				'User.corporate_id',
				'User.corporate_type',
				'User.native_language2', // NC-4753,NC-4818, NC-4896,  NC-4904, NC-4857
				'User.settlement_currency_id',
				'User.timezone_id',
				'User.monthly_payment',
				'User.gender_show_flg',
				'User.residence_show_flg',
				'User.nationality_show_flg',
				'User.residence_id',
				'User.nationality_id'
			)
		));
		if (!empty($result)) {
			return ($result['User']['api_token'] == $api_token) ? $result['User'] : false;
		} else {
			return $this->error = __('Invalid users_api_token');
			return false;
		}
	}

	/**
	* Check if teacher exists
	* @param int $id
	* @return boolean false/array $result['Teacher']
	*/
	public function findTeachersId($id) {
		$this->loadModel('Teacher');
		$this->Teacher->recursive = -1;
		$result = $this->Teacher->useReplica()->find('first', array(
				'conditions' => array('Teacher.id' => $id),
				'fields' => array('Teacher.id')
			)
		);
		return empty($result) ? false : $result['Teacher'];
	}

  /**
  * Check if user is blocked by teacher
  * @param int $teacher_id,$user_id
  * @return boolean
  */
  public function checkBlocked($teacher_id,$user_id){
  	$blockList = BlockListTable::getBlocks($user_id);
  	return in_array($teacher_id,$blockList) ? true : false;
  }

  /**
  * Check if date correct format
  * @param strign $date
  * @return boolean
  */
	public function validateDate($date = null){
		if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/", $date)) {
  		$this->error = __('Invalid date format');
  	}
  	return empty($this->error)? true : false;
	}

	/**
	* determine teacher status
	* 1 ready for lesson
	*	2
	* 3 - during lesson / break
	* @param array
	* @return int
	*/
	public function teacherStatusColor($params) {
		$lessonOnair = $params['LessonOnair'];
		$teacher = $params['Teacher'];
		$ts = $params['TeacherStatus'];
		$LOA_status = $lessonOnair['status'];
		$LOA_connectflg = $lessonOnair['connect_flg'];
		$statusCheck = array(1, 4);
		$result = 4;
		$lessonType = $lessonOnair['lesson_type'];
		$userId = $params['userId'];

		$lesson_time = $this->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
		$lessonTime = date('Y-m-d H:i:s', $lesson_time);

		if (!empty($LOA_status) && $LOA_connectflg <> 0) {
			if ($LOA_status == 1) {
				$result = 1;
			} else if (
				( ($LOA_status == 2 && $params['nextReservation'] == $teacher->id) || 
				  ($LOA_status == 3 && $lessonType == 2 && $userId == $lessonOnair['user_id'])
				) && 
				date('i') >= date('i', strtotime($lessonTime)) && 
				date('i') <= date('i', strtotime($lessonTime.'+25mins'))
			) {
				$result = 5;
			} else if ($LOA_status == 2 || $LOA_status == 3) {
				$result = 3;
			}
		} else {
			if (!empty($LOA_status) && $LOA_connectflg == '0') {
				$result = 4;
			} elseif (!empty($ts->status) && ($ts->status == 4 && (!in_array($ts->remarks1, $statusCheck) || $ts->remarks2 == "after_lesson_other"))) {
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
	 * Check reservation if belongs to this student
	 * @param array $params
	 * @return boolean
	 */
	public function getReservation($condition) {
		$lesson_time = $this->preperationTime() ? ceil(time() / (30 * 60)) * (30 * 60) : floor(time() / (30 * 60)) * (30 * 60);
		$lesson_time = date('Y-m-d H:i:s',$lesson_time);
		$conditions = $condition;
		$conditions['LessonSchedule.status'] = 1;
		$conditions['LessonSchedule.lesson_time'] = $lesson_time;
		$this->LessonSchedule->virtualFields = array(
			'textbook_category_type' => 'SELECT TBC.textbook_category_type FROM textbook_connects as TC INNER JOIN textbook_categories as TBC ON (TBC.id = TC.category_id) WHERE TC.id = LessonSchedule.connect_id LIMIT 1'
		);
		$schedule = $this->LessonSchedule->find('first',array(
			'fields' => array('LessonSchedule.*'),
			'conditions' => $conditions,
			'recursive' => -1
			)
		);
		return isset($schedule['LessonSchedule']) ? $schedule['LessonSchedule'] : false;
	}

	/**
	 * Check if time is 5 mins/less before new lesson
	 * @return boolean
	 */
	public function preperationTime() {
		$time = ceil(time() / (30 * 60)) * (30 * 60);
		if ($time - time() < 300)return true;
		return false;
	}

	/**
	* Generate User Token
	* @param string $ip users ip
	* @return string token
	*/
	public static function generateAPIToken($ip){
		return md5(time() . ip2long($ip) . uniqid());
	}

	/**
	 * Verify iOS receipt on Apple server
	 * @param string $receipt iOS receipt
	 * @param string $password app shared secret, only used for iOS receipts that contain auto-renewable subscriptions
	 * @param string $base_url the URL for verifying the iOS receipt
	 * @return array
	 */
	public function verifyAppleReceipt($receipt, $password = '', $base_url = 'https://buy.itunes.apple.com/verifyReceipt') {
		$params = json_encode(array(
			'receipt-data' => $receipt,
			'password' => $password
		));
		$response = $this->curlRequestAndGetResponse($base_url,$params);
		$result   = $this->parseVerifyJsonResponse($response);
		//status codes for iOS
		//https://developer.apple.com/jp/documentation/ValidateAppStoreReceipt.pdf
		//https://developer.apple.com/library/ios/releasenotes/General/ValidateAppStoreReceipt/Chapters/ValidateRemotely.html#//apple_ref/doc/uid/TP40010573-CH104-SW1
		if ($result['status'] === 0) {
			return array('base_url' => $base_url, 'response' => $response, 'result' => $result);
		} elseif ($result['status'] === 21004) {
			return $this->verifyAppleReceipt($receipt, Configure::read('ios_shared_secret'), $base_url);
		} elseif ($result['status'] === 21007) {
			//テスト環境のレシートを、実稼働環境に送信して検証しようとしたエラー パラメータを変えてサンドボックスに投げる
			return $this->verifyAppleReceipt($receipt, $password, 'https://sandbox.itunes.apple.com/verifyReceipt');
		} else {
			return array('error' => array('id' => Configure::read('error.verify_apple_receipt_error'), 'message' => 'エラーが発生しました。(エラーコード: '.$result['status'].')', 'status' => $result['status']));
		}
	}

	/** リクエストを送信し、結果を返します。*/
	public function curlRequestAndGetResponse($url,$data='',$isJson=true) {

		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($isJson) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_POST, true);
		}
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function parseVerifyJsonResponse($response) {
		$response_keys = array(
			'request_id',
			'status',
			'error_text',
		);
		$msg = json_decode($response, true);

		// 連想配列が期待されるキーを持たない場合は追加する。
		foreach ($response_keys as $key) {
			if (!isset($msg[$key])) { $msg[$key] = ''; }
		}

		return $msg;
	}

	//Get the auth access using the refresh token
	//https://developers.google.com/android-publisher/authorization
	public function getAuthAccess() {
		$params = array(
			'grant_type' => 'refresh_token',
			'client_id' => Configure::read('google_client_id'),
			'client_secret' => Configure::read('google_client_secret'),
			'refresh_token' => Configure::read('google_refresh_token')
		);
		$params = http_build_query($params);
		$response = $this->curlRequestAndGetResponse('https://accounts.google.com/o/oauth2/token',$params,false);
		$authResult = $this->parseVerifyJsonResponse($response);
		return $authResult;
	}

	//Verify Google subscription
	//https://developers.google.com/android-publisher/api-ref/purchases/subscriptions/get
	public function verifyGoogleSubscription($packageName, $productId, $purchaseToken, $accessToken) {
		$url = 'https://www.googleapis.com/androidpublisher/v2';
		$url .= '/applications/' . $packageName;
		$url .= '/purchases/subscriptions/' . $productId;
		$url .= '/tokens/' . $purchaseToken;
		$url .= '?access_token=' . $accessToken;
		$response = $this->curlRequestAndGetResponse($url,'',false);
		$result = $this->parseVerifyJsonResponse($response);
		return array('response' => $response, 'result' => $result);
	}

	//Verify Google purchase
	//https://developers.google.com/android-publisher/api-ref/purchases/products/get
	public function verifyGooglePurchase($packageName, $productId, $purchaseToken, $accessToken) {
		$url = 'https://www.googleapis.com/androidpublisher/v2';
		$url .= '/applications/' . $packageName;
		$url .= '/purchases/products/' . $productId;
		$url .= '/tokens/' . $purchaseToken;
		$url .= '?access_token=' . $accessToken;
		$response = $this->curlRequestAndGetResponse($url,'',false);
		$result = $this->parseVerifyJsonResponse($response);
		return array('response' => $response, 'result' => $result);
	}
	
	/**
	* get last lesson loading, same function from pc -> add 60 seconds after lesson end/ the orange button
	* @param int $userId
	* @param int $teacherId
	* @param int $status -> teacher's lesson status
	* @return int
	**/
	public function getLastLessonLoading($userId='', $teacherId='', $status=4) {
		$result = 0;
		if ($userId && $teacherId) {
			$secondsRemaining = $this->LessonOnairsLog->getLastLessonSecondsRemaining($userId, $teacherId);
			if ($secondsRemaining && $status == 1) {
				$result = 1;
			}
		}
		return (int)$result;
	}


	public function getHideDates($teacherId = null) {
		$hideDates = array();
		if ($teacherId) {
			$start = 0;
			$limitDays = 7;
			$startDate = date('Y-m-d', strtotime("+" . $start . " days", time()));
			$endDate = date('Ymd', strtotime("+" . ($limitDays + $start) . " days", time()));

			$dateList = $this->ShiftWorkHideDate->find('all', array(
				'fields' => array(
					'ShiftWorkHideDate.lesson_time',
					'ShiftWorkHideDate.schedule_type'
				),
				'conditions' => array(
					'ShiftWorkHideDate.teacher_id'     => $teacherId,
					'ShiftWorkHideDate.lesson_time >=' => $startDate,
					'ShiftWorkHideDate.lesson_time <'  => $endDate,
				),
				'order' => ('ShiftWorkHideDate.lesson_time asc'),
			));

			if ($dateList) {
				foreach($dateList as $date) {
					if ($date['ShiftWorkHideDate']['schedule_type'] == 1) {
						$hideDates[] = $date['ShiftWorkHideDate']['lesson_time'];
					} else {
						$hideDates[] = date('Y-m-d', strtotime($date['ShiftWorkHideDate']['lesson_time']));
					}
				}
			}
		}
		return $hideDates;
	}

	public function checkUserAttendedFreeCounseling($params = array()) {
		$userId = $params['userId'];
		$nextChargeDate = $params['nextChargeDate'];
		$chargeDate = date('Y-m-d', strtotime('-1 month'.$nextChargeDate));
		$counselorTeachers = $this->Teacher->getCounselorId();
		return $attendedFlgExist = $this->LessonSchedule->countAttendFreeCounseling(array(
			'userId' => $userId,
			'teacherId' => $counselorTeachers,
			'chargeDate' => $chargeDate
		));
	}
}
