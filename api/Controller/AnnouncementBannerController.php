<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class AnnouncementBannerController extends AppController {
	
	private $api = null;
	private $invalidRequest = 'Invalid Request';
	private $noBannerMsg = 'There is no banner for you';
	
	public $uses = array('CampaignAppBanner');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('banner'));
		$this->autoRender = false;
		$this->api = new ApiCommonController();
	}
	
	/**
	* announcement/banner
	* @specs, refer to this link https://github.com/VJSOL/NativeCamp-iOS/wiki/Api:-Announcement/5f6ab7af4d59e83b64066cc5893f806b0dc48b85
	* @specs, https://docs.google.com/presentation/d/1pm4TG5Ft5padypvTRf9vn79lU9g83JBZFhfGbGvHKkc/edit#slide=id.g1efff3be39_0_152
	* request data -> users_api_token (optional)
	*				-> sign_in_flg (required)
	*/
	function banner() {
		$response = array();
		// request data
		$reqData = json_decode($this->request->input(), true);
		if ($this->request->is('post') && $reqData) {
			$validate = $this->validateAndResult($reqData);
			if ($validate['error']) {
				$response['error'] = $validate['content'];
			} else {
				$response = $validate['content'];
			}
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __($this->invalidRequest);
		}
		return json_encode($response);
	}
	
	/**
	* validateAndResult, validates request data
	* if no error, it gives the banner result
	* @param $reqData -> array()
	* @result $response -> array()
	*/
	private function validateAndResult($reqData=array()) {
		$response = array('error' => false, 'content' => null);
		$userInfo = array();
		if (isset($reqData['users_api_token'])) {
			$userInfo = $this->api->validateToken($reqData['users_api_token']);
			$userInfo = $userInfo ? $userInfo : null;
			if (!empty($reqData['users_api_token']) && !$userInfo) {
				$response['error'] = true;
				$response['content']['id'] = Configure::read('error.invalid_api_token');
				$response['content']['message'] = $this->api->error;
			}
			if (!empty($reqData['users_api_token']) && !is_string($reqData['users_api_token'])) {
				$response['content']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['content']['message'] = $this->api->error;
			}
		}
		if (!$response['error']) {
			$banners = $this->getBanners($userInfo);
			if ($banners) {
				$response['content'] = $banners;
			} else {
				$response['content']['error']['id'] = Configure::read('error.no_banner_message');
				$response['content']['error']['message'] = __($this->noBannerMsg);
			}
		}
		return $response;
	}
	
	/**
	* getBanners, announcement banners
	**/
	public function getBanners($user=array(), $signInFlg=0) {
		$signInFlg = $user ? 1 : 0;
		$conditions = ' `CampaignAppBanner`.`status` = 1';
		$conditions .= ' AND `CampaignAppBanner`.`sign_in` IN (' . $signInFlg . ', 2)';
		
		if ($user) {
			
			// user membership type
			$userCon = $this->getUserStatusCondition($user);
			
			$usert = new UserTable(array());
			$companyCards = array_keys(UserTable::getSettlementCompany());
			
			// user card_company
			if (in_array($user['card_company'], $companyCards) && !is_null($user['card_company'])) {
				$conditions .= " AND `CampaignAppBanner`.`payment_company` LIKE '%" . $user['card_company'] . "%'";
			}
			
			// free trial condition
			$freeTrialCon = $this->freeTrialCondition($user);
			
			// check if first condition is free, and second is a force settlement || fail payment user
			if ($userCon == "`CampaignAppBanner`.`user_status` LIKE '%5.1%'" && $freeTrialCon == " `CampaignAppBanner`.`user_status` LIKE '%5.0%'") {
				// use free trial (no)
				$conditions .= " AND " . $freeTrialCon;
				
			} else if ($userCon && $freeTrialCon) {
				// first condition is not free trial and second condition is free trial (yes) or (no)
				$conditions .= " AND ( " . $userCon . " OR " . $freeTrialCon .  " ) ";

			} else {
				// user what type of free trial is this user
				$conditions .= " AND " . $freeTrialCon;
			}
		}
		
		$sql = 'SELECT `CampaignAppBanner`.`image_url`,';
		$sql .= ' `CampaignAppBanner`.`campaign_url`,';
		$sql .= ' `sign_in`';
		$sql .= ' FROM';
		$sql .= ' `campaign_app_banners` AS `CampaignAppBanner`';
		$sql .= ' FORCE INDEX (sign_in)';
		$sql .=  ' WHERE';
		$sql .= ' ' . $conditions;
		$sql .= ' order by ISNULL(`CampaignAppBanner`.`priority_number`), `CampaignAppBanner`.`priority_number` ASC';
		$campaignApp = $this->CampaignAppBanner->useReplica()->query($sql);
		$banner = array();
		// NC-4854
		$checkCreditCharge = myTools::getUrl().'/user/mobapp/payment/credit_charge';
		$setCreditRetry = myTools::getUrl().'/user/mobapp/payment/credit_retry';
		foreach($campaignApp as $val) {
			$ca = new CampaignAppBannerTable($val['CampaignAppBanner']);
			$banner['banners'][] = array(
				'image' => $ca->getImageUrl(),
				'url' => ($user['fail_flg'] == 1 && $ca->campaign_url == $checkCreditCharge ? $setCreditRetry : $ca->campaign_url)
			);
		}
		return $banner;
	}
	
	private function getUserStatusCondition($user=array()) {
		$result = '';
		$user = new UserTable($user);
		if ($user->status == 9 && $user->admin_flg == 0) {
			$result = "`CampaignAppBanner`.`user_status` LIKE '%7%'";
		} elseif ($user->status == 0 && $user->admin_flg == 0) {
			$result = "`CampaignAppBanner`.`user_status` LIKE '%6%'";
		} elseif ($user->status == 1 && $user->charge_flg == 0 && $user->admin_flg == 0) {
			$result = "`CampaignAppBanner`.`user_status` LIKE '%5.1%'";
		} else {
			$membership = $user->getUserMembership();
			$userType = PaymentTable::getUserType($user->id, $user->hash16);
			if ($user->admin_flg == 1) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%1%'";
			} elseif ($user->parent_id) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%5.1%'";
			} elseif ($membership == Configure::read('user.member_type_fail')) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%5.1%'";
			} elseif ($membership == Configure::read('user.member_type_expired')) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%5.1%'";
			} elseif ($membership == Configure::read('user.member_type_free')) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%5.1%'";
			} elseif (
				$membership == Configure::read('user.member_type_paid') &&
				$userType == 'premium_plan_paid'
			) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%1%'";
			} elseif (
				$membership == Configure::read('user.member_type_paid') &&
				$userType == 'premium_plan_free'
			) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%2%'";
			} elseif (
				$membership == Configure::read('user.member_type_paid') &&
				$userType == 'weekly_plan_paid'
			) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%4%'";
			} elseif (
				$membership == Configure::read('user.member_type_paid') &&
				$userType == 'weekly_plan_free'
			) {
				$result = "`CampaignAppBanner`.`user_status` LIKE '%3%'";
			}
		}
		return $result;
	}
	
	private function freeTrialCondition($user=array()) {
		// free trial condition
		$result = '';
		if ( (
				isset($user['charge_flg']) && 
				isset($user['fail_flg']) && 
				isset($user['double_check_flg']) &&
				$user['charge_flg'] == "0" && 
				$user['fail_flg'] == "0" && 
				$user['double_check_flg'] == "2"
			)
			||
			(
				isset($user['fail_flg']) &&
				$user['fail_flg'] == "1"
			)
		) {
			
			// add sql condition if it matches from above condition
			// this user here is forcePayment || fail Payment User
			$result = " `CampaignAppBanner`.`user_status` LIKE '%5.0%'";
			
		// add sql condition if it does not match from above condition
		} else {
			$result = " `CampaignAppBanner`.`user_status` LIKE '%5.1%'";
		}
		return $result;
	}
}