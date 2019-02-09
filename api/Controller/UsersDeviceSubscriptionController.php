<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersDeviceSubscriptionController extends AppController {
    public $uses = array('User', 'DeviceToken', 'UsersSubscription');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('subscribeDevice', 'unsubscribeDevice'));
        $this->autoRender = false;
    }

    /**
     * Subscribe a device to a user's Topic subscription
     */
    public function subscribeDevice() {
        $response = array();
        
        @$data = json_decode($this->request->input(),true);
        if ($data) {
            foreach($data as $key => $value) {
                $data[$key] = $value;
            }
        }

        //validate passed data
        if (!$data) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request');
        } else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_is_required');
            $response['error']['message'] = __('users_api_token is required');
        } else if(!is_string($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
            $response['error']['message'] = __('users_api_token must be string');
        } else if (!isset($data['device_token']) || empty($data['device_token'])) {
            $response['error']['id'] = Configure::read('error.device_token_is_required');
            $response['error']['message'] = __('device_token is required');
        } else if (!is_string($data['device_token'])) {
            $response['error']['id'] = Configure::read('error.device_token_must_be_string');
            $response['error']['message'] = __('device_token must be string');
        } else {
            //validate api token
            $apiCommon = new ApiCommonController();
            $user['User'] = $apiCommon->validateToken($data['users_api_token']);

            if (!$user['User'] || $user['User']['status'] != 1) {
                $response['error']['id'] = Configure::read('error.invalid_api_token');
                $response['error']['message'] = __('Invalid users_api_token');
            } else {
                //get endpoint of device
                $endpoint = $this->DeviceToken->find('first', array(
                    'fields' => array(
                        'DeviceToken.id',
                        'DeviceToken.endpoint_arn'
                    ),
                    'conditions' => array(
                        'DeviceToken.endpoint_arn IS NOT NULL',
                        'DeviceToken.device_token' => $data['device_token'],
                        'DeviceToken.user_id' => $user['User']['id'],
                        'DeviceToken.active_flg' => 1
                    ),
                    'order' => 'DeviceToken.id DESC'
                ));

                if ($endpoint && isset($endpoint['DeviceToken']['endpoint_arn'])) {
                    //----Unsubscribe past subscriptions
                    $pastSubscriptions = $this->UsersSubscription->find('all', array(
                        'fields' => 'UsersSubscription.id',
                        'conditions' => array(
                            'UsersSubscription.device_token_id' => $endpoint['DeviceToken']['id'],
                            'UsersSubscription.user_id !=' => $user['User']['id'],
                            'UsersSubscription.status' => 1
                        )
                    ));

                    if ($pastSubscriptions) {
                        foreach ($pastSubscriptions as $subscription) {
                            //unsubscribe and set status to 0 for other subscriptions
                            $this->UsersSubscription->unsubscribeFromTopic($endpoint['DeviceToken']['id'], $subscription['UsersSubscription']['id']);
                        }
                    }
                    
                    //----Subscribe
                    if (
                        isset($user['User']['sns_topic_arn']) && 
                        !is_null($user['User']['sns_topic_arn']) && 
                        !empty($user['User']['sns_topic_arn'])
                    ) {
                        //check if topic arn column exists and is not null
                        $topicArn = $user['User']['sns_topic_arn'];
                    } else {
                        $topicArn = $this->UsersSubscription->createTopicForUser($user['User']['id']);
                    }

                    $subscribe = $this->UsersSubscription->subscribeToTopic($endpoint['DeviceToken']['id'], $endpoint['DeviceToken']['endpoint_arn'], $user['User']['id'], $topicArn);
                    $response['subscribe'] = ($subscribe) ? true : false;

                } else {
                    //return error
                    $response['error']['id'] = Configure::read('error.this_user_does_not_have_this_device_token');
                    $response['error']['message'] = "this_user_does_not_have_this_device_token";
                }//endif endpoint

            }//endif user validation

        }

        return json_encode($response);
    }

    /**
     * Unsubscribe a device from a user's Topic subscription
     */
    public function unsubscribeDevice() {
        $response = array();

        @$data = json_decode($this->request->input(),true);
        if ($data) {
            foreach($data as $key => $value) {
                $data[$key] = $value;
            }
        }

        //validate passed data
        if (!$data) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request');
        } else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_is_required');
            $response['error']['message'] = __('users_api_token is required');
        } else if(!is_string($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
            $response['error']['message'] = __('users_api_token must be string');
        } else if (!isset($data['device_token']) || empty($data['device_token'])) {
            $response['error']['id'] = Configure::read('error.device_token_is_required');
            $response['error']['message'] = __('device_token is required');
        } else if (!is_string($data['device_token'])) {
            $response['error']['id'] = Configure::read('error.device_token_must_be_string');
            $response['error']['message'] = __('device_token must be string');
        } else {
            //validate api token
            $api = new ApiCommonController();
            $user['User'] = $api->validateToken($data['users_api_token']);

            if (!$user['User'] || $user['User']['status'] != 1) {
                $response['error']['id'] = Configure::read('error.invalid_api_token');
                $response['error']['message'] = __('Invalid users_api_token');
            } else {
                //get endpoint of device
                $endpoint = $this->DeviceToken->find('first', array(
                    'fields' => 'DeviceToken.id',
                    'conditions' => array(
                        'DeviceToken.device_token' => $data['device_token'],
                        'DeviceToken.user_id' => $user['User']['id']
                    ),
                    'order' => 'DeviceToken.id DESC'
                ));

                if ($endpoint) {
                    //get current user topic subscriptions of device
                    $pastSubscription = $this->UsersSubscription->find('first', array(
                        'fields' => 'UsersSubscription.id',
                        'conditions' => array(
                            'UsersSubscription.device_token_id' => $endpoint['DeviceToken']['id'],
                            'UsersSubscription.user_id' => $user['User']['id'],
                            'UsersSubscription.status' => 1,
                            'UsersSubscription.topic_arn' => $user['User']['sns_topic_arn'] //specific to user topic
                        )
                    ));

                    if ($pastSubscription) {
                        //unsubscribe and set status to 0 previous subscription
                        $result = $this->UsersSubscription->unsubscribeFromTopic($endpoint['DeviceToken']['id'], $pastSubscription['UsersSubscription']['id']);
                        $response['unsubscribed'] = $result;
                    } else {
                        $response['error']['id'] = Configure::read('error.device_subscription_does_not_exist');
                        $response['error']['message'] = 'device_subscription_does_not_exist';
                    }//endif unsubcription

                } else {
                    $response['error']['id'] = Configure::read('error.this_user_does_not_have_this_device_token');
                    $response['error']['message'] = 'this_user_does_not_have_this_device_token';
                }

            }//endif user validation

        }

        return json_encode($response);
    }
}

?>