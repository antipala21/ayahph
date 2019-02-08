<?php
/****************************
 * API for Lesson Useful Phrases
 * Author : Sharon Macasaol
 * June 2017   
 *****************************/

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class LessonUsefulPhrasesController extends AppController{

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow('index');
	}

	public function index(){
		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
		}

		if (empty($data)) {
			$result['error']['id'] = Configure::read('error.invalid_request');
			$result['error']['message'] = __('Invalid request.');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$result['error']['id'] = Configure::read('error.users_api_token_is_required');
			$result['error']['message'] = __('users_api_token is required');
		} else if (!is_string($data['users_api_token'])) {
			$result['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$result['error']['message'] = __('The users_api_token must be string request.');
		} else {
			$user_api_token = $data['users_api_token'];
			$api = new ApiCommonController();
			$user = $api->findApiToken($user_api_token);

			if (is_array($user)) {
				// check if has user
				if (!array_key_exists('id', $user)) {
					$result['error']['id'] = Configure::read('error.invalid_api_token');
					$result['error']['message'] = $api->error;
				} 
			} else {
				$result['error']['id'] = Configure::read('error.invalid_api_token');
				$result['error']['message'] = $api->error;
			}
		}

		//if no request error
		if (!isset($result['error']['message'])) {
			$result = $this->returnArray();
		}

		echo json_encode($result);
	}

	private function returnArray() {
		$arrayPhrases = array (
			"phrases" => array(
				array(
					"category_name_ja" => "要望",
					"category_name_en" => "Request",
					"words" =>	
						array(
							array(
								"ja" => "今言った単語/センテンスをタイピングしてください",
								"en" => "Could you type what you've just said?"
							),
							array(
								"ja" => "もう一度言ってください",
								"en" => "Could you say it again?"
							),
							array(
								"ja" => "もっとゆっくり話してください。",
								"en" => "Could you speak more slowly?"
							),
							array(
								"ja" => "もっと簡単な単語・表現で教えてください。",
								"en" => "Could you use more simple words/expressions?"
							),
							array(
								"ja" => "もっとゆっくり進めてください。",
								"en" => "Could you slow down the lessen pace?"
							),
							array(
								"ja" => "もう少し時間をください。",
								"en" => "Could you give me some more time?"
							),
							array(
								"ja" => "ヒント／例をください。",
								"en" => "Could you give me a clue/example?"
							),
							array(
								"ja" => "私が間違えたら指摘してください。",
								"en" => "If I make mistakes, kindly correct them."
							),
							array(
								"ja" => "日本人スタッフを呼んでいただけますか？",
								"en" => "Could you call Japanese staff for me?"
							)									
						)			

				),
				array(
					"category_name_ja" => "レッスン",
					"category_name_en" => "Lesson",
					"words" =>	
						array(
							array(
								"ja" => "スピーキング中心でお願いします。",
								"en" => "I'd like to focus on speaking in this lesson."
							),
							array(
								"ja" => "リスニング中心でお願いします。",
								"en" => "I'd like to focus on listening in this lesson."
							),
							array(
								"ja" => "今日は15分だけレッスンしたい。",
								"en" => "I have only 15 minutes for the lesson today."
							),
							array(
								"ja" => "レッスンを切り上げたい。",
								"en" => "Excuse me, I have to end this lesson now."
							)	
						)		
					),	
				array(
					"category_name_ja" => "音声設定",
					"category_name_en" => "Sound setting",
					"words" =>	
						array(
							array(
								"ja" => "よく聞こえません。",
								"en" => "I can't hear you."
							),
							array(
								"ja" => "わたし(の声)が聞こえますか？",
								"en" => "Can you hear me?"
							),
							array(
								"ja" => "音量を上げてもらえますか？",
								"en" => "Could you turn the volume up?"
							),
							array(
								"ja" => "音量を下げてもらえますか？",
								"en" => "Could you turn the volume down?"
							)
						)				
				),
				array(
					"category_name_ja" => "映像設定",
					"category_name_en" => "Video setting",
					"words" =>	
						array(
							array(
								"ja" => "あなた(の姿)が見えません。",
								"en" => "I can't see you."
							),
							array(
								"ja" => "わたし(の姿)が見えますか？",
								"en" => "Can you see me?"
							),
							array(
								"ja" => "わたしはウェブカメラを持っていません。/使いません。",
								"en" => "I don't have a web camera. / I don't use a web camera."
							),
							array(
								"ja" => "映像を止めてもらっていいですか？(音声だけにしたい場合)",
								"en" => "Could you turn off the camera?"
							)
						)	
				)
			)
		);
		return $arrayPhrases;	
	}
}