<?php
/****************************
 * API for Info Finish Read
 * Author : Karl Vincent Lim
 * March 2017   
 *****************************/

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class AnnouncementInfoFinishReadController extends AppController{

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

		//if no request error
		if (!isset($result['error']['message'])) {			
			if (isset($data['id'])) {
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

				 $to_update = $this->Announce->useReplica()->find('first', array(
					'fields' => array('Announce.id'),
					'conditions' => array(
						'Announce.id' => $data['id'],
						'Announce.currency_id' => array(0, $userCurrId), // NC-5051
                		'Announce.language_id' => array(0, $userLangId)
					),
					'recursive' => -1
					)
			    	);
				 $notif = $this->NotificationsRead->find('first', array(
					'conditions' => array(
						'user_id' => $user['id'],
						'notif_id' => $data['id']
					)
				 ));
			}

			//check announcement exist
			 if (isset($to_update) && count($to_update) <= 0) {
				$result['error']['id'] = Configure::read('error.id_does_not_exist');
				$result['error']['message'] = 'Announcement ID not found';
			} else {
				//if annuoncement is already read
				if (!isset($notif) || $notif['NotificationsRead']['read_flg'] != '1') {
					$seeReadNotificationsVar = $this->Announce->seeReadNotifications(array(
						'announceId' =>isset($data['id']) ? $data['id'] : null,
						'userId' => $user['id'],
						'viewType' => 'read',
						'countUnread' => true,
						'api' => true
					));
					if (isset($data['id'])) {
						$result['result'] = true;
					}
					$result['count'] = (isset($seeReadNotificationsVar['error'])) ? $seeReadNotificationsVar : count($seeReadNotificationsVar);
				} else {
					$result['error']['id'] = Configure::read('error.announcement_already_read');
					$result['error']['message'] = 'Announcement is already read';
				}
			
			}
		
			
		}
		echo json_encode($result);
	}
}