<?php
/****************************
 * API for Important Announcement Info
 * Author : Karl Vincent Lim
 * March 2017   
 *****************************/

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class AnnouncementImportantInfoController extends AppController{

	public $uses = array(
		'Announce',
		'NotificationsRead',
		'CountryCode',
		'SettlementCurrency'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow('index');
	}

	public function index(){
		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
			$curTime = date("Y-m-d H:i:s", time());
			
			if (empty($data)) {
				$result['error']['id'] = Configure::read('error.invalid_request');
				$result['error']['message'] = __('Invalid request.');
			} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
				$result['error']['id'] = Configure::read('error.users_api_token_is_required');
				$result['error']['message'] = __('users_api_token is required');
			} else if (!is_string($data['users_api_token'])) {
				$result['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$result['error']['message'] = __('The users_api_token must be string request.');
			} else if (!isset($data['device'])) {
				$result['error']['id'] = Configure::read('error.device_type_is_required');
				$result['error']['message'] = __('device is required.');
			} else if (!is_int($data['device'])) {
				$result['error']['id'] = Configure::read('error.device_must_be_integer');
				$result['error']['message'] = __('The device must be integer request.');
			} else if (!in_array($data['device'], array(1,2,4))) {
				$result['error']['id'] = Configure::read('error.invalid_device');
				$result['error']['message'] = __('The device is invalid.');
			} else {
				$user_api_token = $data['users_api_token'];
				$api = new ApiCommonController();
				$user = $api->findApiToken($user_api_token);

				if (is_array($user)) {
					//if has user
					if (!array_key_exists('id', $user)) {
						$result['error']['id'] = Configure::read('error.invalid_api_token');
						$result['error']['message'] = $api->error;
					} 
				} else {
					$result['error']['id'] = Configure::read('error.invalid_api_token');
					$result['error']['message'] = $api->error;
				}
			}

			//if no request error
			if (!isset($result['error']['message'])) {
				$device_display = array(0,2); // Both and APP 
				switch ($data['device']) {
					case 1:
						$device_display[] = 3; // ios
						break;
					case 2:
						$device_display[] = 4; // android
						break;
					case 4:
						$device_display[] = 5; // kindle
						break;	
				}

				// get user's read notification 
				$notifications_read = $this->NotificationsRead->find('all', array(
					'conditions' => array(
							'NotificationsRead.user_id' => $user['id'],
							'NotificationsRead.read_flg' => 1
						),
					'fields' => 'NotificationsRead.notif_id',
					'group' => 'NotificationsRead.notif_id',
					'recursive' => -1
				));

				$notifids = array();
				//extract read notifification ids for exclusion
				foreach ($notifications_read as $notifread) {
					if (isset($notifread["NotificationsRead"]["notif_id"])) {
						$notifids[] = $notifread["NotificationsRead"]["notif_id"];
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

				$fields = array(
					'Announce.id', 
					'Announce.title',
					'Announce.contents',
					'Announce.title as title_ja',
					'Announce.contents as contents_ja'
				);
				$conditions = array(
					'Announce.start_time <='=> $curTime,
					'Announce.end_time >='=> $curTime,
					'Announce.status' => 1,
					'Announce.kbn' => 1,
					'Announce.device_display' => $device_display,
					'NOT' => array('Announce.id' => $notifids),
					'Announce.currency_id' => array(0, $userCurrId), // NC-5051
					'Announce.language_id' => array(0, $userLangId)
				);

				$options = array(
					'conditions' => $conditions,
					'fields' => $fields,
					'order'=> array('disp_date' => 'DESC','id' => 'DESC'),
					'group' => 'Announce.id',
					'recursive' => -1
				);
				$notifications = $this->Announce->useReplica()->find('all', $options);

				if (!$notifications) {
					$result['error']['id'] = Configure::read('error.no_announce_message');
					$result['error']['message'] = 'There is no announce';
					return json_encode($result);
				}

				$nativeLanguage = $user['native_language2'];
				$apiVersion = isset($data['api_version']) ? $data['api_version'] : null;

				$this->loadModel('Translation');
				$translationCategories = Configure::read('translation_categories');
				$translateTextArray = array('languageCode' => $nativeLanguage);

				foreach ($notifications as $announces) {
					$announces["Announce"]['id'] = intval($announces["Announce"]['id']);
					$announces["Announce"]['title_ja'] = $announces["Announce"]['title'];
					$announces["Announce"]['contents_ja'] = $announces["Announce"]['contents'];

					// title
					$translateTextArray['categoryId'] = $translationCategories['announce_title'];
					$translateTextArray['messageId'] = $announces["Announce"]['id'];
					$translateTextArray['text'] = $announces["Announce"]['title'];
					$announces["Announce"]['title'] = $this->Translation->translateText($translateTextArray);

					// content
					$translateTextArray['categoryId'] = $translationCategories['announce_contents'];
					$translateTextArray['text'] = $announces["Announce"]['contents'];
					$announces['Announce']['contents'] = $this->Translation->translateText($translateTextArray);
					$result['announces'][] = $announces["Announce"];
				}
			}
			return json_encode($result);
		}
	}
}