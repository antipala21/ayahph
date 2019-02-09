<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTranslator','Lib');

class TeachersReviewsController extends AppController {

	public $uses = array('Teacher', 'UsersClassEvaluation');
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {
		$this->autoRender = false;
		@$data = json_decode($this->request->input(),true);
		$inputs = array();
		if ($data) {
			foreach($data as $key => $value) {
				$inputs[$key] = $value;
			}
		}
		$api = new ApiCommonController();
		if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
			$result['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$result['error']['message'] =  __('users_api_token must be string');
		} else if (isset($data['users_api_token']) && trim($data['users_api_token']) == "") {
			$result['error']['id'] = Configure::read('error.users_api_token_can_not_be_empty');
			$result['error']['message'] =  __('users_api_token can not be empty');
		} else if (!isset($data['teachers_id']) || empty($data['teachers_id']) && trim($data['teachers_id']) == "") {
			$result['error']['id'] = Configure::read('error.teachers_id_is_required');
			$result['error']['message'] =  __('teachers_id is required');
		} else if (isset($data['user_language']) && !is_string($data['user_language'])) {
			$response['result'] = false;
			$response['error']['id'] = Configure::read('error.user_language_must_be_string');
			$response['error']['message'] = __('The user language must be string');
		} else if (isset($data['user_language']) && empty($data['user_language'])) {
			$response['result'] = false;
			$response['error']['id'] = Configure::read('error.invalid_user_language');
			$response['error']['message'] = __('The user language is invalid.');
		} else if (is_string($data['teachers_id'])) {
			$result['error']['id'] = Configure::read('error.teachers_id_must_be_integer');
			$result['error']['message'] =  __('teachers_id must be integer');
		} else if (isset($data['pagination']) && is_string($data['pagination'])) {
			$result['error']['id'] = Configure::read('error.pagination_must_be_integer');
			$result['error']['message'] =  __('pagination must be integer');
		} else if (isset($data['pagination']) && $data['pagination'] <= 0) {
			$result['error']['id'] = Configure::read('error.pagination_must_be_greater_than_zero');
			$result['error']['message'] = __('pagination must be greater than 0');
		} else if (isset($data['selfReviews_flg']) && !is_bool($data['selfReviews_flg'])) {
			$result['error']['id'] = Configure::read('error.selfReviews_flg_must_be_boolean');
			$result['error']['message'] = __('selfReviews_flg must be boolean');
		} else if (empty($data['users_api_token']) && $data['selfReviews_flg']) {
			$result['error']['id'] = Configure::read('error.selfReviews_flg_cannot_be_set_to_true_if_no_users_api_token');
			$result['error']['message'] = __('selfReviews_flg cannot be set to true if no users_api_token');
		} else if (isset($data['users_api_token']) && !($user = $api->validateToken($data['users_api_token']))) {
			$result['error']['id'] = Configure::read('error.invalid_api_token');
			$result['error']['message'] =  __('Invalid users_api_token');
		} else if (!$this->checkTeacher($data['teachers_id'])) {
			$result['error']['id'] = Configure::read('error.invalid_teachers_id');
			$result['error']['message'] = __('Invalid teachers_id');
		} else {
			$api = new ApiCommonController();

			if (!empty($data['users_api_token']) && $api->checkBlocked($data['teachers_id'],$user['id'])) {
				$response['error']['id'] = Configure::read('error.missing_teacher');
				$response['error']['message'] = __($api->missing_teacher);
				return json_encode($response);
			}
			$conditions = array(
				'UsersClassEvaluation.teacher_id' => $data['teachers_id'],
				'UsersClassEvaluation.user_comment <>' => ''
			);
			$selfReviewsFlg = isset($data['selfReviews_flg'])? $data['selfReviews_flg'] : false; 
			if ($selfReviewsFlg) {
				$conditions['UsersClassEvaluation.user_id'] = $user['id']; 
			}else {
				$conditions['UsersClassEvaluation.approve_flag'] = 1; 
			}

			$lang = Configure::read('default.user_language');
			if (isset($user['native_language2']) && $user['native_language2'] != '') {
				$lang =  $user['native_language2'];
			} else if (!isset($data['users_api_token']) && isset($data['user_language'])) {
				$lang_param = strtolower($data['user_language']);
				$lang = in_array( $lang_param, array("ja","ko","th") ) ? $lang_param : "en";
			}
			
			$limit = 10;
			$pagination = (isset($data['pagination'])) ? $data['pagination'] : 1;
			$offset = $limit*($pagination-1);
			$this->UsersClassEvaluation->openDBReplica();
			$total = $this->UsersClassEvaluation->find('count', array('conditions' => $conditions));

			$translationField = $this->getTranslationField($lang);
			
			$review = $this->getReviews($conditions, $limit, $offset, $translationField);
			$this->UsersClassEvaluation->closeDBReplica();
			
			if ($review) {
				$reviewArr = array();
				foreach($review as $key => $value) {
					if (isset($value['UsersClassEvaluation'][$translationField]) && ($value['UsersClassEvaluation'][$translationField] == "" || empty($value['UsersClassEvaluation'][$translationField]))) {
						$googleTranslator = new myTranslator();
						$translated = $googleTranslator->translateWithOptions(array(
										'text' => trim($value['UsersClassEvaluation']['user_comment']),
										'target_language' => $lang
										)
									);
						// save translated text
						$userClassEval['id'] = $value['UsersClassEvaluation']['id'];
						$userClassEval[$translationField] = $translated;
						$this->UsersClassEvaluation->clear();
						$this->UsersClassEvaluation->recursive = -1;
						$this->UsersClassEvaluation->read(array_keys($userClassEval), $userClassEval['id']);
						$this->UsersClassEvaluation->set($userClassEval);
						$this->UsersClassEvaluation->save();
					} else {
						$translated = $value['UsersClassEvaluation'][$translationField];
					}
					// replace &#39; to apostrophe
					if (strpos($translated, "&#39;") !== false) {
						$translated = str_replace("&#39;", "'", $translated);
					}
					$data = array(
						'message' 	=> $value['UsersClassEvaluation']['user_comment'],
						'message_translation' => $translated,
						'rating' => is_null($value['UsersClassEvaluation']['rate']) ? null : $value['UsersClassEvaluation']['rate'],
						'age' 		=> ($value['users']['birthday_show_flg'] == 1) ? $this->calcAge($value['users']['birthday']) : null,
						'gender' 	=> ($value['users']['gender_show_flg'] == 1) ? intval($value['users']['gender']) : null,
						'date' 		=> date('Y-m-d H:i:s', strtotime($value['UsersClassEvaluation']['created']))
					);
					array_push($reviewArr, $data);
				}
				$result['reviews'] = $reviewArr;
				$result['has_next'] = ($total - ($offset+$limit) > 0) ? true : false;
			} else {
				return null;
			}
		}
		return json_encode($result);
	}

