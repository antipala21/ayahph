<?php
/********************************
*																*
*	Memo List for API 						*
*	Author: John Mart Belamide		*
*	August 2015										*
*																*
********************************/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myTools','Lib');
class MemoListController extends AppController {

	public $uses = array(
		'UsersMemo', 
		'Teacher'
	);

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

			$search = isset($data['text']) ? $data['text'] : '';

			$restrictedChars = array('/_/','/%/');
			foreach ($restrictedChars as $char) {
				$search = preg_replace($char,'\\'.substr($char,1,1),$search);
			}
			
			$limit = 20;
			$page = (!empty($data['pagination']) && (int)$data['pagination'] >= 1) ? (int)$data['pagination'] : 1;
			$offset = ($page - 1) * $limit;
			$offsetNext = ($page) * $limit;

			$conditions = array(
				'UsersMemo.user_id'	=>	$user['id'],
				'UsersMemo.memo <>'	=>	'',
				'UsersMemo.memo LIKE' => '%'.$search.'%'
			);
			$data = $this->UsersMemo->find('all', array(
				'fields' => array(
						'UsersMemo.id',
						'UsersMemo.created',
						'UsersMemo.memo',
						'User.nickname',
						'Teacher.name',
						'Teacher.counseling_flg',
						),
				'conditions' => $conditions,
				'joins' => array(
						array(
							'type'  => 'LEFT',
							'table' => 'lesson_onairs_logs',
							'alias' => 'LessonOnairsLog',
							'conditions' => array('UsersMemo.chat_hash = LessonOnairsLog.chat_hash', 'UsersMemo.user_id = LessonOnairsLog.user_id')
						),
						array(
							'type'  => 'LEFT',
							'table' => 'users',
							'alias' => 'User',
							'conditions' => array('UsersMemo.user_id = User.id')
						),
						array(
							'type'  => 'LEFT',
							'table' => 'teachers',
							'alias' => 'Teacher',
							'conditions' => array('Teacher.id = LessonOnairsLog.teacher_id')
						)
					),
				'order'	=> array('UsersMemo.created DESC'),
				'limit'	=> $limit,
				'offset'=> $offset
				)
			);

			if (!$data) {
				return null;
			} else {
				$hasNext = $this->UsersMemo->useReplica()->find('first',array(
					'conditions' =>  $conditions,
					'offset'	 => $offsetNext
					)
				);
			}

			$list = array();

			//for counselor detail
			$counselorDetail = array();
			if ($data) {
				$counselorDetail = $this->Teacher->getDefaultCounselorDetail();
			}

			// Put all data's in a proper assiociate name
			foreach($data as $row) {
				if ($counselorDetail && $row['Teacher']['counseling_flg']) {
					$row['Teacher']['name'] = $counselorDetail['Teacher']['name'];
				}

				$memo = array(
					'lesson_id'	=> (int)$row['UsersMemo']['id'],
					'user'		=> $row['User']['nickname'],
					'teacher'	=> $row['Teacher']['name'],
					'created'	=> $row['UsersMemo']['created'],
					'text'		=> $row['UsersMemo']['memo']
				);
				array_push($list,$memo);
			}

			$response['memos'] 		= $list;
			$response['has_next'] = $hasNext ? true : false;

		}
		return json_encode($response);
	}
}
