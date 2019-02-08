<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class CounselorsSlotsController extends AppController{

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
		$this->autoRender = false;
		$req = json_decode($this->request->input(), true);
		
		if (isset($req['users_api_token']) && trim($req['users_api_token']) <> '') {
			$user = $this->api->validateToken($req['users_api_token']);
			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');
				return json_encode($response);
			}
			return json_encode($this->counselorSchedules($user));
		} elseif ((isset($req['users_api_token']) && trim($req['users_api_token']) == '')) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		}
	}

	private function counselorSchedules($user) {
		$start_date = date('Y-m-d');

		$slots = array(
			'show_caution_flg' => 0,
			'start_date' => $start_date,
			'states' => NULL
		);

		// - if reservation exceeds cancellation already
		$slots['show_caution_flg'] = ($this->LessonSchedule->reservationCautionFlag(array('userId' => $user['id']))) ? 1 : 0;

		$counselorIds = $this->Teacher->getCounselorId();
		$result = $this->LessonSchedule->getCounselorSlots(array(
			'counselor_ids' => $counselorIds,
			'user_id' => $user['id'],
			'start_day' => $start_date
		));
		$openSlots = array();
		$disabledSchedule = array();
		$schedules = array();
		if (!$result['error']) {
			// - get disabled dates
			$disabledSchedule = $this->LessonSchedule->getDisabledDays(array('userId' => $user['id'], 'teacherId' => $counselorIds));

			// - arrange open_slot
			foreach ($result['openSlotData'] as $slot) {

				$openSlots[$slot['ShiftWorkOn']['lesson_time']] = $slot[0]['shiftCount'];

				// - check if disabled or not
				if ($disabledSchedule['success']) {
					$formattedLessonTime = date('Y-m-d H:i', strtotime($slot['ShiftWorkOn']['lesson_time']));

					// - if all are disabled
					if ($disabledSchedule['disableAll']) { 
						// - state 5 : Available but can't reserve because of reservation limit
						$schedules[$formattedLessonTime] = array(
							'state' => 5,
							'open_counselor' => intval($slot[0]['shiftCount'])
						);

					// - check if limit for today is reach
					} elseif (!empty($disabledSchedule['disabledDays']) && in_array(date('Y-m-d', strtotime($slot['ShiftWorkOn']['lesson_time'])), $disabledSchedule['disabledDays'])) {

						// - state 5 : Available but can't reserve because of reservation limit
						$schedules[$formattedLessonTime] = array(
							'state' => 5,
							'open_counselor' => intval($slot[0]['shiftCount'])
						);
					} else {
						// - state 7 : Available
						$schedules[$formattedLessonTime] = array(
							'state' => 7,
							'open_counselor' => intval($slot[0]['shiftCount'])
						);
					}
				} else {
					// - state 7 : Available
					$schedules[$formattedLessonTime] = array(
						'state' => 7,
						'open_counselor' => intval($slot[0]['shiftCount'])
					);
				}
            }	

			$counselorsIds = !empty($counselorIds) ? array_values($counselorIds) : array();
			// - counselor reservations 
			foreach ($result['reservationData'] as $reservation) {
				$formattedLessonTime = date('Y-m-d H:i', strtotime($reservation['LessonSchedule']['lesson_time']));

				// - if my reservation
				if ($reservation['LessonSchedule']['user_id'] == $user['id']) {
					if (!in_array($reservation['LessonSchedule']['teacher_id'], $counselorsIds) && !empty($openSlots[$reservation['LessonSchedule']['lesson_time']])) {
						$schedules[$formattedLessonTime] = array(
							'state' => 2
						);
					} elseif (!in_array($reservation['LessonSchedule']['teacher_id'], $counselorsIds) && empty($openSlots[$reservation['LessonSchedule']['lesson_time']])) {
						$schedules[$formattedLessonTime] = array(
							'state' => 3
						);
					} else {
						$schedules[$formattedLessonTime] = array(
							'state' => 1	
						);
					}

				// - if not my reservations
				} elseif ((isset($schedules[$formattedLessonTime]) && $schedules[$formattedLessonTime]['state'] != 1)) {
					$openSlots[$reservation['LessonSchedule']['lesson_time']] = $openSlots[$reservation['LessonSchedule']['lesson_time']] - 1;

					// - check disabled dates 
					if (
						(isset($disabledSchedule['disableAll']) && $disabledSchedule['disableAll']) 
						|| (!empty($disabledSchedule['disabledDays']) && in_array(date('Y-m-d', strtotime($reservation['LessonSchedule']['lesson_time'])), $disabledSchedule['disabledDays']))
						&& (!empty($openSlots[$reservation['LessonSchedule']['lesson_time']]) && $schedules[$formattedLessonTime]['state'] >= 5)
					) {
						// - state 5 : Disabled dates
						$schedules[$formattedLessonTime] = array(
							'state' => 5,
							'open_counselor' => intval($openSlots[$reservation['LessonSchedule']['lesson_time']])
						);
					} elseif (!empty($openSlots[$reservation['LessonSchedule']['lesson_time']]) && $schedules[$formattedLessonTime]['state'] >= 4) {
						// - state 7 : Available
						$schedules[$formattedLessonTime] = array(
							'state' => 7,
							'open_counselor' => intval($openSlots[$reservation['LessonSchedule']['lesson_time']])
						);
					} elseif (isset($openSlots[$reservation['LessonSchedule']['lesson_time']]) && $openSlots[$reservation['LessonSchedule']['lesson_time']] <= 0 && $schedules[$formattedLessonTime]['state'] >= 4) {
						// - state 4 : reserve by other user
						$schedules[$formattedLessonTime] = array(
							'state' => 4
						);
					}
				}
			}
		}

		// - set variables for passed schedules
		$now = time();
		$nowDate = date('Y-m-d', $now);
		$nowHour = date('H', $now);
		$nowMinute = date('i', $now);

		// - generate passed schedules
		for ($hour = 0; $hour < 24; $hour++) {
			if ($hour <= $nowHour) {
				if ($hour == $nowHour && $nowMinute < 20){
					$slotDateTime = $nowDate.' '.str_pad($hour, 2, "0", STR_PAD_LEFT).':00';
					$schedules[$slotDateTime] = array('state' => 6);
				} else {
					$slotDateTime = $nowDate.' '.str_pad($hour, 2, "0", STR_PAD_LEFT).':00';
					$schedules[$slotDateTime] = array('state' => 6);
					$slotDateTime = $nowDate.' '.str_pad($hour, 2, "0", STR_PAD_LEFT).':30';
					$schedules[$slotDateTime] = array('state' => 6);
				}
				if ($nowMinute >= 50 && $hour == $nowHour) {
					if ($hour == 23) {
						$nowDate = date('Y-m-d', strtotime($nowDate . " +1 days"));
						$slotHour = 0;
					} else {
						$slotHour = $hour + 1;
					}
					$slotDateTime = $nowDate.' '.str_pad($slotHour, 2, "0", STR_PAD_LEFT).':00';
					$schedules[$slotDateTime] = array('state' => 6);
				}
			}
		}
		
		ksort($schedules);
		$slots['states'] = $schedules;

		return $slots;
	}
}