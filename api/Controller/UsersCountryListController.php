<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersCountryListController extends AppController {
	public $uses = array(
		'CountryCode', 
		'User',
		'Country'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
		$this->api = new ApiCommonController();
	}

	public function index() {
		$this->autoRender = false;
		$data = json_decode($this->request->input(), true);
		$response = array();

		$userLanguage = Configure::read('default.user_language');
		$supportedLang = $this->CountryCode->commonMemcachedCountryCode();

		if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		} else if (isset($data['users_api_token']) && trim($data['users_api_token']) == '') {
			$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['error']['message'] = __('users_api_token can not be empty');
		} elseif (isset($data['users_api_token']) && !($users = $this->api->validateToken($data['users_api_token']))) {
			$response['error']['id'] = Configure::read('error.invalid_api_token');
			$response['error']['message'] = __('Invalid users_api_token');
		} else {

			// logged in user
			if (isset($data['users_api_token'])) {
				$userData = $user['User'] = $this->api->validateToken($data['users_api_token']);
				$user = new UserTable($userData);
				$userLanguage = !isset($supportedLang[$users['native_language2']]) ? $userLanguage : $supportedLang[$users['native_language2']]['iso_639_1'];
			} else if (isset($data['user_language'])) {
				if (!is_string($data['user_language'])) {
					$response['error']['id'] = Configure::read('error.user_language_must_be_string');
					$response['error']['message'] = __('users_language must be string');
					return json_encode($response);
				} else if (!isset($supportedLang[$data['user_language']])) {
					$response['error']['id'] = Configure::read('error.users_language_invalid');
					$response['error']['message'] = __('users_language is not supported');
					return json_encode($response);
				} else {
					$userLanguage = $data['user_language'];
				}
			}
			
			//get country list in memcached
			$countryList = $this->Country->getCountry();
			$response = array('country_list' => $countryList);
		}
		return json_encode($response);
	}
}