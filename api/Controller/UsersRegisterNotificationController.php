<?php
/***
* Register / Add User Device Token
* Author : John Robert Jerodiaz ( Roy )
*/
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersregisternotificationController extends AppController {
    public $uses = array('DeviceToken', 'User', 'UsersSubscription');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('index', 'subscribeDevice'));
        $this->autoRender = false;
    }

    /***
    * @return type array
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
            } else if(!is_string($data['users_api_token'])) {    
                $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
                $response['error']['message'] = __('users_api_token must be string');
            } else if (!isset($data['device_token'])) {
                $response['error']['id'] = Configure::read('error.device_token_is_required');
                $response['error']['message'] = __('device_token is required'); 
            } else if (trim($data['device_token']) == "" || empty($data['device_token'])) {
                $response['error']['id'] = Configure::read('error.device_token_is_required');
                $response['error']['message'] = __('device_token is required');
            } else if (!is_string($data['device_token'])) {  
                $response['error']['id'] = Configure::read('error.device_token_must_be_string');
                $response['error']['message'] = __('device_token must be string');
            } else if (!isset($data['device_type']) ) {
                $response['error']['id'] = Configure::read('error.device_type_is_required');
                $response['error']['message'] = __('device_type is required'); 
            } else if (!is_int($data['device_type'])) {
                $response['error']['id'] = Configure::read('error.device_type_must_be_integer');
                $response['error']['message'] = __('device_type must be integer');
            } else if ($data['device_type']!=1 && $data['device_type']!=2) {
                $response['error']['id'] = Configure::read('error.invalid_device_type');
                $response['error']['message'] = __('device_type must be 1 or 2');
            } else {
                $apiCommon = new ApiCommonController();
                $query['User'] = $apiCommon->validateToken($data['users_api_token']);
                if (!$query['User']) {
                    $response['error']['id'] = Configure::read('error.invalid_api_token');
                    $response['error']['message'] = __('Invalid users_api_token');
                } else {
                    //-----Device Token Registration Logic  
                    $checkExist = $this->DeviceToken->find('first', array(
                        'fields' => array(
                            'DeviceToken.device_token',
                            'DeviceToken.id',
                            'DeviceToken.endpoint_arn'
                        ),
                        'conditions' => array('DeviceToken.device_token' => $data['device_token'])
                    ));

                    $deviceData = array(
                        'user_id' => $query['User']['id'],
                        'device_token' => $data['device_token'],
                        'device_type' => $data['device_type'],
                        'active_flg' => 0 //set to 0 initially, set to 1 when enpoint is created and active
                    );

                    //if device token exist
                    if (
                        $checkExist && 
                        $checkExist['DeviceToken']['device_token'] == $data['device_token']
                    ) {
                        $this->DeviceToken->read(null, $checkExist['DeviceToken']['id']);
                    } else {
                        //else create new row
                        $this->DeviceToken->create();
                    }
                    
                    $this->DeviceToken->set($deviceData);

                    try {
                        $save = $this->DeviceToken->save();
                    } catch (Exception $e) {
                        $save = false;
                        CakeLog::write("error", $e->getMessage());
                    }

                    if ($save) {
                        $pn = new pushNotification();
                        $response['created'] = true;
                        $endpointSave = false;

                        //create endpoint if token does not have any yet, endpoint is null or current endpoint is disabled
                        if (
                            !$checkExist || 
                            (
                                $checkExist && 
                                (
                                    is_null($checkExist['DeviceToken']['endpoint_arn']) || 
                                    !$pn->isEndpointEnabled($checkExist['DeviceToken']['endpoint_arn'])
                                )
                            )
                        ) {
                            //create Endpoint with device token
                            $endpointArn = $pn->getEndpointArn($deviceData['device_token'], $deviceData['device_type']);

                            //if endpoint created
                            if ($endpointArn) {
                                //save EndpointArn to database and set active_flg to 1
                                if ($this->DeviceToken->read(null, $save['DeviceToken']['id'])) {
                                    $this->DeviceToken->set(array(
                                        "user_id" => $deviceData['user_id'],
                                        "endpoint_arn" => $endpointArn,
                                        "active_flg" => 1
                                    ));
                                    try {
                                        $endpointSave = $this->DeviceToken->save();
                                    } catch (Exception $e) {
                                        CakeLog::write("error", $e->getMessage());
                                    }
                                }
                            }
                            $response['endpoint_created'] = ($endpointSave) ? true : false;
                        //else if already has token
                        } else {
                            if ($this->DeviceToken->read(null, $save['DeviceToken']['id'])) {
                                $this->DeviceToken->set(array("active_flg" => 1));
                                try {
                                    $endpointSave = $this->DeviceToken->save();
                                } catch (Exception $e) {
                                    CakeLog::write("error", $e->getMessage());
                                }
                            }
                            $response['endpoint_created'] = ($endpointSave) ? true : false;
                        }

                        //-----Android Subscription Logic
                        if (
                            $data['device_type'] == 2 &&
                            $response['created'] &&
                            $response['endpoint_created'] &&
                            $endpointSave
                        ) {
                            //----Unsubscribe past subscriptions
                            $pastSubscriptions = $this->UsersSubscription->find('all', array(
                                'fields' => 'UsersSubscription.id',
                                'conditions' => array(
                                    'UsersSubscription.device_token_id' => $endpointSave['DeviceToken']['id'],
                                    'UsersSubscription.user_id !=' => $query['User']['id'],
                                    'UsersSubscription.status' => 1
                                    )
                                ));

                            if ($pastSubscriptions) {
                                foreach ($pastSubscriptions as $subscription) {
                                    //unsubscribe and set status to 0 for other subscriptions
                                    $this->UsersSubscription->unsubscribeFromTopic($endpointSave['DeviceToken']['id'], $subscription['UsersSubscription']['id']);
                                }
                            }

                            //----Subscribe
                            if (
                                isset($query['User']['sns_topic_arn']) && 
                                !is_null($query['User']['sns_topic_arn']) && 
                                !empty($query['User']['sns_topic_arn'])
                            ) {
                                //check if topic arn column exists and is not null
                                $topicArn = $query['User']['sns_topic_arn'];
                            } else {
                                $topicArn = $this->UsersSubscription->createTopicForUser($query['User']['id']);
                            }

                            $this->UsersSubscription->subscribeToTopic($endpointSave['DeviceToken']['id'], $endpointSave['DeviceToken']['endpoint_arn'], $query['User']['id'], $topicArn);
                        }

                    } else {
                        $response['created'] = false;
                    }
                }
            }       
        }

        return json_encode($response);
    }

}