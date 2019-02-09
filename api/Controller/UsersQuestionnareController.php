<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTools','Lib');
class UsersQuestionnareController extends AppController {

	public $uses = array('StudentQuestionnaire');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;

		@$data = json_decode($this->request->input(),true);

		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (isset($data['pagination']) && !is_int($data['pagination'])) {
			$response['error']['id'] = Configure::read('error.pagination_must_be_integer');
			$response['error']['message'] = __('pagination must be integer');
		} else {
			$apiCommon = new ApiCommonController();

			$user = $apiCommon->validateToken($data['users_api_token']);

			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $apiCommon->error;
				return json_encode($response);
			}
			if($data['question_type'] == 1){
				$conditions = array('StudentQuestionnaire.type'=>1);
			}elseif($data['question_type'] == 2){
				$conditions = array('StudentQuestionnaire.type'=>2);
			}
			$questionnare = $this->StudentQuestionnaire->useReplica()->find('first', array(
				'order' => array(
					'id' => 'DESC'
				),
				'conditions' => $conditions,
				'fields'=>array(
					'StudentQuestionnaire.type',
					'StudentQuestionnaire.status',
					'StudentQuestionnaire.title',
					'StudentQuestionnaire.contents',
					'StudentQuestionnaire.url'
				))
			);
			if ($questionnare['StudentQuestionnaire']['status'] == 0) {
				$response['error']['id'] = Configure::read('error.questionnare_status_is_off');
				$response['error']['message'] = __('questionnare status is off');
			}else{
				$response = array(
	              'type'     => $questionnare['StudentQuestionnaire']['type'],
	              'status'   => $questionnare['StudentQuestionnaire']['status'],
	              'title'    => $questionnare['StudentQuestionnaire']['title'],
	              'contents' => $questionnare['StudentQuestionnaire']['contents'],
	              'url'      => $questionnare['StudentQuestionnaire']['url']
	            );
			}


		}
		return json_encode($response);
	}
}
