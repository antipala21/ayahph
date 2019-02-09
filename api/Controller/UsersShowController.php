<?php

/**
 * Author : John Robert Jerodiaz ( Roy )
 * Users Show own account API
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersShowController extends AppController {

  public $uses = array(
    'User',
    'UsersPoint',
    'CampaignMaster',
    'PaymentReceivable',
    'SettlementCurrency',
    'CountryCode',
    'FamilyPlanList',
    'Timezone'
  );

  public function beforeFilter() {
      parent::beforeFilter();
      $this->Auth->allow();
      $this->autoRender = false;
  }

  /***
   * Function use to show user information
   * @return json
   */
  public function index() {

    $response = array();
    //expect json request
    $data = json_decode($this->request->input(),true);
    $CountryCodes = $this->CountryCode->commonMemcachedCountryCode();
    if (!$data) {
        $response['error']['id'] = Configure::read('error.invalid_request');
        $response['error']['message'] = __('Invalid request');
    } else if (isset($data['user_language']) && !isset($CountryCodes[$data['user_language']])) {
        $response['error']['id'] = Configure::read('error.users_language_invalid');
        $response['error']['message'] = __('users_language is not supported');
    } else if (isset($data['users_api_token']) && empty($data['users_api_token'])) {
        $response['error']['id'] = Configure::read('error.users_api_token_is_required');
        $response['error']['message'] = __('users_api_token is required');
    } else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])){
        $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
        $response['error']['message'] = __('users_api_token must be string');
    } else if (isset($data['api_version']) && is_string($data['api_version'])) {
        $response['error']['id'] = Configure::read('error.api_version_must_be_integer');
        $response['error']['message'] = __('api version must not be string');
    } else {

      $validCurrency = Configure::read('valid.settlement.currency');
      $monthlyPrices = Configure::read('monthly.prices');

      if (isset($data['users_api_token'])) {
        $api = new ApiCommonController();
        $user['User'] = $api->validateToken($data['users_api_token']);
        if ($user['User']) {
          $coins = @$this->UsersPoint->getCurrentUserPoint($user['User']['id']);
          $user = new UserTable($user['User']);
          $apiVersion = isset($data['api_version']) ? $data['api_version'] : 0;
          $userStatus = $user->getAccountStatus(array('api_version' => $apiVersion));

          //declare variable
          $failDate = "";
          $failPrice = "";
          $monthlyPrice = "";
          $coinPrice = "";
          $defaultCurrInfo = $this->SettlementCurrency->getDefaultCurrencyInfo();
          $userCurrency = $defaultCurrInfo['currency'];
          $userLang = Configure::read('default.user_language');
          $userTimezone = Configure::read('default.user_timezone_id');
          $settlementCurrency = array_keys($validCurrency);          
          $listCurrency = $this->SettlementCurrency->supportedCurrencyById();


          // check and set user language
          if ($user->native_language2 != '' && isset($CountryCodes[$user->native_language2])) {
            $userLang = $user->native_language2;
          }

          // check and set user timezone
          if ($user->timezone_id != null) {
            $userTimezone = $user->timezone_id;
          }

          //get user settlement currency
          if ($user->settlement_currency_id != "") {
            $tempCurrencyId = $user->settlement_currency_id;     
            if (isset($listCurrency[$tempCurrencyId])) {                
                $userCurrency = $listCurrency[$tempCurrencyId]['currency'];
            }
          }

          $getCurrSymbol = $this->SettlementCurrency->find('first', array(
                          'fields' => array('currency_symbol'),
                          'conditions' => array('currency' => $userCurrency)
                        ));

          //check fail flg
          if ($user->fail_flg) {
            $date = PaymentTable::failSettlementDate($user->id);

            if ($date == null) {
              $failDate = null;              
            } else {
              $failDate = date("Y-m-d", strtotime($date));
            }

            $getFailPrice = PaymentTable::computeFailResult($user->id,$user->parent_id);
            $failPrice = $this->assignCurrency($getFailPrice, $userLang, $userCurrency);
          } else {
            $failPrice = $this->assignCurrency(0, $userLang, $userCurrency);
          }

          $canPurchase = 1;
          //fetch can_purchase_flg if family_plan
          if ($userStatus == 7) {
              $family = $this->FamilyPlanList->useReplica()->find('first', array(
                    'fields' => 'FamilyPlanList.can_coin_purchase_flg',
                    'conditions' => array('FamilyPlanList.family_id' => $user->id)
              ));

              if (isset($family['FamilyPlanList']['can_coin_purchase_flg']) && $family['FamilyPlanList']['can_coin_purchase_flg'] == 0) {
                $canPurchase = 0;
              }
          }

          $nextChargedate = null;
          if (isset($user->next_charge_date)) {
            $currencyTimezone = Configure::read('currency_timezone');
            $nextChargedateTime = strtotime($user->next_charge_date);
            if (isset($currencyTimezone[$user->settlement_currency_id])) {
              $timeDiff = $this->Timezone->getTimeDiff(array('id' => $currencyTimezone[$user->settlement_currency_id], 'dst' => 0));
              $nextChargedateTime = strtotime($timeDiff . ' minutes', $nextChargedateTime);
            }
            $nextChargedate = date("Y-m-d H:i:s", $nextChargedateTime);
          }

          if ($user->monthly_payment == '') {
            $monthlyPrice = $this->assignCurrency($monthlyPrices[$userCurrency], $userLang, $userCurrency);
          } else {
            $monthlyPrice = $this->assignCurrency($user->monthly_payment, $userLang, $userCurrency);
          }

          $coinPrice = $this->getCoinList($userCurrency, $userLang);

          $response = array(
            'users_id'          => @$user->id,
            'users_username'    => @$user->nickname,
            'users_gender'      => (int)@$user->gender,
            'users_gender_show_flg' => intval($user->gender_show_flg),
            'users_birthday'    => (isset($user->birthday) && trim($user->birthday) <> '0000-00-00') ? $user->birthday : null,
            'users_birthday_show_flg' => intval($user->birthday_show_flg),
            'profile_image'     => $user->getImageUrl(),
            'account_status'    => $userStatus,
            'settlement_company'=> (isset($user->card_company) && !is_null($user->card_company))? (int) $user->card_company : null,
            'coin'              => (int)$coins,
            'users_email'       => @$user->email,
            'next_charge_date'  => $nextChargedate,
            'magazine_flg'         => intval($user->magazine_flg),
            'reservation_mail_flg' => intval($user->reservation_mail_flg),
            'reservation_cancel_mail_flg' => intval($user->reservation_cancel_mail_flg),
            'fail_flg'          => @$user->fail_flg,
            'fail_date'         => $failDate,
            'fail_price'        => $failPrice,
            'double_check_flg'  => @$user->double_check_flg,
            'has_child' => (($userStatus == 3) && $this->User->isParent($user->id)) ? true : false,
            'language' => $userLang,
            'timezone_id' => (int)$userTimezone,
            'residence_id' => isset($user->residence_id) ? intval($user->residence_id) : null,
            'residence_show_flg' => intval($user->residence_show_flg),
            'nationality_id' => isset($user->nationality_id) ? intval($user->nationality_id) : null,
            'nationality_show_flg' => intval($user->nationality_show_flg),
            'monthly_price' => $monthlyPrice,
            'coin_prices' => $coinPrice,
            'currency' => $userCurrency."(".$getCurrSymbol['SettlementCurrency']['currency_symbol'].")",
            'currency_id' => $userCurrency,
            'can_coin_purchase_flg' => $canPurchase
          );
        }else{
          $response['error']['id'] = Configure::read('error.invalid_api_token');
          $response['error']['message'] = __('Invalid users_api_token');
        }
      } elseif(isset($data['user_language'])) {
        $userLang = $data['user_language'];
        $monthlyPrice = "";
        $coinPrice = "";
        if ($userLang == 'en' || $userLang == 'ja') {
          $userCurrency = 'JPY';
          $monthlyPrice = $monthlyPrices[$userCurrency];
          $coinPrice = $this->getCoinList($userCurrency, $userLang);
        } else {
          // get language currency          
          $userCurrency = $this->CountryCode->getLangCurrency($userLang);
          $userCurrency = $userCurrency['currency'];
          $monthlyPrice = $monthlyPrices[$userCurrency];
          $coinPrice = $this->getCoinList($userCurrency, $userLang);
        }
        $response['monthly_price'] = $this->assignCurrency($monthlyPrice, $userLang, $userCurrency);
        $response['coin_prices'] = $coinPrice;
      }  else {
        $response['error']['id'] = Configure::read('error.invalid_request');
        $response['error']['message'] = __('Invalid request');
      }
    }
    return json_encode($response, true);
  }

  public function assignCurrency($amount, $lang, $currency){
    $validCurrency = Configure::read('valid.settlement.currency');
    if ($lang == 'en') {
      return $validCurrency[$currency][$lang].number_format($amount);
    } else {
      return number_format($amount).$validCurrency[$currency][$lang];
    }
  }

  public function getCoinList($currency, $lang){
    $coinPrices = Configure::read('coin.prices');
    $getcoinPrices = $coinPrices[$currency];
    $coinPrice = array();
    foreach ($getcoinPrices as $key => $value) {
      if($key == 0){
        $coinPrice[$key] = $value[$lang];
      }else{
        $coinPrice[$key] = $this->assignCurrency($value, $lang, $currency);
      }
    }
    return $coinPrice;
  }

	/** NC-4910 : cosmopier site ( e-station ) */
	public function cosmopier(){
		$response = array();
		$get = $this->request->query;

		if (!$get) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (!isset ($get['token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (empty($get['token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (!is_string($get['token'])){
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else {

			// get/check users_api_token
			$api = new ApiCommonController();
			$getUser = $api->validateToken($get['token']);
			if (!$getUser) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('Invalid users_api_token');
			} else {

				$cmtArr = Configure::read('cosmopier_membership_type');

				if ( $getUser ) {
					$cmCode = "";
					if ( isset($getUser["campaign_id"]) && strlen($getUser["campaign_id"]) ) {
						$getCC = $this->CampaignMaster->find("first",array(
								"conditions" => array( "CampaignMaster.id" => $getUser["campaign_id"] ),
								"fields" => array("CampaignMaster.title"),
								"recursive" => -1
							)
						);
						if ($getCC) {
							$cmCode = ( isset($getCC["CampaignMaster"]["title"]) && strlen($getCC["CampaignMaster"]["title"]) ) ? $getCC["CampaignMaster"]["title"] : "" ;
						}
					}

					$user = new UserTable($getUser);
					$studentStatus = $user->getMembershipTypeIndex();

					$response["users_id"] = $user->id;
					$response["status"] = in_array($studentStatus, $cmtArr) ? 1 : 0; // 1 = can lesson ,0 = can not lesson
					$response["cmcode"] = $cmCode;

				}
				
			}
		}
		return json_encode($response, true);
	}

	/** NC-4910 : cosmopier site checking ( e-station ) */
	public function cosmopier_check(){

			$response = $dataArr = $illigalTokensArr = array();
			//expect json request
			$data = json_decode($this->request->input(),true);

			if ( !isset($data["token"]) ) {
				$response['error']['message'] = __('Token is required.');
			} elseif ( isset($data["token"]) && is_array($data["token"]) == false ) {
				$response['error']['message'] = __('Token should be an array.');
			} else {

				$userTokens = $data["token"];
				$illigalTokensArr = $userTokens;

				$getUsers = $this->User->useReplica()->find("all",array(
						"conditions" => array( 
							"User.api_token" => $userTokens
						),
						"fields" => array(
							"User.id",
							"User.status",
							"User.charge_flg",
							"User.fail_flg",
							"User.parent_id",
							"User.corporate_id",
							"User.corporate_type",
							"User.hash16",
							"User.api_token",
							"CampaignMaster.title"
						),
						"joins" => array(
							array(
								'table' => 'campaign_masters',
								'alias' => 'CampaignMaster',
								'type' => 'LEFT',
								'conditions' => 'CampaignMaster.id = User.campaign_id'
							)
						),
						"recursive" => -1
					)
				);

				if ( $getUsers ) {

					foreach ($getUsers as $key => $value) {

						$cmtArr = Configure::read('cosmopier_membership_type');
						$user = new UserTable($value["User"]);
						$studentStatus = $user->getMembershipTypeIndex();
						$cmCode = isset( $value["CampaignMaster"]["title"] ) && strlen( $value["CampaignMaster"]["title"] ) ? ( $value["CampaignMaster"]["title"] ) : "";

						$dataArr["token"] = $user->api_token;
						$dataArr["users_id"] = $user->id;
						$dataArr["status"] = in_array($studentStatus, $cmtArr) ? 1 : 0; // 1 = can lesson ,0 = can not lesson
						$dataArr["cmcode"] = $cmCode;

						$response["users"][] = $dataArr;

						// unset legal tokens
						$index = array_search( $user->api_token, $illigalTokensArr );
						if ( $index !== FALSE ) {
							unset( $illigalTokensArr[$index] );
						}

					}
					// check for illigal tokens
					if ( count($illigalTokensArr) > 0 ) {

						foreach ($illigalTokensArr as $key => $value) {

							$dataArr["token"] = $value;
							$dataArr["users_id"] = "0";
							$dataArr["status"] = 0;
							$dataArr["cmcode"] = "";

							$response["users"][] = $dataArr;
						}

					}
				}

			}
			return json_encode($response, true);
	}
}
