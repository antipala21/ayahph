<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class PurchaseDoneController extends AppController{

	public $uses = array(
		'User', 
		'CmcodeUrl'
	);

	private $api = null;

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
		$this->api = new ApiCommonController();
	}

	public function index() {
		$this->autoRender = false;
		//save parameters
		$this->CmcodeUrl->saveParameter(array(
			'url' => $_SERVER['QUERY_STRING'],
			'server_name' => $_SERVER['SERVER_NAME'],
			'controller' => $this->params['controller']
		));	

		$get = $this->request->query;
		
		if (isset($get['api_token']) && trim($get['api_token']) <> '') {
			$apiToken = urldecode($get['api_token']);
			$apiToken = json_decode($apiToken, true);
			//get string api token
			$get['api_token'] = isset($apiToken['api_token']) ? $apiToken['api_token'] : '';
			$user = $this->api->validateToken($get['api_token']);
			if (!$user) {
				$response['error']['message'] = __('Invalid users_api_token');
				myTools::display($response);
			} else {
				if (isset($get['a8']) && !empty($get['a8'])) {
					$curlStr = "http://px.a8.net/a8fly/earnings?a8=" . $get['a8'] . "&pid=s00000014758001&so=" . $user['id'] . "&si=1-1-1-a8";
					return myTools::exeCurl($curlStr);
				} else {
					$response['error'] = __('a8 must not be empty.');
					myTools::display($response);
				}
			}	
		} 
	}
}