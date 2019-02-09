 <?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class ReservationsCancelController extends AppController {
	public $uses = array(
		'LessonSchedule',
		'LessonScheduleCancel',
		'User',
		'Teacher',
		'UsersPoint'
	);
	public $helpers = array('Html', 'Form');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('cancel'));
	}

	public function cancel(){
		$Validate = new ApiCommonController();
		$this->autoRender = false;
		$json = array();
		if ($this->request->is('post')) {

			$data = @json_decode($this->request->input(), true);

			if (!$data) {
				$json['error']['id'] = Configure::read('error.invalid_request');
				$json['error']['message'] = __('Invalid request');
			} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
				$json['error']['id'] = Configure::read('error.users_api_token_is_required');
				$json['error']['message'] = __('users_api_token is required');
			} else if (!$Validate->validateToken($data['users_api_token'])) {
				$json['error']['id'] = Configure::read('error.invalid_api_token');
				$json['error']['message'] = $Validate->error;
			}  else if (isset($data['api_version']) && is_string($data['api_version'])) {
				$json['error']['id'] = Configure::read('error.api_version_must_be_integer');
				$json['error']['message'] = __('api version must not be string');  
			} else if (isset($data['user_status']) && ($data['user_status'] < 0 || $data['user_status'] > 9)) {
				$json['error']['id'] = Configure::read('error.user_status_invalid');
				$json['error']['message'] = __('user_status is invalid');
			} else {
				$id = $Validate->findApiToken($data['users_api_token']);
				if (isset($data['user_status'])) {
					//check mismatch
					$params = array('status' => $data['user_status'], 'user' => $id);
					$mismatch = UserTable::checkStatusMatch($params);
					if ($mismatch['fail']) {
						return json_encode($mismatch['data']);
					}
				}

				if (!isset($data['reservation_id']) || empty($data['reservation_id'])) {
					$json['error']['id'] = Configure::read('error.reservation_id_is_required');
					$json['error']['message'] = __('reservation_id is required');
					return json_encode($json);
				}

				$lessonSched = $this->LessonSchedule->find('first', array(
					'conditions' => array(
						'user_id' => $id['id'],
						'id' => $data['reservation_id']
					),
					'recursive' => -1
				));
				
				$res = false;
				// limit cancellation for today to 3 only
				$userCancelledReservation = $this->LessonScheduleCancel->countCancelledReservation(array('userId' => $id['id']));
				if ($userCancelledReservation >= 3) {
					$res = 2;
				}

				if (isset($lessonSched['LessonSchedule']) && !$res) {
					$userId = $id['id'];
					$counselorAttendedFlg = $id['counseling_attended_flg'];
					$nextChargeDate = $id['next_charge_date'];
					$teacherId = $lessonSched['LessonSchedule']['teacher_id'];
					$dateAndTime = $lessonSched['LessonSchedule']['lesson_time'];

					$isRefundable = LessonScheduleTable::isRefundable($lessonSched['LessonSchedule']['id']);

					$refundCoin = $this->LessonSchedule->findByUserIdAndTeacherIdAndLessonTime($userId, $teacherId, $dateAndTime);

					$cancelFrom = 'user';
					$res = @$this->LessonSchedule->cancelReserve(array(
						'user_id' => $userId,
						'teacher_id' => $teacherId,
						'reserve_time' => $dateAndTime,
						'cancelFrom' => $cancelFrom
					));

					// NC-4526: set $res = 1 if array $res['LessonScheduleCancel'] exist
					// update status manual
					if (isset($res['LessonScheduleCancel'])) {
						if (!$res['LessonScheduleCancel']['old_data_flg']) {
							$lsc = $res['LessonScheduleCancel'];
							$cancelledTimeDiff = strtotime($lsc['lesson_time']) - strtotime($lsc['cancelled_date']);
							$statusManual = myTools::getReservationCancellationType(array('cancelFrom' => $cancelFrom, 'cancelledTimeDiff' => $cancelledTimeDiff));
							
							$lscModel = $this->LessonScheduleCancel;
							$lscModel->clear();
							$lscModel->read(array('status', 'status_manual'), $lsc['id']);
							$lscModel->set('status', $statusManual);
							$lscModel->set('status_manual', $statusManual);
							$lscModel->validate = array();
							$lscModel->save();
						}
						$res = 1;
					}

					if ($isRefundable) {
						$userAttendedFreeCounseling = $Validate->checkUserAttendedFreeCounseling(array(
						'userId' => $userId,
						'nextChargeDate' => $nextChargeDate
						));
						$this->User->read(array('counseling_attended_flg'), $userId);
						$this->User->validate = false;
						if ($userAttendedFreeCounseling || !$counselorAttendedFlg) {
							$this->User->set(array('counseling_attended_flg' => $counselorAttendedFlg));
						} else {
							$this->User->set(array('counseling_attended_flg' => 0));
						}
						$this->User->save();
					} else {
						$this->User->read(array('counseling_attended_flg'), $userId);
						$this->User->validate = false;
						$this->User->set(array('counseling_attended_flg' => 1));
						$this->User->save();
					}
					//cancel user coupon
					$cancelCoupon = ClassRegistry::init('UserCoupon')->cancel($userId, 1, $lessonSched['LessonSchedule']['id']);
				
					if (isset($lessonSched['LessonSchedule']) && $isRefundable && !$cancelCoupon) {
						# add to the user's existing coin
						$point_params = array(
							'user_id' => $userId,
							'add_point' => $refundCoin['LessonSchedule']['coin'],
							'point_kbn' => 2
						);
						$refunded = $this->UsersPoint->addPoint($point_params);

						# return coin
						$coinReturn = 1;

						# update reservation flg
						if ($refunded) {
							$this->LessonScheduleCancel->updateAll(
								array(
									'LessonScheduleCancel.refund_flg' => 1
								),
								array(
									'LessonScheduleCancel.reservation_id' => $lessonSched['LessonSchedule']['id']
								)
							);
						}
					}
				}

				switch ($res) {
					case 2:
						$json['error']['id'] = Configure::read('error.you_have_made_reservation_cancellation_more_than_3_times_in_1_day');
						$json['error']['message'] = __('You have made reservation cancellation more than 3 times in 1 day.');
						break;
					case 1:
						$memcached = new myMemcached();
						$teacherReservations = array();
						if($memcached->get('teacherHasReservation_' . $teacherId)) {
							$teacherReservations = $memcached->get('teacherHasReservation_' . $teacherId); 
						}
						array_pop($teacherReservations);
						$memcached->set(array(
							'key' => 'teacherHasReservation_'.$teacherId,
							'value' => $teacherReservations,
							'expire' => 604800
						));
						$json['cancelled'] = true;
						break;
					case 0:
						$json['error']['id'] = Configure::read('error.the_begin_time_you_specified_is_not_scheduled');
						$json['error']['message'] = __('The begin time you specified is not scheduled.');
						break;
					default:
						break;
				}
			}
		} else {
			$json['error']['id'] = Configure::read('error.invalid_request');
			$json['error']['message'] = __('Invalid request');
		}

		return json_encode($json);
	}
}