<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersSearchCategoriesController extends AppController {
	public $uses = array(
		'Teacher',
		'TeacherRankCoin',
		'User',
		'CountryCode'
	);
	private $api = null;
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('search', 'getTextbookCategoriesSearchItems'));
		$this->api = new ApiCommonController();
	}
	public function search() {
		$this->autoRender = false;
		@$inputs = json_decode($this->request->input(), true);
		$this->request->data = $inputs;
		$users = $response = array();
		$api = new ApiCommonController();
		if (
			isset($this->request->data['users_api_token']) &&
			!is_string($this->request->data['users_api_token'])
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
			return json_encode($response);
		} else if (
			isset($this->request->data['users_api_token']) &&
			trim($this->request->data['users_api_token']) == ""
		) {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
			return json_encode($response);
		} elseif (
			isset($this->request->data['users_api_token']) &&
			!( $users = $api->validateToken($this->request->data['users_api_token']) )
		) {
			$response['error']['id'] = Configure::read('error.invalid_api_token');
			$response['error']['message'] = __('Invalid users_api_token');
			return json_encode($response);
		}
		$post = $this->request->data;
		$apiVersion = (isset($post['api_version']) && $post['api_version'])? $post['api_version'] : 0;
		$nativeLang = "ja";
		$countryCodeIso639_2 = "jpn";

		// Not logged in
		if ( !isset($this->request->data['users_api_token']) && isset($this->request->data['user_language']) ) {

			$nativeLang = strtolower($this->request->data['user_language']);

			$countryCodeIso = $this->CountryCode->useReplica()->find("first",array(
					"conditions" => array( "CountryCode.iso_639_1" => $nativeLang ),
					"fields" => array("CountryCode.iso_639_2"),
					"recursive" => -1
				)
			);
			$countryCodeIso639_2 = isset($countryCodeIso["CountryCode"]["iso_639_2"]) && strlen($countryCodeIso["CountryCode"]["iso_639_2"]) > 0 ? $countryCodeIso["CountryCode"]["iso_639_2"] : "jpn" ;
		}

		
		if (isset($post['users_api_token']) && $post['users_api_token']) {
			$nativeLang = (isset($users["native_language2"]) && $users["native_language2"]) ? $users["native_language2"] : 'ja';
			if (isset($nativeLang) && $nativeLang) {
				$suportedlang = $this->CountryCode->commonMemcachedCountryCode();
				$countryCodeIso639_2 = $suportedlang[$nativeLang]["iso_639_2"];
			}
		}

		$setLocale = ($nativeLang != 'en') ? $countryCodeIso639_2 : 'eng' ;
		Configure::write('Config.language',$setLocale);
		$this->Session->write('Config.language',$setLocale);
		$features = array(
			array(
				'param_name' => "free_talk_flg",
				'name' => __d('features','フリートークが得意')
			),
			array(
				'param_name' => "callan_halfprice_flg",
				'name' => __d('features','カラン半額')
			),
			array(
				'param_name' => "beginner_teacher_flg",
				'name' => __d('features','予約無料')
			),
			array(
				'param_name' => "native_speaker_flg",
				'name' => __d('features','ネイティブ')
			)
		);
		
		$req = $this->request->data;
		$apiVersion = isset($req['api_version'])? $req['api_version'] : 0;
		$test= $this->getTextbookCategoriesSearchItems($apiVersion,$nativeLang);

		$nationalities = $this->getNationalities($apiVersion,$nativeLang);
		//get coin amounts
		$coinAmount = $this->TeacherRankCoin->getCoinAmount();
		$coinAmount = array_values($coinAmount);
		$coinAmount = array_map('intval', $coinAmount);
		$searchCategories = array(
			'textbook_category' => $test,
			'feature' => $features,
			'nationality' => $nationalities,
			'coin' => $coinAmount
		);
		
		foreach ($searchCategories["textbook_category"] as &$value) {
			unset($value["jp_name"]);
			unset($value["eng_name"]);
		}
		foreach ($searchCategories["nationality"] as &$value) {
			unset($value["jp_name"]);
			unset($value["eng_name"]);
			foreach ($value["country_list"] as &$value1) {
				unset($value1["jp_name"]);
				unset($value1["eng_name"]);
			}
		}
		
		return json_encode($searchCategories);
	}
	public function getNationalities($apiVersion,$nativeLang = null) {
		$nationalities = $this->Teacher->find('all', array(
				'fields' => array(
						'CountryCode.region',
						'CountryCode.country_name',
						'CountryCode.nationality',
						'CountryCode.id'
					),
				'conditions' => array(
						'Teacher.status = 1',
						'Teacher.stealth_flg = 0',
						'Teacher.admin_flg = 0',
						'Teacher.homeland2 IS NOT NULL',
						'CountryCode.id IS NOT NULL'
					),
				'joins' => array(
						array(
							'type' => 'LEFT',
							'table' => 'country_codes',
							'alias' => 'CountryCode',
							'conditions' => array(
									'Teacher.homeland2 = CountryCode.id'
								)
							)
					),
				'group' => 'CountryCode.region, CountryCode.id',
				'order' => 'COUNT(`Teacher`.`homeland2`) DESC, CountryCode.region ASC',
				'recursive' => -1
		));
		/**
		* Fetch available teacher nationality according to region
		*/
		$result = $nationalities;
		$result = $this->segregateByRegion($nationalities,$nativeLang,$apiVersion);
		$arrReg = array();
		$region_id = Configure::read('region_id');
		
		if ($nationalities) {
			foreach ($result as $key => $region) {
				switch ($nativeLang) {
					case 'ja':
						$setLocale = 'jpn';
					break;
					case 'ko':
						$setLocale = 'kor';
					break;
					case 'th':
						$setLocale = 'tha';
					break;
					default:
						$setLocale = 'eng';
					break;
				}
				Configure::write('Config.language',$setLocale);
				$this->Session->write('Config.language',$setLocale);
				$arrReg[] = array(
					'region_id' => $region_id[$key],
					'name' => ($setLocale == 'eng') ? $key : __d('default',$key),
					'country_list' => $region
				);
			}
		}
		return $arrReg;
	}
	private function segregateByRegion($nationalities = array(),$nativeLang = null, $apiVersion = null) {
		$formattedData = array();
		foreach ($nationalities as $nationality) {
			if (isset($nationality['CountryCode']['region']) && isset($nationality['CountryCode']['nationality'])) {
				
				$countries = strtolower(str_replace(' ', '_', $nationality['CountryCode']['country_name']));
				switch ($nativeLang) {
					case 'ja':
						$setLocale = 'jpn';
					break;
					case 'ko':
						$setLocale = 'kor';
					break;
					case 'th':
						$setLocale = 'tha';
					break;
					default:
						$setLocale = 'eng';
					break;
				}
				Configure::write('Config.language',$setLocale);
				$this->Session->write('Config.language',$setLocale);
				$formattedData[$nationality['CountryCode']['region']][] = array(
					'id' => $nationality['CountryCode']['id'],
					'name' => ($setLocale == 'eng') ? $nationality['CountryCode']['country_name'] : __d('default',$nationality['CountryCode']['country_name']),
					'country_image' => myTools::getUrl()."/images/flag/".$countries.".png",
				);
				
			}
		}
		return $formattedData;
	}
	/**
	 * getTextbookCategoriesSearchItems
	 * -> get textbook categories
	 */
	public function getTextbookCategoriesSearchItems ($apiVersion ,$nativeLang = null) {
		$this->autoRender = false;
		
		switch ($nativeLang) {
			case 'ja':
				$categoryName = '指定なし';
			break;
			case 'ko':
				$categoryName = '지정 없음';
			break;
			case 'th':
				$categoryName = 'ไม่ระบุ';
			break;
			default:
				$categoryName = 'Not Specified';
			break;
		}
		// create empty array
		$categories = array(
			array(
				'id' => 0,
				'name' => $categoryName
			)
		);
		
		// get categories
		$activeBadges = TeacherBadgeTable::getBadges();
		// check active badges
		if ($activeBadges) {
			// loop through active badges
			foreach($activeBadges as $badge){
				if (!isset($categories[$badge['TextbookCategory']['id']])) {
					switch ($nativeLang) {
						case 'ja':
							$textbookCategoryName = $badge['TextbookCategory']['name'];
						break;
						case 'ko':
							$textbookCategoryName = $badge['TextbookCategory']['korean_name'];
						break;
						case 'th':
							$textbookCategoryName = $badge['TextbookCategory']['thai_name'];
						break;
						default:
							$textbookCategoryName = $badge['TextbookCategory']['english_name'];
						break;
					}
					$categories[$badge['TextbookCategory']['id']] = array(
						'id' => $badge['TextbookCategory']['id'],
						'name' => $textbookCategoryName,
					);
				}
			}
		}
		// return categories
		return array_values($categories);
	}
}