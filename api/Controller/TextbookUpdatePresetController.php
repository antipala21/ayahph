<?php
App::uses('ApiCommonController', 'Controller');
class TextbookUpdatePresetController extends AppController{
	
	private $apiCommon;
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow('index');
		$this->apiCommon = new ApiCommonController();
	}
	
	public $uses = array(
		'UsersLastViewedTextbook',
		'TextbookConnect'
	);
	
	private $requestData = null;
	
	public function index(){
		$requiredArr = array(
			'users_api_token',
			'class_id',
			'chapter_id',
			'lesson_text_id',
			'textbook_type'
		);
		$response = null;
		
		if ($this->request->is('post')){
			@$data = json_decode($this->request->input(),true);
			
			// validation for the array request
				if ($data) {
					// validation for users_api_token
					if (!isset($data['users_api_token'])) {
						$response['error']['id'] = Configure::read('error.users_api_token_is_required');
						$response['error']['message'] = __('users_api_token is required');
						// validation for class_id
					} else if (!isset($data['connect_id'])) {
						$response['error']['id'] = Configure::read('error.connect_id_is_required');
						$response['error']['message'] = __('connect_id is required');
						// validation for chapter_id
					} else if (!is_int($data['connect_id'])) {
						$response['error']['id'] = Configure::read('error.connect_id_must_be_integer');
						$response['error']['message'] = __('connect_id must be integer');
					} else {
						$user = $this->apiCommon->validateToken($data['users_api_token']);
						if (!$user){
							$response['error']['id'] = Configure::read('error.invalid_api_token');
							$response['error']['message'] = __($this->apiCommon->error);
						} else {
							$connectId = $data['connect_id'];
							if ($this->TextbookConnect->checkValidTextbook($connectId)) {
								$save = $this->UsersLastViewedTextbook->savePresetTextbook(array(
											'userId' => $user['id'],
											'connectId' => $connectId
								));
								$response['result'] = $save? true : false;
							} else {
								$response['result'] = false;
							}
						}
					}
			} else {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
			}
			return json_encode($response);
		}
	}
}