<?php
/****************************
 * API for Info Finish Read
 * Author : FDC
 * January 2018  
 *****************************/

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class AnnouncementInfoCountController extends AppController{

	public $uses = array('Announce', 'Inquiry', 'CountryCode', 'SettlementCurrency');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow('index');
	}

	public function index() {
		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
		}
		
		if (empty($data)) {
			$result['error']['id'] = Configure::read('error.invalid_request');
			$result['error']['message'] = __('Invalid request.');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$result['error']['id'] = Configure::read('error.users_api_token_is_required');
			$result['error']['message'] = __('users_api_token is required');
		} else if (!is_string($data['users_api_token'])) {
			$result['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$result['error']['message'] = __('The users_api_token must be string request.');
		} else if (isset($data['device']) && !is_int($data['device'])) {
			$result['error']['id'] = Configure::read('error.device_must_be_integer');
			$result['error']['message'] = __('The device must be integer request.');
		} else if (isset($data['device']) && !in_array($data['device'], array(1, 2, 3))) {
			$result['error']['id'] = Configure::read('error.invalid_device');
			$result['error']['message'] = __('The device is invalid.');
		} else {
			$user_api_token = $data['users_api_token'];
			$api = new ApiCommonController();
			$user = $api->findApiToken($user_api_token);

			if (is_array($user)) {
				// check if has user
				if (!array_key_exists('id', $user)) {
					$result['error']['id'] = Configure::read('error.invalid_api_token');
					$result['error']['message'] = $api->error;
				} 
			} else {
				$result['error']['id'] = Configure::read('error.invalid_api_token');
				$result['error']['message'] = $api->error;
			}
		}

		// if no request error
		if (!isset($result['error']['message'])) {
			$dateNow = date('Y-m-d H:i:s');

			$device_display = array(0,2); //PC/APP and APP Only

			if (isset($data['device'])) {
				$device = $data['device'];
				if ($device == 1) {//iOS
					$device_display[] = 3;
				} elseif ($device == 2) {//Android
					$device_display[] = 4;
				} elseif ($device == 3) {//Kindle
					$device_display[] = 5;
				}
			}

			// NC-5051
	        // get user language info
	        $userLangId = $this->CountryCode->getUserLanguageId($user['native_language2']);
	        // get user currency info
	        if ($user['settlement_currency_id'] != '') {
	            $userCurrId = $user['settlement_currency_id'];
	        }else {
	            $getDefaultCurr = $this->SettlementCurrency->getDefaultCurrencyInfo();
	            $userCurrId = $getDefaultCurr['id'];
	        }

			// count user unread announcement
			$cntAnnounces = $this->Announce->useReplica()->find('count', array(
				'conditions' => array(
					'Announce.start_time <=' => $dateNow,
					'Announce.end_time >=' => $dateNow,
					'Announce.status' => 1,
					'Announce.device_display' => $device_display,
					'OR' => array(
						'NotificationsRead.read_flg IS NULL',
						'NotificationsRead.read_flg' => 0
					),
					'Announce.currency_id' => array(0, $userCurrId), // NC-5051
                	'Announce.language_id' => array(0, $userLangId)
				),
				'joins' => array(
					array(
						'table' => 'notifications_read',
						'alias' => 'NotificationsRead',
						'type' => 'left',
						'conditions' => array('NotificationsRead.notif_id = Announce.id and NotificationsRead.notif_type = 1 and NotificationsRead.user_id = ' . $user['id'])
					)
				)
			));

			// count user unread inquiry
			$cntInquiries = $this->Inquiry->useReplica()->find('count', array(
				'conditions' => array(
					'user_id' => $user['id'],
					'student_read_flg' => 0,
					'admin_id IS NOT NULL'
				),
				'recursive' => -1
			));

			$result = array(
				'announcement' => $cntAnnounces,
				'inquiry' => $cntInquiries
			);
		}

		return json_encode($result);
	}
}