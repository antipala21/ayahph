 <?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class ReservationsTextbookChangeController extends AppController {
	public $uses = array(
		'LessonSchedule',
		'TextbookConnect',
		'User'
	);

	public $helpers = array('Html', 'Form');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
		$Validate = new ApiCommonController();
		$this->autoRender = false;
		$response = array();

		if ($this->request->is('post')) {

			$data = @json_decode($this->request->input(), true);
			if (!$data) {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
			} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			} else if (!isset($data['reservation_id']) || empty($data['reservation_id'])) {
				$response['error']['id'] = Configure::read('error.reservation_id_is_required');
				$response['error']['message'] = __('reservation_id is required');
			} else if ( !isset($data['connect_id']) || empty($data['connect_id'])) {
				$response['error']['id'] = Configure::read('error.connect_id_is_required');
				$response['error']['message'] = __('connect_id is required');
			} else {
				$user = $Validate->findApiToken($data['users_api_token']);
				if (!$user) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = $api->error;
				} else {
					$change = true;
					$callanCategoryTypes = array(2,5,6);
					$dateNow = date('Y-m-d H:i:s', strtotime('-1 minute'));
					$connectId = $data['connect_id'];
					$reservationId = $data['reservation_id'];
					$reservation = $this->LessonSchedule->useReplica()->find('first', array(
						'fields' => array(
							'Teacher.callan_halfprice_flg',
							'LessonSchedule.connect_id'
						),
						'joins' => array(
							array(
								'table' => 'textbook_connects',
								'alias' => 'TextbookConnect',
								'type' => 'INNER',
								'conditions' => 'TextbookConnect.id = LessonSchedule.connect_id'
							),
							array(
								'table' => 'textbook_categories',
								'alias' => 'TextbookCategory',
								'type' => 'INNER',
								'conditions' => 'TextbookCategory.id = TextbookConnect.category_id'
							),
							array(
								'table' => 'textbooks',
								'alias' => 'Textbook',
								'type' => 'INNER',
								'conditions' => 'Textbook.id = TextbookConnect.textbook_id AND Textbook.callan_level_check != 1' // not callan level check
							),
							array(
								'table' => 'teachers',
								'alias' => 'Teacher',
								'type' => 'LEFT',
								'conditions' => 'Teacher.id = LessonSchedule.teacher_id'
							),
						),
						'conditions' => array(
							'LessonSchedule.id' => $reservationId,
							'LessonSchedule.lesson_time >' => $dateNow,
							'OR' => array(
								'Teacher.callan_halfprice_flg' => 0,
								array(
									'Teacher.callan_halfprice_flg' => 1,
									'TextbookCategory.textbook_category_type NOT IN' => $callanCategoryTypes
								)
							)
						),
						'recursive' => -1
					));

					if ($reservation) {
						// get change textbook for callan and obunsya
						$textbook = $this->TextbookConnect->useReplica()->find('first', array(
							'fields' => array(
								'TextbookCategory.textbook_category_type',
								'Textbook.callan_level_check'
							),
							'joins' => array(
								array(
									'table' => 'textbook_categories',
									'alias' => 'TextbookCategory',
									'type' => 'INNER',
									'conditions' => 'TextbookCategory.id = TextbookConnect.category_id AND TextbookCategory.reservation_flg = 1'
								),
								array(
									'table' => 'textbooks',
									'alias' => 'Textbook',
									'type' => 'INNER',
									'conditions' => 'Textbook.id = TextbookConnect.textbook_id'
								)
							),
							'conditions' => array('TextbookConnect.id' => $connectId)
						));

						if ($textbook) {
							// set to false if card auth user and change textbook to obunsya ||
							// set to false if user did not perform callan level check and change textbook to callan method ||
							// set to false if change textbook to callan level check ||
							// set to false if callan half price teacher
							if (
								(
									PaymentTable::checkIfCardAuth($user['id'], $user['hash16']) && 
									$textbook['TextbookCategory']['textbook_category_type'] == 0
								) || 
								($user['callan_level_check'] != 2 && $textbook['TextbookCategory']['textbook_category_type'] == 2) ||
								$textbook['Textbook']['callan_level_check'] || 
								($reservation['Teacher']['callan_halfprice_flg'] && $textbook['TextbookCategory']['textbook_category_type'] != 0)
							) {
								$change = false;
							}
						}
					} else {
						$change = false;
					}

					if ($change) {
						// update reservation textbook
						$lsModel = $this->LessonSchedule;
						$lsModel->clear();
						$read = $lsModel->read(null, $reservationId);
						$save = false;
						if ($read) {
							$lsModel->set(array(
								'connect_id' => $connectId,
								'old_connect_id' => $reservation['LessonSchedule']['connect_id'],
								'teacher_check_flg' => 1
							));
							$save = $this->LessonSchedule->save();
						}

						if (!$read || !$save) {
							$change = false;
						}
					}

					$response['change'] = $change;
				}
			}
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		}

		return json_encode($response);
	}
}