	private function getReviews($conditions, $limit, $offset, $translationField) {
		return $this->UsersClassEvaluation->find('all', array(
				'joins'	=> array(
					array(
						'table' => 'users',
						'conditions' => array(
							'UsersClassEvaluation.user_id = users.id'
						)
					)
				), 'conditions' => $conditions,
				'fields' => array(
					'UsersClassEvaluation.id',
					'UsersClassEvaluation.'.$translationField,
					'UsersClassEvaluation.user_comment',
					'UsersClassEvaluation.rate',
					'users.birthday',
					'users.gender',
					'users.birthday_show_flg',
					'users.gender_show_flg',
					'UsersClassEvaluation.created'
				), 'limit' => $limit,
				'offset' => $offset,
				'order'	=> array('UsersClassEvaluation.created DESC')
			)
		);
	}

	private function calcAge($birthday) {
		$present	= new DateTime();
		$fromTime = new DateTime($birthday);
		$diff = $fromTime->diff($present);
		return intval($diff->format('%y'));
	}

	private function checkTeacher($teacherId) {
		$this->Teacher->id = $teacherId;
		return $this->Teacher->exists();
	}

	private function getTranslationField($lang = null) {
		switch ($lang) {
			case 'en':
				return 'user_comment_en';
				break;
			case 'ja':
				return 'user_comment_ja';
				break;
			case 'ko':
				return 'user_comment_ko';
				break;
			case 'th':
				return 'user_comment_th';
				break;
			default:
				return 'user_comment_en';
				break;
		}
	}

}