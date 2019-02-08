<?php
App::uses('ApiCommonController', 'Controller');
App::uses('myTwilio','Lib');
class TwilioController extends AppController{
	private $apiCommon;
	public $uses = array('LessonOnair');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
		$this->apiCommon = new ApiCommonController();
	}
	
	public function index(){
		$this->autoRender = false;
		/* decode request data */
		@$data = json_decode($this->request->input(),true);
		if ($data) {
			foreach($data as $key => $value) {
				$this->request->data[$key] = $value;
			}
		}
		
		$response = array();
		
		$data = $this->request->data;
		$usersToken = $data['users_api_token'];
		
		/* validate users token */
		/* users_api_token not set */
		if (!isset($usersToken)) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else {
			/* validate user token */
			$user = $this->apiCommon->validateToken($usersToken);

			// if user does not exist
			if (!$user){
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __($this->apiCommon->error);
				
			// else use twilio
			} else {
				// - get lesson onair information
				$lessonOnair = $this->LessonOnair->find('first', array(
					'conditions' => array(
						'user_id' => $user['id']
					),
					'recursive' => -1
				));

				$twilio = new myTwilio();
				$twilioIceServers = $twilio->generateIceServers("api");
				$skyWayId = $twilio->generatePeerID($lessonOnair['LessonOnair']['chat_hash'], 'api');
				$skyWayCreds = json_decode($twilio->generateHash($skyWayId));
				
				// set ice server
				$response['is_using_skyway'] = $twilioIceServers['is_using_skyway']; 
				$response['skyway_key'] = $twilioIceServers['skyway_key'];
				$response['skyway_id'] = $skyWayId;
				$response['skyway_credentials'] = $skyWayCreds;
				$response['video_bandwidth'] = $twilioIceServers['video_bandwidth'];
				$response['audio_bandwidth'] = $twilioIceServers['audio_bandwidth'];
			}
		}
		
		// return response
		return json_encode($response);
	}
}