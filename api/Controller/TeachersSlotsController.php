<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersSlotsController extends AppController {

	public $uses = array(
		'User',
		'Teacher',
		'LessonSchedule'
	);

	private $api = null;

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
		$this->api = new ApiCommonController();
	}

	public function index() {
		$validate = $this->api;
		$this->autoRender = false;
		$json = array();
		if ($this->request->is('post')) {
			$reqData = @json_decode($this->request->input(), true);

			if (isset($reqData['users_api_token']) && trim($reqData['users_api_token']) == "") {
				$json['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
				$json['error']['message'] = __('users_api_token can not be empty');
				return json_encode($json);
			}
			$user = (!empty($reqData['users_api_token'])) ? $validate->validateToken($reqData['users_api_token']) : null;
			if (!empty($reqData['users_api_token']) && empty($user)) {
				$json['error']['id'] = Configure::read('error.invalid_api_token');
				$json['error']['message'] = $validate->error;
				return json_encode($json);

			} else if (!$validate->validateTeachersId($reqData['teachers_id'])) {
				$json['error']['id'] = Configure::read('error.invalid_teachers_id');
				$json['error']['message'] = $validate->error;
				return json_encode($json);
			}

			if (!empty($user) && $validate->checkBlocked($reqData['teachers_id'], $user['id'])) {
				$json['error']['id'] = Configure::read('error.missing_teacher');
				$json['error']['message'] = __($validate->missing_teacher);
				return json_encode($json);
			}

			$schedules = null;
			$teacherData = $this->Teacher->find('first', array(
				'fields' => array('Teacher.id', 'Teacher.reservation_hide_flg'),
				'conditions' => array('Teacher.id' => $reqData['teachers_id']),
				'recursive' => -1
			));

			$teacher = new TeacherTable($teacherData['Teacher']);
			$json = $this->teacherSchedules((!empty($user['id']) ? $user['id'] : null), $teacher->id, $teacher->reservation_hide_flg);

		} else {
			$json['error']['id'] = Configure::read('error.invalid_request');
			$json['error']['message'] = __('Invalid request');
		}

		return json_encode($json);
	}


	/**
	 * Get teacher schedules
	 * @param int $userId
	 * @param int $teacherId
	 * @param int $teacherReservationHide
	 * @return array $schedules
	 */
	private function teacherSchedules($userId, $teacherId = null, $teacherReservationHide = null) {
		$start = 0;
		$limitDays = 6;
		$start_date = date('Ymd', strtotime("+" . $start . " days", time()));
		$end_date = date('Ymd', strtotime("+" . ($limitDays + $start) . " days", time()));

		$schedules = array(
			"show_caution_flg" => 0,
			"start_date" => $this->formatDate($start_date, false),
			"states" => null
		);

		$slots = @$this->LessonSchedule->getTeacherSlot(
			$userId,
			$teacherId,
			$start_date,
			$end_date
		);

		// no schedules
		if (!empty($slots)) {
			// get hide schedules
			$hideDates = $this->api->getHideDates($teacherId);

			//get disabled schedule
			$disabledSchedule = $this->LessonSchedule->getDisabledDays(array('userId' => $userId, 'teacherId' => $teacherId));

			foreach($slots as $key => $val) {
				$date = $this->formatDate($key);
				$slotTime = strtotime($date);
				$secondsDiff = time() - $slotTime;
				// check if slots are not hide and teacher reservations is not hide.
				if (!in_array($date.':00', $hideDates) && !in_array($date, $hideDates) && !in_array(date('Y-m-d', strtotime($date)), $hideDates) && !$teacherReservationHide) {
					if ($val['state'] == 7) {
						//check if disabled or not
						if ($disabledSchedule['success']) {

							// - if all are disabled
							if ($disabledSchedule['disableAll']) { 
								// - state 5 : Available but can't reserve because of reservation limit
								$schedules['states'][$date] = array('state' => 5);

							// - check if limit for today is reach
							} elseif ($disabledSchedule['disabledDays'] && in_array(date('Y-m-d', strtotime($date)), $disabledSchedule['disabledDays'])) { 

								// - state 5 : Available but can't reserve because of reservation limit
								$schedules['states'][$date] = array('state' => 5);
							} else {
								$schedules['states'][$date] = $slots[$key];
							}
						} else {
							$schedules['states'][$date] = $slots[$key];
						}
					} else {
						$schedules['states'][$date] = $slots[$key];
					}	
				}
			}
		}

		$now = time();
		$nowDate = date('Y-m-d', $now);
		$nowHour = date('H', $now);
		$nowMinute = date('i', $now);
		
		// - generate passed schedules
		for ($hour = 0; $hour < 24; $hour++) {
			if ($hour <= $nowHour) {
				if ($hour == $nowHour && $nowMinute < 20){
					$slotDateTime = $nowDate.' '.str_pad($hour, 2, "0", STR_PAD_LEFT).':00';
					$schedules['states'][$slotDateTime] = array('state' => 6);
				} else {
					$slotDateTime = $nowDate.' '.str_pad($hour, 2, "0", STR_PAD_LEFT).':00';
					$schedules['states'][$slotDateTime] = array('state' => 6);
					$slotDateTime = $nowDate.' '.str_pad($hour, 2, "0", STR_PAD_LEFT).':30';
					$schedules['states'][$slotDateTime] = array('state' => 6);
				}
				if ($nowMinute >= 50 && $hour == $nowHour) {
					if ($hour == 23) {
						$nowDate = date('Y-m-d', strtotime($nowDate . " +1 days"));
						$slotHour = 0;
					} else {
						$slotHour = $hour + 1;
					}
					$slotDateTime = $nowDate.' '.str_pad($slotHour, 2, "0", STR_PAD_LEFT).':00';
					$schedules['states'][$slotDateTime] = array('state' => 6);
				}
			}
		}

		ksort($schedules['states']);

		$schedules['show_caution_flg'] = ($this->LessonSchedule->reservationCautionFlag(array('userId' => $userId))) ? 1 : 0;

		return $schedules;
	}

	private function formatDate($stringDate, $includeTime = true) {
		if (!$includeTime) {
			return substr($stringDate, 0, 4).'-'.substr($stringDate, 4, 2).'-'.substr($stringDate, 6, 2);
		}
		return substr($stringDate, 0, 4).'-'.substr($stringDate, 4, 2).'-'.substr($stringDate, 6, 2).' '.substr($stringDate, 8,2).':'.substr($stringDate, 10,2);
	}
}
