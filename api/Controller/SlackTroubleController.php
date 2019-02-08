<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class SlackTroubleController extends AppController {

	public $uses = array(
		'Teacher',
		'LessonOnair', 
		'LessonOnairsLog'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow('index');
	}

	public function index() {
		$response = array();
		$data = json_decode($this->request->input(), true);
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request.');
			return json_encode($response);
		} else {
			if (!isset($data['users_api_token']) && empty($data['users_api_token'])) { //users_api_token validation
				$response['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
				$response['error']['message'] = __('users_api_token can not be empty');
				return json_encode($response);
			} elseif (!is_string($data['users_api_token'])) {
				$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
				$response['error']['message'] =__('users_api_token must be string');
				return json_encode($response);
			}
			//check user
		    $api = new ApiCommonController();
		    $user = $api->findApiToken($data['users_api_token']);
		    //validate user
		    if (!$user) {
	          	$response['error']['id'] = Configure::read('error.invalid_api_token');
	        	$response['error']['message'] = $api->error;
	        	return json_encode($response);
		    }

			if (empty($data['chat_hash']) || !isset($data['chat_hash'])) { //chat_hash validation
				$response['error']['id'] = Configure::read('error.chat_hash_is_required');
				$response['error']['message'] = __('chat_hash is required.');
				return json_encode($response);
			} elseif (!is_string($data['chat_hash'])) {
				$response['error']['id'] = Configure::read('error.chat_hash_must_be_string');
				$response['error']['message'] = __('chat_hash must be string');
				return json_encode($response);
			} else {
				$lessonData = LessonOnairTable::findLessonData(array(
					'fields' => array(
						'id',
						'teacher_id',
						'user_id',
						'chat_hash',
						'lesson_system_trouble',
						'user_agent'
					),
					'conditions' => array(
						'chat_hash' => $data['chat_hash'],
						'user_id' => $user['id']
					)
				));

				//if no lesson data found
				if (!$lessonData) {
					$response['error']['id'] = Configure::read('error.invalid_chat_hash_for_the_users_api_token_requested');
					$response['error']['message'] = 'Invalid chat_hash for the users_api_token requested.';	
					return json_encode($response);
				} elseif ($lessonData['data']['lesson_system_trouble'] == 0) {
					//update trouble
					$model = $this->$lessonData['model'];
					if ($model->read(null, $lessonData['data']['id'])) {
						$model->set('lesson_system_trouble', 1);
						$model->save();
					}
				}
				//assign lesson data
				$lessonData = $lessonData['data'];
				//fetch data for posting
				$problemData = $this->LessonOnairsLog->countProblematicLesson(array(
					'userId' => $user['id'],
					'teacherId' => $lessonData['teacher_id'],
					'lessonData' => $lessonData
					));

				//teacher name
				$nameData = $this->Teacher->find('first', array(
					'fields' => 'Teacher.name',
					'conditions' => 'Teacher.id = ' . $lessonData['teacher_id'],
					'recursive' => -1
				));
				//content is empty
				if (isset($data['contents'])) {
					$data['contents'] = trim($data['contents']);
					if (strlen($data['contents']) == 0) {
						$data['contents'] = '[the user did not input some comments]';
					}
				} else {
					$data['contents'] = '[the user did not input some comments]';
				} 
				//OS
				$os = $this->getOsFromUA($lessonData['user_agent']);
				// send to slack
				$slack = new mySlack();
				// set the channel, change to #nc-monitoring
				$slack->channel  = myTools::checkChannel("#nc-voice-trouble", "#nc-voice-trouble-dev");
				//type
				$slack->text = "```種別：通信トラブル({$os})\n";
				// set os and problem count
				$slack->text .= "本日：{$problemData['totalProblemCount']}件目（{$problemData['userAgentStr']}）\n";

				// set lecturer id and number of lesson
				$slack->text .= "講師ID：{$lessonData['teacher_id']} ({$nameData['Teacher']['name']})（本日{$problemData['teacherCount']}回目）\n";

				// set member id and number of lesson
				$slack->text .= "会員ID：{$user['id']} ({$user['nickname']})（本日{$problemData['userCount']}回目）\n";
				// set contents
				$slack->text .= "内容：{$data['contents']}\n";
				// set chat hash
				$slack->text .= "chathash: https://{$_SERVER['HTTP_HOST']}/admin/lesson-history?chat_hash={$data['chat_hash']}\n";
				//information
				$slack->text .= (isset($data['information']) ? '接続情報："' . $data['information'] . '"' : '') . "```";
				// set slack user name
				$slack->username = "NativeCamp";

				if ($slack->sendSlack() == 200) {
					return json_encode(array('posted' => true));
				} 
			}
		}
		return json_encode(array('posted' => false));
	}

	/**
	* get os from user-agent 
	*/
	private function getOsFromUA($ua) {
		//Detect special conditions devices
		$iPod    = stripos($ua, "iPod");
		$iPhone  = stripos($ua, "iPhone");
		$iPad    = stripos($ua, "iPad");
		$Android = stripos($ua, "Android");
		$Kindle = stripos($ua, "KF");

		if ($iPod !== false || $iPhone !== false || $iPad !== false) {
		    return 'iOS';
		} elseif ($Android !== false) {
			if ($Kindle !== false) {
				return 'Kindle';
			}
		    return 'Android';
		} else {
			return 'Other';
		}
	}
}