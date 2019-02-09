<?php

/* 
 * API Lesson End Time Controller
 * Author: John Robert Jerodiaz ( Roy )
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class LessonEndTimeController extends AppController {
    
  public $uses = array(
      'LessonOnair',
      'User'
  );
    
  public function beforeFilter() {
      parent::beforeFilter();
      $this->Auth->allow('index');
      $this->autoRender = false;
  }
  
  /***
   * Api lesson end time
   * @access url https://nativecamp.net/api/lesson/endtime
   * @param
   * @return  array
   */
  public function index() {
      $response = array();
      $data = json_decode($this->request->input(), true);
      if (!$data) {
          $response['error']['id'] = Configure::read('error.invalid_request');
          $response['error']['message'] = __('Invalid request.');
      } else {
        if (empty($data['users_api_token']) || !isset($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_is_required');
            $response['error']['message'] = __('users_api_token is required.');
        } else if (!is_string($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
            $response['error']['message'] =__('users_api_token must be string');
        } else if (empty($data['chat_hash']) || !isset($data['chat_hash'])) {
            $response['error']['id'] = Configure::read('error.chat_hash_is_required');
            $response['error']['message'] = __('chat_hash is required.');
        } else if (!is_string($data['chat_hash'])) {
            $response['error']['id'] = Configure::read('error.chat_hash_must_be_string');
            $response['error']['message'] = __('chat_hash must be string');
        } else {
          //get user id by users_api_token
          $api = new ApiCommonController();
          $user['User'] = $api->validateToken($data['users_api_token']);
          $getId = $user;
          if ($getId['User']) {
            // If the requested users_api_token and chat_hash is in the LessonOnair table
            $LessonOnair = $this->LessonOnair->find('first', array(
              'fields' => 'end_time',
              'conditions' => array(
                'BINARY(chat_hash)' => $data['chat_hash'],
                'user_id' => $getId['User']['id']
              )
            ));
            if ($LessonOnair) {
              $endTimeStamp = strtotime($LessonOnair['LessonOnair']['end_time']);
              $now = time();
              $timeDifference = $endTimeStamp - $now;
              if($timeDifference < 0) {
                $timeDifference = 0;
              }
              $response = array(
                'min' => date('i', $timeDifference),
                'sec' => date('s', $timeDifference),
                'end_time_stamp' => $endTimeStamp
              );
            } else {
              $response['error']['id'] = Configure::read('error.invalid_chat_hash_for_the_users_api_token_requested');
              $response['error']['message'] = 'Invalid chat_hash for the users_api_token requested.';
            }
          } else {
              $response['error']['id'] = Configure::read('error.invalid_api_token');
              $response['error']['message'] = 'Invalid users_api_token.'; 
          }
        }
      }
    return json_encode($response);
  }// end of function
    
} // end of class

