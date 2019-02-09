<?php
/**
 * Word book
 * Author : Frank Code ( Prank )
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class WordBooksController extends AppController {
	public $uses = array( 'User', 'LessonWord' );

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('create','lists','update','delete');
		$this->autoRender = false;
	}
	public function create() {
		$this->autoRender = false;

		@$data = json_decode($this->request->input(),true);
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if ( !isset($data['words']) || !isset($data['meaning']) ) {
			$response['error']['id'] = Configure::read('error.invalid_token');
			$response['error']['message'] = __('Invalid token.');
		} else if ( preg_match ("/\*/",$data['words']) ) {
			$response['error']['id'] = Configure::read('error.fill_out_by_alphabet');
			$response['error']['message'] = __('Fill out by alphabet.');
		} else if ( $data['words'] == "" || $data['meaning'] == "" || strlen( trim($data['words']) ) == 0 || strlen( trim($data['meaning']) ) == 0  ) {
			$response['error']['id'] = Configure::read('error.words_and_meaning_are_required');
			$response['error']['message'] = __('Words and meaning are required.');
		} else if ( preg_match ("/\*/",$data['meaning'] ) ) {
			$response['error']['id'] = Configure::read('error.fill_out_by_alphabet');
			$response['error']['message'] = __('Fill out by alphabet.');
		} else {
			
			$apiCommon = new ApiCommonController();
			$user = $apiCommon->validateToken($data['users_api_token']);
			
			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $apiCommon->error;
				return json_encode($response);
			}
			// check existing words
			
			$arrData = array(
				"words" => $data['words'],
				"userId" => $user['id']
			);
			
			if ($this->isWordExist($arrData) == false) {
				// create 
				$this->LessonWord->create();
				$this->LessonWord->set(
					array(
						"user_id" => $user['id'],
						"words" => $data['words'],
						"meaning" => $data['meaning']
					)
				);

				if ( $this->LessonWord->save() ) {
					$response['created'] = true;
				} else {
					$response['error']['id'] = Configure::read('error.invalid_token');
					$response['error']['message'] = __('Invalid token.');
				}
			} else {
				$response['error']['id'] = Configure::read('error.words_already_exist');
				$response['error']['message'] = __('Words already exist.');
			}
		}
		return json_encode($response);
	}
	public function lists() {
		$this->autoRender = false;

		@$data = json_decode($this->request->input(),true);

		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid requests');
		} else if (empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (isset($data['pagination']) && !is_int($data['pagination'])) {
			$response['error']['id'] = Configure::read('error.invalid_token');
			$response['error']['message'] = __('Invalid token.');
		} else {

			$apiCommon = new ApiCommonController();
			$user = $apiCommon->validateToken($data['users_api_token']);

			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $apiCommon->error;
				return json_encode($response);
			}

			$list = array();
			$limit = 20;
			$page = (!empty($data['pagination']) && (int)$data['pagination'] >= 1) ? (int)$data['pagination'] : 1;
			$offset = ($page - 1) * $limit;
			$offsetNext = ($page) * $limit;
			$conditions = array( "LessonWord.user_id" => $user['id'] );
			// if text search is set
			if ( isset($data['text']) ) {
				$conditions['AND']['OR']['LessonWord.words LIKE'] = '%'.$data['text'].'%';
				$conditions['AND']['OR']['LessonWord.meaning LIKE'] = '%'.$data['text'].'%';
			}

			// ------------ count : "total"
			$lessonWordCountAll = $this->LessonWord->find("count",array(
				"conditions" => $conditions,
				"fields" => array( 'LessonWord.id' ),
			));

			// ------------ word books : "wordbooks"
			$lessonWord = $this->LessonWord->find("all",array(
				"conditions" => $conditions,
				"fields" => array(
					'LessonWord.id',
					'LessonWord.user_id',
					'LessonWord.words',
					'LessonWord.meaning'
				),
				'order'	=> array('LessonWord.created DESC'),
				'limit'	=> $limit,
				'offset'=> $offset,
				'order' => "LessonWord.words ASC"
			));

			if (!$lessonWord) {
				return null;
			} else {
				foreach($lessonWord as $row) {
					$wordBook = array(
						'id' => (int)$row['LessonWord']['id'],
						'words' => $row['LessonWord']['words'],
						'meaning' => $row['LessonWord']['meaning']
					);
					array_push($list,$wordBook);
				}
				// ------------ Has next pagination : "has_next"
				$hasNext = $this->LessonWord->find('first',array(
					'conditions' =>  $conditions,
					'fields' =>  array("LessonWord.id"),
					'offset' => $offsetNext,
					'order' => "LessonWord.words ASC"
					)
				);
			}

			$response['total'] = $lessonWordCountAll;
			$response['wordbooks'] = $list;
			$response['has_next'] = $hasNext ? true : false;

			return json_encode($response);
		}

	}
	public function update() {
		$this->autoRender = false;

		@$data = json_decode($this->request->input(),true);
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if ( !isset($data['id']) || !isset($data['words']) || !isset($data['meaning']) ) {
			$response['error']['id'] = Configure::read('error.invalid_token');
			$response['error']['message'] = __('Invalid token.');
		} else if ( !is_numeric($data['id']) || is_float($data['id']) ) {
			$response['error']['id'] = Configure::read('error.invalid_id');
			$response['error']['message'] = __('Invalid ID.');
		} else if ( preg_match ("/\*/",$data['words']))  {
			$response['error']['id'] = Configure::read('error.fill_out_by_alphabet');
			$response['error']['message'] = __('Fill out by alphabet.');
		} else if ( $data['words'] == "" || $data['meaning'] == "" || strlen( trim($data['words']) ) == 0 || strlen( trim($data['meaning']) ) == 0  ) {
			$response['error']['id'] = Configure::read('error.words_and_meaning_are_required');
			$response['error']['message'] = __('Words and meaning are required.');
		} else if ( preg_match ("/\*/",$data['meaning'] ) ) {
			$response['error']['id'] = Configure::read('error.fill_out_by_alphabet');
			$response['error']['message'] = __('Fill out by alphabet.');
		} else {
			
			$apiCommon = new ApiCommonController();
			$user = $apiCommon->validateToken($data['users_api_token']);
			
			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $apiCommon->error;
				return json_encode($response);
			}
			
			// check is lessonWord ID is valid
			$LWIDvalid = $this->LessonWord->find("first",array(
				"conditions" => array( "LessonWord.id" => $data['id']),
				"fields" => array( "LessonWord.id"),
			)); 
			if ( !$LWIDvalid ) {
				$response['error']['id'] = Configure::read('error.id_does_not_exist');
				$response['error']['message'] = __("ID doesn't exist.");
				return json_encode($response);
			}			
			
			// check lessonWord "words" if already exist
			$LWIDexist = $this->LessonWord->find("first",array(
				"conditions" => array(
					"LessonWord.words" => $data['words'],
					"LessonWord.id !=" => $data['id'],
					"LessonWord.user_id" => $user['id']
				),
				"fields" => array( "LessonWord.id"),
			)); 
			if ( $LWIDexist ) {
				$response['error']['id'] = Configure::read('error.words_already_exist');
				$response['error']['message'] = __("Words already exist.");
				return json_encode($response);
			}
			
			// create 
			$this->LessonWord->read(array("id"),$LWIDvalid['LessonWord']['id']);
			$this->LessonWord->set(
				array(
					"user_id" => $user['id'],
					"words" => $data['words'],
					"meaning" => $data['meaning']
				)
			);

			if ( $this->LessonWord->save() ) {
				$response['updated'] = true;
			} else {
				$response['error']['id'] = Configure::read('error.invalid_token');
				$response['error']['message'] = __('Invalid token.');
			}
		}
		return json_encode($response);
	}
	public function delete() {
		$this->autoRender = false;

		@$data = json_decode($this->request->input(),true);
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else if (empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if ( !isset($data['id']) ) {
			$response['error']['id'] = Configure::read('error.invalid_token');
			$response['error']['message'] = __('Invalid token.');
		} else if ( isset($data['id']) && !is_int($data['id']) ) {
			if ( is_float($data['id'] ) ) {
				$response['error']['id'] = Configure::read('error.invalid_id');
				$response['error']['message'] = __('Invalid ID.');
			} else {
				$response['error']['id'] = Configure::read('error.invalid_token');
				$response['error']['message'] = __('Invalid token.');
			}
		} else {
			
			$apiCommon = new ApiCommonController();
			$user = $apiCommon->validateToken($data['users_api_token']);
			
			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = $apiCommon->error;
				return json_encode($response);
			}
			
			$checkWord = $this->LessonWord->find( "first", array(
					"conditions" => array( "LessonWord.id" => $data['id'] ),
					"fields" => array("LessonWord.id")
				)
			);

			if ( $checkWord ) {
				if ( $this->LessonWord->delete($data['id']) ) {
					$response['deleted'] = true;
				} else {
					$response['error']['id'] = Configure::read('error.invalid_token');
					$response['error']['message'] = __('Invalid token.');
				}
			} else {
				$response['error']['id'] = Configure::read('error.id_does_not_exist');
				$response['error']['message'] = __("ID doesn't exist.");
			}
		}
		return json_encode($response);
	}
	private function isWordExist($params = array()) {
		$words = isset($params['words'])? $params['words'] : null;
		$userId = isset($params['userId'])? $params['userId'] : null;
		$flag = false;
		if (!is_null($words)) {
			$conditions = array('conditions' => 
				array(
					'LessonWord.words' => $words, 
					'LessonWord.user_id' => $userId
				)
			);
			$duplicate = $this->LessonWord->find('first', $conditions);

			if ( $duplicate ) {
				$flag = true;
			}
		}
		return $flag;
	}
}
