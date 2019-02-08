<?php
/****************************
 * API for Teacher APP_LOGIN
 * Author : Burt Karl Cabigas
 * June 2018
 * NC-4538 
 *****************************/

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class TeacherAppLoginController extends AppController {

	public $uses = array(
		'Teacher',
		'TeacherAppLoginHistory'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow('index');
	}

	public function index() {
		$response = array();
		$teacher = array();

		if ($this->request->is('post')) {
			$data = json_decode($this->request->input(), true);
			$token = isset($data['teachers_api_token']) && !empty($data['teachers_api_token'])? $data['teachers_api_token'] : null;

			if (!$token) {
				$response['error']['id'] = Configure::read('error.teachers_api_token_can_not_be_empty');
				$response['error']['message'] = __('teachers_api_token can not be empty');
			} else {
				$teacher = $this->Teacher->getFromToken($token);
				if (!$teacher) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = __('Invalid teachers_api_token');
				} else {
					$save = $this->TeacherAppLoginHistory->saveLog($teacher['id'], 2);
					if ($save) {
						$response['success'] = true;
					} else {
						$response['error']['id'] = Configure::read('error.save_to_logs_failed');
						$response['error']['message'] = __('Unable to save teacher_app_login_logs');
					}
					
				}
			}
			return json_encode($response);
		}
	}
}