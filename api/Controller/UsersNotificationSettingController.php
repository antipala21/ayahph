<?php

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersNotificationSettingController extends AppController {
    public $uses = array('User');
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('index', 'update'));
        $this->autoRender = false;
    }
    
    /**
     * Get user's current setting for notifications
     */
    public function index() {
        $response = array();
        
        $data = json_decode($this->request->input(), true);
        if (!$data) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request');
        } else { 
            if (!isset($data['users_api_token'])) {
                $response['error']['id'] = Configure::read('error.users_api_token_is_required');
                $response['error']['message'] = __('users_api_token is required');  
            } else if (empty($data['users_api_token'])) {
                $response['error']['id'] = Configure::read('error.users_api_token_is_required');
                $response['error']['message'] = __('users_api_token is required');
            } else if (!is_string($data['users_api_token'])) {    
                $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
                $response['error']['message'] = __('users_api_token must be string');
            } else {
                $apiCommon = new ApiCommonController();
                $query['User'] = $apiCommon->validateToken($data['users_api_token']);
                if (!$query['User']) {
                    $response['error']['id'] = Configure::read('error.invalid_api_token');
                    $response['error']['message'] = __('Invalid users_api_token');
                } else {
                    //TODO: for future notification setting, add to fields
                    $notifSetting = $this->User->find('first', array(
                        'fields' => array(
                            'User.reservation_notif_flg',
                            'User.reservation_cancel_notif_flg'),
                        'conditions' => array(
                            'User.id' => $query['User']['id'],
                            'User.status' => 1
                        )
                    ));
                    
                    if ($notifSetting) {
                        $response = $notifSetting['User'];
                    } else {
                        $response['notification_setting'] = false;
                    }
                }
            }
        }
        
        return json_encode($response);
    }
    
    /**
     * Update user's notification setting
     */
    public function update() {
        $response = array();
        
        $data = json_decode($this->request->input(), true);
        if (!$data) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request');
        } else { 
            if (!isset($data['users_api_token'])) {
                $response['error']['id'] = Configure::read('error.users_api_token_is_required');
                $response['error']['message'] = __('users_api_token is required');  
            } else if (empty($data['users_api_token'])) {
                $response['error']['id'] = Configure::read('error.users_api_token_is_required');
                $response['error']['message'] = __('users_api_token is required');
            } else if (!is_string($data['users_api_token'])) {    
                $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
                $response['error']['message'] = __('users_api_token must be string');
            } else if (isset($data['reservation_notif_flg']) && $data['reservation_notif_flg'] != 0 && $data['reservation_notif_flg'] != 1){
                $response['error']['id'] = Configure::read('error.invalid_reservation_notif_flg');
                $response['error']['message'] = __('reservation_notif_flg must be 0 or 1');
            } else if (isset($data['reservation_cancel_notif_flg']) && $data['reservation_cancel_notif_flg'] != 0 && $data['reservation_cancel_notif_flg'] != 1) {
                $response['error']['id'] = Configure::read('error.invalid_reservation_cancel_notif_flg');
                $response['error']['message'] = __('reservation_cancel_notif_flg must be 0 or 1');
            //TODO: for future notification settings, add validation here per setting
            } else {
                $apiCommon = new ApiCommonController();
                $query['User'] = $apiCommon->validateToken($data['users_api_token']);
                if (!$query['User']) {
                    $response['error']['id'] = Configure::read('error.invalid_api_token');
                    $response['error']['message'] = __('Invalid users_api_token');
                } else {
                    $userData = array();
                    $save = false;

                    //TODO: for future notification settings, add checker here to be included for update
                    //set reservation notif flag if present
                    if (isset($data['reservation_notif_flg'])) {
                        $userData['reservation_notif_flg'] = (filter_var($data['reservation_notif_flg'], FILTER_VALIDATE_BOOLEAN) == true) ? "1" : "0";
                    }
                    
                    //set reservation cancel notif flag if present
                    if (isset($data['reservation_cancel_notif_flg'])) {
                        $userData['reservation_cancel_notif_flg'] = (filter_var($data['reservation_cancel_notif_flg'], FILTER_VALIDATE_BOOLEAN) == true) ? "1" : "0";
                    }
                    
                    //update notification settings according to keys in array
                    $this->User->validate = array();
                    if ($this->User->read(null, $query['User']['id'])) {
                        $this->User->set($userData);                        
                        $save = $this->User->save();
                    }

                    $response['update_data'] = $userData;
                    $response['updated'] = ($save) ? true : $save;
                }                
            }
        }
        
        return json_encode($response);    
    }
}

?>