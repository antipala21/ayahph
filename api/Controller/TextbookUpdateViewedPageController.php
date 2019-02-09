<?php
App::uses('ApiCommonController', 'Controller');
class TextbookUpdateViewedPageController extends AppController {
	
	private $apiCommon = null;
	private $lessonUpdate = null;
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
		$this->apiCommon = new ApiCommonController();
	}
	
	public $uses = array(
		'UsersLastViewedTextbook',
		'TextbookConnect'
	);
	
	public function index(){
		$this->autoRender = false;
		// decoding the json request
		$inputs = json_decode($this->request->input());
		$response = array();
		// check if inputs not empty
		if (!$inputs) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else {
			// convert object request to normal request
			foreach ($inputs as $key => $value) {
				$this->request->data[$key] = $value;
			}
			
			$req = $this->request->data;
			if (!isset($req['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			} else if (!isset($req['connect_id'])) {
				$response['error']['id'] = Configure::read('error.connect_id_is_required');
				$response['error']['message'] = __('connect_id is required');
			} else if (!is_int($req['connect_id'])) {
				$response['error']['id'] = Configure::read('error.connect_id_must_be_integer');
				$response['error']['message'] = __('connect_id must be integer');
			} else {
				$user = $this->apiCommon->validateToken($req['users_api_token']);
				if (!$user){
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = __($this->apiCommon->error);
				} else {
					$response = array('result' => false);
					$validTextbook = false;
					$req['userID'] = $user['id'];
					
					//check valid textook
					$validTextbook = $this->TextbookConnect->checkValidTextbook($req['connect_id']);
					
					if ($validTextbook){
						$res = $this->UsersLastViewedTextbook->upadateLastViewedPage(
							$user['id'],
							$req['connect_id']
						);
						$res = $res ? true: false;
						$response['result'] = $res;
					}
				}
			}
		}
		return json_encode($response);
	}
	
}