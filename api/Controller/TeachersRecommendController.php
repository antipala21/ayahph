<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeachersRecommendController extends AppController {
	public $uses = array(
		'TeacherRanking',
		'Teacher'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
		$this->autoRender = false;
		$response = array();

		if ($this->request->is('post')) {
			@$data = json_decode($this->request->input(), true);
			$validate = $this->validates($data);
			$apiVersion = isset($data['api_version'])? $data['api_version'] : 0;
			if ($validate['error']) {
				$response['error'] = $validate['content'];
			} else {
				$response['teachers'] = $this->getRecommedTeachers($validate['userData'],$apiVersion);
			}
			return json_encode($response);
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid Request');
		}
	}

	private function getRecommedTeachers ($userData = null,$apiVersion) {
		$result = array();
		$ids = $this->getRandomTeachers($userData['id']);

		if (!$ids || empty($ids)) {
			return;
		}

		$Teachers = $this->Teacher->find('all', array(
			'fields' => array(
				'Teacher.id',
				'Teacher.jp_name',
				'Teacher.name',
				'Teacher.image_url',
				'Teacher.lesson_count',
				'TeacherRatingsLesson.ratings',
				'UsersFavorite.id',
				'CountryCode.id',
				'CountryCode.country_name'
			),
			'joins' => array(
				array(
					'table' => 'teacher_ratings_lessons',
					'alias' => 'TeacherRatingsLesson',
					'type' => 'LEFT',
					'conditions' => 'TeacherRatingsLesson.teacher_id = Teacher.id'
				),
				array(
					'table' => 'users_favorites',
					'alias' => 'UsersFavorite',
					'type' => 'LEFT',
					'conditions' => 'Teacher.id = UsersFavorite.teacher_id AND UsersFavorite.user_id = ' . $userData['id']
				),
				array(
					'table' => 'country_codes',
					'alias' => 'CountryCode',
					'conditions' => 'CountryCode.id = Teacher.homeland2',
					'type' => 'INNER'
				)
			),
			'conditions' => array(
				'Teacher.id' => $ids
			),
			'order' => 'RAND()'
		));
		if ($Teachers) {
			foreach ($Teachers as $teacher) {
				$teacherDetail = new TeacherTable($teacher['Teacher']);
				$countries = strtolower($teacher['CountryCode']['country_name']);
				$explodeCountries = explode(' ', $countries);
				$implode = implode('_',$explodeCountries);
				
				$checkCountryCode = (!$userData['native_language2']) ? 'ja' : $userData['native_language2'];
				$getUserSettingLanguage = ( $checkCountryCode == 'ja' ) ? $teacherDetail->jp_name : '' ;
				
				$result[] = array(
					"id" => $teacherDetail->id,
					"name" => $getUserSettingLanguage,
					"name_ja" => $teacherDetail->jp_name,
					"name_eng" => $teacherDetail->name,
					"rating" => isset($teacher['TeacherRatingsLesson']['ratings']) ? $teacher['TeacherRatingsLesson']['ratings'] : '-',
					"lessons" => (int)$teacherDetail->lesson_count,
					"favorite" => isset($teacher['UsersFavorite']['id']) ? true : false,
					"nationality_id" => (int)$teacher['CountryCode']['id'],
					"image_main" => $teacherDetail->getImageUrl(),
					'country_image' => FULL_BASE_URL . "/user/images/flag/" . $implode . ".png"
				);
			}
			
			unset($result[0]['name_ja']); 
			unset($result[1]['name_ja']); 
			unset($result[2]['name_ja']); 
			
		} else {
			return;
		}
		return $result;
	}

	// get 3 random teacher id from ranking teacher reservation count
	private function getRandomTeachers ($userId) {
		$currentYear = date('Y');
		$currentMonth = date('n');
		$currentDay = date('j');

		if ($currentDay <= 15) {
			$day = 16;
			$month = $currentMonth - 1;
			if ($month == 0) {
				$month = 12;
				$year = $currentYear - 1;
			} else {
				$month = $month;
				$year = $currentYear;
			}
		} else {
			$day = 1;
			$month = $currentMonth;
			$year = $currentYear;
		}

		//fetch hidden teacher
		$blockList = BlockListTable::getBlocks($userId);

		//set start date
		$startDate = date('Y-m-d', strtotime($year . '/' . $month . '/' . $day));
		$conditions = array(
			'TeacherRanking.rank_in_flag' => 1,
			'TeacherRanking.dummy_data_flag' => 0,
			'TeacherRanking.start_date' => $startDate,
			'TeacherRanking.rate > 0',
			'NOT' => array('TeacherRanking.teacher_id' => $blockList)
		);

		$random_teachers = $this->TeacherRanking->find('all', array(
			'fields' => array(
				'TeacherRanking.teacher_id'
			),
			'conditions' => $conditions,
			'group' => 'TeacherRanking.id',
			'order' => 'TeacherRanking.reserve_count DESC',
			'limit' => 50
		));

		$random3 = array();
		if ($random_teachers) {
			// get only 3 random teacher id's
			$random_keys = array_rand($random_teachers,3);
			$random3[] = $random_teachers[$random_keys[0]]['TeacherRanking']['teacher_id'];
			$random3[] = $random_teachers[$random_keys[1]]['TeacherRanking']['teacher_id'];
			$random3[] = $random_teachers[$random_keys[2]]['TeacherRanking']['teacher_id'];
		}
		return $random3;
	}

	private function validates ($data = array()) {
		$response = array('error' => true, 'content' => null);
		if (!$data) {
			$response['content']['id'] = Configure::read('error.invalid_request');
			$response['content']['message'] = __('Invalid request.');
		} elseif (!isset($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} elseif (isset($data['users_api_token']) && trim($data['users_api_token']) == '') {
			$response['content']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$response['content']['message'] = __('users_api_token can not be empty');
		} elseif (isset($data['users_api_token']) && $data['users_api_token']) {
			$user_api_token = $data['users_api_token'];
			$api = new ApiCommonController();
			$user = $api->findApiToken($user_api_token);
			if (isset($user) && is_array($user)) {
				// check if has user
				if (!array_key_exists('id', $user)) {
					$response['content']['id'] = Configure::read('error.invalid_api_token');
					$response['content']['message'] = $api->error;
				} else {
					$response['error'] = false;
					$response['userData'] = $user;
				}
			} else {
				$response['content']['id'] = Configure::read('error.invalid_api_token');
				$response['content']['message'] = $api->error;
			}
		} elseif (!empty($data['users_api_token']) && !is_string($data['users_api_token'])) {
			$response['content']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['content']['message'] = __('The users_api_token must be string request.');
		} else {
			$response['error'] = false;
		}
		return $response;
	}

}