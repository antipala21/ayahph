<?php
/***
 * View Reservation List of a user
 * Author : John Robert Jerodiaz
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class ReservationsListController extends AppController {
  public $uses = array(
    'User',
    'Teacher',
    'LessonSchedule',
    'UsersFavorite'
  );

  //language parameter for textbook
  public $urlLang = NULL;

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Auth->allow('index');
  }
	public function index() {
		$this->autoRender = false;    
		$data = json_decode($this->request->input(), true);
		$apiCommon = new ApiCommonController();
		$response = array();
		if (!$data) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request ');
		} else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_is_required');
			$response['error']['message'] = __('users_api_token is required');
		} else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
			$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
			$response['error']['message'] = __('users_api_token must be string');
		}  else if (isset($data['pagination']) && is_string($data['pagination'])) {
			$response['error']['id'] = Configure::read('error.pagination_must_be_integer');
			$response['error']['message'] = __('pagination must not be string');  
		}  else if (isset($data['api_version']) && is_string($data['api_version'])) {
			$response['error']['id'] = Configure::read('error.api_version_must_be_integer');
			$response['error']['message'] = __('api version must not be string');  
		}  else if (isset($data['reservation_status']) && is_string($data['reservation_status'])) {
			$response['error']['id'] = Configure::read('error.reservation_status_must_be_intiger');
			$response['error']['message'] = __('reservation status must not be string');
		}  else if(isset($data['reservation_from_at']) && !is_string($data['reservation_from_at'])) {
			$response['error']['id'] = Configure::read('error.reservation_from_at_must_be_string');
			$response['error']['message'] = __('reservation_from_at must be string');
		}  else if(isset($data['reservation_to_at']) && !is_string($data['reservation_to_at'])) {
			$response['error']['id'] = Configure::read('error.reservation_to_at_must_be_string');
			$response['error']['message'] = __('reservation_to_at must be string');
		} else {
			$user = $apiCommon->validateToken($data['users_api_token']);

			if (!$user) {
				$response['error']['id'] = Configure::read('error.invalid_api_token');
				$response['error']['message'] = __('users_api_token is invalid');
				return json_encode($response);
			}

			//trap reservation_from_at and reservation_to_at difference		
			if (isset($data['reservation_to_at']) && isset($data['reservation_from_at'])) {
				if (strtotime($data['reservation_from_at']) > strtotime($data['reservation_to_at'])) {
					$response['error']['id'] = Configure::read('error.reservation_from_at_must_be_less_than_reservation_to_at');
					$response['error']['message']  = __('reservation_from_at must be less than reservation_to_at');
					return json_encode($response);
				}
			}
			$apiVersion = isset($data['api_version'])? $data['api_version'] : 0;
			$params['version'] = $apiVersion;
			$params['user'] = $user;
			$params['data'] = $data;
			$response = $this->LessonSchedule->apiCommonReservationList($params);
		}
		return json_encode($response);
	}
}