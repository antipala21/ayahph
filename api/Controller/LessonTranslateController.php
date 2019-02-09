<?php
/****************************
 * Lesson Translate for API
 * Author : Karl Vincent Lim / Ado
 * March 2017   
 *****************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTranslator','Lib');

class LessonTranslateController extends AppController {

	public $uses = array('CountryCode');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->autoRender = false;
        $this->Auth->allow('translate');
    }

    public function translate() {

        if ($this->request->is('post')) {
            $data = json_decode($this->request->input(), true);
            $response['result'] = true;
			$params = array();
            if (empty($data)) {
                $response['result'] = false;
                $response['error']['id'] = Configure::read('error.invalid_request');
                $response['error']['message'] = __('Invalid request.');
			} else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['error']['message'] = __('The users_api_token must be string request.');
			} else if (isset($data['users_api_token']) && empty($data['users_api_token'])) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
				$response['error']['message'] = __('The users api token can not be empty.');
			} else if (isset($data['user_language']) && !is_string($data['user_language'])) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.user_language_must_be_string');
				$response['error']['message'] = __('The user language must be string');
			} else if (isset($data['user_language']) && empty($data['user_language'])) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.invalid_user_language');
				$response['error']['message'] = __('The user language is invalid.');
			} else if ((!isset($data['users_api_token']) && !isset($data['user_language'])) || (!isset($data['users_api_token']) && empty($data['user_language']))) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.user_language_or_users_api_token_is_required');
				$response['error']['message'] = __('The user language or users api token isrequired.');
			} else if (isset($data['phrase']) && !is_string($data['phrase'])) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.phrase_must_be_string');
				$response['error']['message'] = __('The phrase must be string.');
			} else if (!isset($data['phrase']) || empty($data['phrase'])) {
				$response['result'] = false;
				$response['error']['id'] = Configure::read('error.phrase_is_required');
				$response['error']['message'] = __('The phrase is required.');
			} else if (
				(isset($data['users_api_token']) && !isset($data['user_language']))
				|| (isset($data['users_api_token']) && isset($data['user_language'])) // if both are availabe, use native_language2
			) {
				$user_api_token = $data['users_api_token'];
				$api = new ApiCommonController();
				$user = $api->findApiToken($user_api_token);
				if (!array_key_exists('id', $user)) {
					$response['result'] = false;
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = $api->error;
				} else {
					$params['user_language'] = isset($user['native_language2']) && !empty($user['native_language2']) ? $user['native_language2'] : 'ja'; // native_language2 default
				}
			} else if (!isset($data['users_api_token']) && isset($data['user_language'])) {
				$params['user_language'] = $data['user_language'];
			}

			// - debug
			$this->log(__METHOD__ . " step 1 -> checking translation -> " . json_encode($params), "debug");

            if ($response['result'] !== false) {
                $params['text'] = trim($data['phrase']);

                // - debug
				$this->log(__METHOD__ . " step 2 -> checking translation -> " . json_encode($params), "debug");

                if (empty($params['text'])) {
                    $response['result'] = false;
                    $response['error']['id'] = Configure::read('error.invalid_phrase');
                    $response['error']['message'] = __('Invalid phrase.');
                } else {
                  // NC-4904 : check if the user_language is currently supported.
                  $suportedlang = $this->CountryCode->commonMemcachedCountryCode();
                  $params['supported_lang'] = isset($suportedlang[$params['user_language']]) ? true : false;

                  $google = new myTranslator();
                  $translate = $google->api_translate($params);

                 	// - debug
					$this->log(__METHOD__ . " step 3 -> checking translation -> " . json_encode($translate), "debug");

                  if (isset($translate) && $translate) {
                      $response["translated"] = true;

                      // replace &#39; to apostrophe
                      if (strpos($translate, "&#39;") !== false) {
                        $translate = str_replace("&#39;", "'", $translate);
                      }

                      $response["phrase"] = $translate;
                      unset($response['result']);

                      // - debug
						$this->log(__METHOD__ . " step 4 -> checking translation -> " . json_encode($response), "debug");
                  }
                }
            }
            return json_encode($response);  
        }
    }
}
