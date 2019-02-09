<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTranslator','Lib');

class TeachersCounselorReviewsController extends AppController {

	public $uses = array('Teacher', 'UsersClassEvaluation', 'User');

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
		} else if (isset($data['user_language']) && !is_string($data['user_language'])) {
			$response['result'] = false;
			$response['error']['id'] = Configure::read('error.user_language_must_be_string');
			$response['error']['message'] = __('The user language must be string');
		} else if (isset($data['user_language']) && empty($data['user_language'])) {
			$response['result'] = false;
			$response['error']['id'] = Configure::read('error.invalid_user_language');
			$response['error']['message'] = __('The user language is invalid.');
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
		} else {
			$selfReviewsFlg = isset($data['selfReviews_flg'])? $data['selfReviews_flg'] : false; 

			$limit = 10;
			$pagination = (isset($data['pagination'])) ? $data['pagination'] : 1;
			$offset = $limit*($pagination-1);

			$lang = Configure::read('default.user_language');
			if (isset($user['native_language2']) && $user['native_language2'] != '') {
				$lang =  $user['native_language2'];
			} else if (!isset($data['users_api_token']) && isset($data['user_language'])) {
				$lang_param = strtolower($data['user_language']);
				$lang = in_array( $lang_param, array("ja","ko","th") ) ? $lang_param : "en";
			}

			$translationField = $this->getTranslationField($lang);

			$params = array(
				'limit' => $limit,
				'offset' => $offset,
				'selfReviewsFlg' => $selfReviewsFlg,
				'user_id' => $user['id'],
				'translated_field' => $translationField
			);
			$this->UsersClassEvaluation->openDBReplica();

			$allReviews = $this->getAllCounselEvaluation($params);
			$total = $this->getCountAllEvaluation($params);
			$this->UsersClassEvaluation->closeDBReplica();

			if ($allReviews) {
				$reviewArr = array();
				foreach($allReviews as $value) {
					// check if translation text is already in db.
					if (isset($value['UsersClassEvaluation'][$translationField]) && ($value['UsersClassEvaluation'][$translationField] == "" || empty($value['UsersClassEvaluation'][$translationField]))) {
						$googleTranslator = new myTranslator();
						$translated = $googleTranslator->translateWithOptions(array(
										'text' => trim($value['UsersClassEvaluation']['user_comment']),
										'target_language' => $lang
										)
									);
						// save translated text
						$userClassEval['id'] = $value['UsersClassEvaluation']['eval_Id'];
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

					$valueDetail = new UsersClassEvaluationTable($value['UsersClassEvaluation']);
					$valueUserDetail = new UserTable($value['User']);
					$data = array(
						'message' => $valueDetail->user_comment,
						'message_translation' => $translated,
						'rating' => is_null($valueDetail->rate) ? null : $valueDetail->rate,
						'age' 		=> ($valueUserDetail->birthday_show_flg == 1) ? $valueUserDetail->getAge() : null,
						'gender' 	=> ($valueUserDetail->gender_show_flg == 1) ? intval($valueUserDetail->gender) : null,
						'date' 		=> date('Y-m-d H:i:s', strtotime($valueDetail->created))
					);
					$reviewArr[] = $data;
				}
				$result['reviews'] = $reviewArr;
				$result['has_next'] = ($total - ($offset+$limit) > 0) ? true : false;
			} else {
				return null;
			}
		}

		return json_encode($result);
	}
	// NC-3876
	//return counselor evaluations
	public function getAllCounselEvaluation($params = array()) {
		$translatedField = 'user_comment_ja';
		if (isset($params['translated_field'])) {
			$translatedField = $params['translated_field'];
		}
		$limit = $params['limit'];
		$offset = $params['offset'];
		$selfReviewsFlg = $params['selfReviewsFlg'];
		$user_id = $params['user_id'];

		$conditions[] = array(		
			'Teacher.status = 1',
			'Teacher.counseling_flg = 1',
			'Teacher.stealth_flg = 0',
			'Teacher.counselor_order IS NOT NULL',
			'UsersClassEvaluation.user_comment <>' => ''
		);
		if ($selfReviewsFlg) {
			$conditions['UsersClassEvaluation.user_id'] = $user_id; 
		}else {
			$conditions['UsersClassEvaluation.approve_flag'] = 1; 
		} 

		$evaluationDatas = $this->UsersClassEvaluation->find('all',array(
				'fields' => array(
								'Teacher.id',
								'Teacher.status',
								'Teacher.counseling_flg',
								'Teacher.stealth_flg',
								'User.birthday',
								'User.gender',
								'User.birthday_show_flg',
								'User.gender_show_flg',
								'UsersClassEvaluation.created',
								'UsersClassEvaluation.user_comment',
								'UsersClassEvaluation.rate',
								'UsersClassEvaluation.'.$translatedField,
								'UsersClassEvaluation.id AS eval_Id'
				),
				'joins' => array(
							array(
								'type' => 'LEFT',
								'table' => 'users',
								'alias' => 'User',
								'conditions' => array('UsersClassEvaluation.user_id = User.id')
							),
							array(
								'type' => 'LEFT',
								'table' => 'teachers',
								'alias' => 'Teacher',
								'conditions' => array('UsersClassEvaluation.teacher_id = Teacher.id')
							)
				),
				'limit' => $limit,
				'offset' => $offset,
				'conditions' => $conditions,
				'order' => 'UsersClassEvaluation.created DESC',
				'recursive' => -1
			)
		);

		return $evaluationDatas;
	}

	public function getCountAllEvaluation($params = array()) {
		$selfReviewsFlg = $params['selfReviewsFlg'];
		$user_id = $params['user_id'];
		$conditions[] = array(		
			'Teacher.status = 1',
			'Teacher.counseling_flg = 1',
			'Teacher.stealth_flg = 0',
			'Teacher.counselor_order IS NOT NULL',
			'UsersClassEvaluation.user_comment <>' => ''
		);
		if ($selfReviewsFlg) {
			$conditions['UsersClassEvaluation.user_id'] = $user_id; 
		}else {
			$conditions['UsersClassEvaluation.approve_flag'] = 1; 
		} 

		$countEvaluationDatas = $this->UsersClassEvaluation->find('count',array(
				'joins' => array(
							array(
								'type' => 'LEFT',
								'table' => 'users',
								'alias' => 'User',
								'conditions' => array('UsersClassEvaluation.user_id = User.id')
							),
							array(
								'type' => 'LEFT',
								'table' => 'teachers',
								'alias' => 'Teacher',
								'conditions' => array('UsersClassEvaluation.teacher_id = Teacher.id')
							)
				),
				'conditions' => $conditions,
				'recursive' => -1
			)
		);

		return $countEvaluationDatas;		
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