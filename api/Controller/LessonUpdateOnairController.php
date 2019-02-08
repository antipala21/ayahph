<?php
App::uses('ApiCommonController', 'Controller');
class LessonUpdateOnairController extends AppController{
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->Auth->allow('index');
		$this->autoRender = false;
		$this->apiCommon = new ApiCommonController();
	}
	
	private $apiCommon = null;
	
	public $uses = array(
		'LessonOnair',
		'UsersLastViewedTextbook',
		'Textbook',
		'TextbookConnect'
	);
	
	public function index(){
		$apiCommon = $this->apiCommon;
		$response = array();
		if ($this->request->is('post')){
			@$data = json_decode($this->request->input(), true);
			
			if ($data){
				// validate
				if (!isset($data['users_api_token'])) {
					$response['error']['id'] = Configure::read('error.users_api_token_is_required');
					$response['error']['message'] = __('users_api_token is required');
				} else if (!isset($data['chat_hash'])) {
					$response['error']['id'] = Configure::read('error.chat_hash_is_required');
					$response['error']['message'] = __('chat_hash is required');
				} else if (!isset($data['connect_id'])) {
					$response['error']['id'] = Configure::read('error.connect_id_is_required');
					$response['error']['message'] = __('connect_id is required');
				} else if (!is_string($data['chat_hash'])) {
					$response['error']['id'] = Configure::read('error.chat_hash_must_be_string');
					$response['error']['message'] = __('chat_hash must be string');
				} else if (!is_int($data['connect_id'])) {
					$response['error']['id'] = Configure::read('error.connect_id_must_be_integer');
					$response['error']['message'] = __('connect_id must be integer');
				} else {
					// get user data
					$user = $apiCommon->validateToken($data['users_api_token']);
					// validate user
					if (!$user){
						$response['error']['id'] = Configure::read('error.invalid_api_token');
						$response['error']['message'] = __($apiCommon->error);
					} else {
						// user validation success

						$response['result'] = false;

						// set findTBResult flag if textbook is found
						$findTBResult = false;

						$lessonOnair = $this->LessonOnair->find('first',array(
								'conditions' => array(
									'LessonOnair.user_id' => $user['id'],
									'LessonOnair.chat_hash' => $data['chat_hash']
								),
								'fields' => array(
									'LessonOnair.id',
									'LessonOnair.teacher_id',
									'LessonOnair.connect_id',
									'LessonOnair.chat_hash'
								),
								'recursive' => -1,
							)
						);

						if ($lessonOnair) {
							if (empty($lessonOnair['LessonOnair']['connect_id'])) {
								$envFlag = 'all';
								$onGoingClass = false;
							} else {
								$envFlag = 'lesson_now';
								$onGoingClass = true;
							}
							
							$getTextbookArr = array(
								'teacher_id' => $lessonOnair['LessonOnair']['teacher_id'],
								'env_flag' => $envFlag,
								'user_id' => $user['id'],
								'select_method' => 'first',
								'auto_select' => 'off',
								'on_going_class' => $onGoingClass,
								'connect_id' => $data['connect_id']
							);
							$textbookData = $this->Textbook->getTextbooks($getTextbookArr);
							$textbookArr = $textbookData['res_data'];
							if ($textbookArr) {
								if ($textbookArr['TextbookCategory']['display_flag'] == 1) {
									$findTBResult = true;
								}
							}

							//disable all textbooks on callan lesson
							if (!empty($lessonOnair['LessonOnair']['connect_id'])) {
								$textbook = $this->TextbookConnect->find('first', array(
									'fields' => array(
										'TextbookCategory.id',
										'TextbookCategory.textbook_category_type'
									),
									'conditions' => array(
										'TextbookConnect.id' => $lessonOnair['LessonOnair']['connect_id']
									),
									'joins' => array(
										array(
											'table' => 'textbook_categories',
											'alias' => 'TextbookCategory',
											'type' => 'INNER',
											'conditions' => 'TextbookCategory.id = TextbookConnect.category_id'
										)
									)
								));
								if (isset($textbook['TextbookCategory']['textbook_category_type']) && $textbook['TextbookCategory']['textbook_category_type'] == 2) {
									$findTBResult = false;
								}
								// eiken special condition
								if( isset($textbook['TextbookCategory']['id']) && in_array( $textbook['TextbookCategory']['id'], Configure::read('eiken_category_ids') ) ) {
									$findTBResult = true;
								}
							}

							// finding the textbook is success / textbook is valid
							if ($findTBResult){
								$update = array(
									'connect_id' => $data['connect_id']
								);
								$this->LessonOnair->read(null, $lessonOnair['LessonOnair']['id']);
								$this->LessonOnair->set($update);
								if ($this->LessonOnair->save()){
									$res = $this->UsersLastViewedTextbook->upadateLastViewedPage(
										$user['id'],
										$data['connect_id']
									);
									$res = $res ? true: false;
									$response['result'] = $res;
								}
							}
						}
					}
				}
			} else {
				$response['error']['id'] = Configure::read('error.invalid_request');
				$response['error']['message'] = __('Invalid request');
			}
		} else {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		}
		return json_encode($response);
	}
}