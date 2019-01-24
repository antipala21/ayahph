<?php
//AWS SDK
require_once(ROOT.'/lib/Vendor/aws.phar');
use Aws\Sns\SnsClient,
Aws\Sns\Exception\SnsException,
Aws\Common\Enum\Region;

class pushNotification{
    public $sns;
    public $platform = array(
        0 => 'APNS_SANDBOX',
        1 => 'APNS',
        2 => 'GCM'
    );

    public $platformApplicationArn = array(
        'APNS' => 'arn:aws:sns:ap-northeast-1:611762151909:app/APNS/nativecamp-ios-production',
        'APNS_SANDBOX' => 'arn:aws:sns:ap-northeast-1:611762151909:app/APNS_SANDBOX/nativecamp-ios-development',
        'GCM' => 'arn:aws:sns:ap-northeast-1:611762151909:app/GCM/nativecamp-android-production'
    );

    public $userSettings = array(
        'APNS_SANDBOX' => array(
            'platform_name' => 'nativecamp-ios-development',
            'cert_file_name' => 'dev/apns-dev-cert.pem',
            'key_file_name' => 'dev/apns-dev-key.pem'
        ),
        'APNS' => array(
            'platform_name' => 'nativecamp-ios-production',
            'cert_file_name' => 'live/apns-prod-cert.pem',
            'key_file_name' => 'live/apns-prod-key.pem'
        ),
        'GCM' => array(
            'platform_name' => 'nativecamp-android-production',
            'server_api_key' => 'AAAA3TW5ASM:APA91bEGd0g5EIgb78SqpMTBU8yURr_0SJKUi75ub5sy3Ou9lHiASV9BMJiqucRRqhoWuBP3CnonSxq9e5GOB7xWSAwORr4-EiQ9Nj_P0t-TD24vFl0jDwkqZ4Adz1apRUeFDydsL9_Z '
        )
    );
    
    public function __construct() {        
        //Create SNSClient, connection to AWSSNS service with AWS auth credentials        
        //error handling on try-catch of each method call using client
        $this->sns = SnsClient::factory(array(
            'key'       =>  'AKIAILFJMYRPRRLARGNA',
            'secret'    =>  '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',
            'region'    =>  'ap-northeast-1'
        ));
        
        //test connection
        try{
            $this->sns->listPlatformApplications();
        }catch(SnsException $e) {
            $this->sns = false;
        }
    }

    public function __log_error($method = null, $message = null) {
        CakeLog::write('error', "[PUSH_NOTIFICATION] ". $method ."|| ".$message);
    }

    public function __log_debug($message = null) {
        CakeLog::write('debug', "[PUSH_NOTIFICATION] ". $message);
    }

    public function createPlatformArn($platform = null){
        if ($this->sns && !is_null($platform)) {
            $name = 'nativecamp';
            $attributes = array();
            $platformSettings = $this->userSettings;
            $name = isset($platformSettings[$platform]['platform_name']) ? trim($platformSettings[$platform]['platform_name']) : '';

            if (strlen($name) == 0) {
                $error = "Platform Name is empty.";
                $this->__log_error(__METHOD__,  "Error: ".$error);
                return;
            }

            if ($platform == 'APNS_SANDBOX' || $platform == 'APNS') {
                $pKeyFileName = isset($platformSettings[$platform]['key_file_name']) ? $platformSettings[$platform]['key_file_name'] : '';
                $certFileName = isset($platformSettings[$platform]['cert_file_name']) ? $platformSettings[$platform]['cert_file_name'] : '';

                $apnsPK = ROOT . DS . "lib" . DS . "Certs" . DS . "user" . DS . $pKeyFileName;
                $apnsCert = ROOT . DS . "lib" . DS . "Certs" . DS . "user" . DS . $certFileName;
                
                if (file_exists($apnsPK) && file_exists($apnsCert)) {
                    $attributes = array(
                        'PlatformCredential' => file_get_contents($apnsPK),
                        'PlatformPrincipal' => file_get_contents($apnsCert)
                    );
                }

            } else if ($platform == 'GCM') {
                $serverApiKey = isset($platformSettings[$platform]['server_api_key']) ? $platformSettings[$platform]['server_api_key'] : '';
                $attributes = array(
                    'PlatformCredential' => $serverApiKey
                );
            } else {
                //do nothing if not matches any
                return;
            }

            try {
                $result = $this->sns->createPlatformApplication(array(
                    'Name' => $name,
                    'Platform' => $platform,
                    'Attributes' => $attributes
                ));
                $this->__log_debug("Platform Application ARN: ". $result['PlatformApplicationArn']);
                return $result['PlatformApplicationArn'];
            } catch (SnsException $e) {
                //log error
                $this->__log_error(__METHOD__, "Error: ".$e->getResponse()->getMessage()."|| Arguments: ". json_encode(func_get_args()));
            }
        }
    }

    public function deletePlatformApplication($platform = null){
        $result = false;

        if (!$this->sns || is_null($platform)) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $result;
        }

        $platformApplicationArnSet = $this->platformApplicationArn;

        try {
            $platformArn = isset($platformApplicationArnSet[$platform]) ? $platformApplicationArnSet[$platform] : null;
            if (strlen($platformArn) > 0) {
                $this->sns->deletePlatformApplication(array('PlatformApplicationArn' => $platformArn));
                $this->__log_debug('Deleted Platform ARN: '.$platformArn);
                $result = true;
            }
        } catch (SnsException $e) {
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". json_encode(func_get_args()));
        }

        return $result;
    }

    public function setPlatformApplicationAttributes($platform = null){
        $result = array();

        if (!$this->sns || is_null($platform)) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            $result['error'] = $error;
            return $result;
        }

        try {
            $attributes = array();
            $platformSettings = $this->userSettings;
            $platformApplicationArnSet = $this->platformApplicationArn;

            if ($platform == 'APNS_SANDBOX' || $platform == 'APNS') {
                $pKeyFileName = isset($platformSettings[$platform]['key_file_name']) ? $platformSettings[$platform]['key_file_name'] : '';
                $certFileName = isset($platformSettings[$platform]['cert_file_name']) ? $platformSettings[$platform]['cert_file_name'] : '';
                
                $apnsPK = ROOT . DS . "lib" . DS . "Certs" . DS . "user" . DS . $pKeyFileName;
                $apnsCert = ROOT . DS . "lib" . DS . "Certs" . DS . "user" . DS . $certFileName;
                
                if (file_exists($apnsPK) && file_exists($apnsCert)) {
                    $attributes = array(
                        'PlatformCredential' => file_get_contents($apnsPK),
                        'PlatformPrincipal' => file_get_contents($apnsCert)
                    );
                }

            } else if ($platform == 'GCM') {
                $serverApiKey = isset($platformSettings[$platform]['server_api_key']) ? $platformSettings[$platform]['server_api_key'] : '';
                $attributes = array(
                    'PlatformCredential' => $serverApiKey
                );
            }

            $platformArn = isset($platformApplicationArnSet[$platform]) ? $platformApplicationArnSet[$platform] : null;
            if (strlen($platformArn) > 0) {
                $this->sns->setPlatformApplicationAttributes(array(
                    'Attributes' => $attributes,
                    'PlatformApplicationArn' => $platformArn
                ));
                $result['success'] = true;
            } else {
                $error = 'Platform ARN is empty.';
                $result['error'] = $error;
                $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));

            }

        } catch (SnsException $e) {
            $error = $e->getMessage();
            $result['error'] = $error;
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
         }

        return $result;
    }

    public function listPlatformApplications() {
        if (!$this->sns) {
            $this->__log_error(__METHOD__, "Error: SnsClient is not an object.");
            return;
        }

        try {
            $result = $this->sns->listPlatformApplications();
            print_r($result);
        } catch (SnsException $e) {
            $this->__log_error(__METHOD__, "Error: ".$e->getMessage());
            return;
        }
    }
    
    /**
     * create an Endpoint with device_token and return EndpointArn
     * referenced from: http://qiita.com/gomi_ningen/items/1002d256285c6d72c6ac
     * @param  String $deviceToken 
     * @return String|Array              On success return EndpointArn else return error details as array
     */
    public function getEndpointArn($deviceToken = null, $deviceType = null) {
        $response = false;
        
        if (is_null($deviceToken) || is_null($this->getPlatform($deviceType)) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $response;
        } else {            
            try {
                //get platform
                $platform = $this->getPlatform($deviceType);

                //attempt to create endpoint
                $result = $this->createDeviceEndpointArn($deviceToken, $platform);
                
                //check endpointArn contents
                if (!is_array($result['error'])) {
                    //return endpointArn
                    $response = $result;
                } else {
                    //catch error in attempt to create endpoint, log error
                    $error = isset($result['error']) ? $result['error'] : $result;
                    $this->__log_error(__METHOD__, "Error: ".$error);
                }
            } catch (SnsException $e) {
                //catch error in attempt to create endpoint, log error
                $this->__log_error(__METHOD__, "Error: ".$e->getMessage()." || Arguments: ". json_encode(func_get_args()));
            }
        }
        
        return $response;
    }

    public function createDeviceEndpointArn($deviceToken = null, $platform = null) {
        $response = false;
        
        if (is_null($deviceToken) || is_null($platform) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $response['error'] = $error;
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $response;
        } else {            
            try {
                $platformApplicationArnSet = $this->platformApplicationArn;

                //attempt to create endpoint
                $result = $this->sns->createPlatformEndpoint(array(
                    'PlatformApplicationArn' => $platformApplicationArnSet[$platform],
                    'Token' => $deviceToken
                ));
                
                //check endpointArn contents
                if (isset($result['EndpointArn'])) {
                    //return endpointArn
                    $response = $result['EndpointArn'];
                } else {
                    $response['error'] = 'Create endpoint ARN failed.';
                }
            } catch (SnsException $e) {
                //catch error in attempt to create endpoint, log error
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage()." || Arguments: ". json_encode(func_get_args()));
                $response['error'] = $e->getMessage();
            }
        }
        
        return $response;
    }
    
    /**
     * delete endpoint
     * @param  String $endpointArn endpointARN to be deleted
     * @return Bool              result of deletion
     */
    public function deleteEndpoint($endpointArn = null) {
        $response = false;
        
        if (is_null($endpointArn) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return $response;
        } else {            
            try {
                //attempt to create endpoint
                $this->sns->deleteEndpoint(array(
                    'EndpointArn' => $endpointArn
                ));
                
                //result for this operation is always empty when successful
                $response = true;
            } catch (SnsException $e) {
                //catch error in attempt to delete endpoint, log error
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage()." || Arguments: ". func_get_arg(0));
            }
        }
        
        return $response;
    }
    
    /**
     * check if endpoint is enabled
     * @param  String  $endpointArn 
     * @return boolean              [description]
     */
    public function isEndpointEnabled($endpointArn = null) {
        $response = false;
        
        if (is_null($endpointArn) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return $response;
        } else {            
            try {
                //attempt to create endpoint
                $result = $this->sns->getEndpointAttributes(array(
                    'EndpointArn' => $endpointArn
                ));
                
                //check Enabled string value, [key(String): value(String]
                if ($result && isset($result['Attributes']['Enabled'])) {
                    //check boolean of string value
                    $response = filter_var($result['Attributes']['Enabled'], FILTER_VALIDATE_BOOLEAN);
                }
            } catch (SnsException $e) {
                //catch error in attempt to delete endpoint, log error
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage()." || Arguments: ". func_get_arg(0));
            }
        }
        
        return $response;
    }
    
    /**
     * subscribe endpointArn to TopicArn
     * referenced from:  http://qiita.com/gomi_ningen/items/1002d256285c6d72c6ac
     * @param  String $endpointArn endpointArn upon creation of endpoint
     * @param  String $topic       topicArn to subscribe endpoints to
     * @return String $subscriptionArn  the ARN of the subscription
     */
    public function subscribeToTopic($endpointArn = null, $topic = null) {
        $subscriptionArn = null;

        //check necessary variables
        if (is_null($endpointArn) || is_null($topic) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $subscriptionArn;
        }
        
        //set parameters for subscription
        $params = array(
            'Endpoint' => $endpointArn,
            'Protocol' => 'application',
            'TopicArn' => $topic
        );

        try {
            $result = $this->sns->subscribe($params);
            $subscriptionArn = isset($result['SubscriptionArn']) ? $result['SubscriptionArn'] : null;
        } catch (SnsException $e) {
            //catch error in attempt to subscribe to topic, log error
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage()." || Arguments: ". json_encode(func_get_args()));
        }
        
        return $subscriptionArn;
    }

    /**
     * delete a subscription
     * @param  String $subscriptionArn the ARN of th subscription to be deleted
     * @return void     the result of this action is always null
     */
    public function unsubscribeFromTopic($subscriptionArn = null) {
        $result = false;

        //check necessary variables
        if (is_null($subscriptionArn) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return false;
        }

        try {
            //return is always empty
            $this->sns->unsubscribe(array('SubscriptionArn' => $subscriptionArn));
            //assume true anyway when no exception is thrown
            $result = true;

        } catch(SnsException $e) {
            //catch error in attempt to unsubscribe
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage()." || Arguments: ". func_get_arg(0));

            $result = false;
        }

        return $result;
    }

    /**
     * create a topic for notifications
     * @param  String $topicName the name of the topic you want to create
     * @return String $topicArn the ARN (Amazon Resource Name) assigned to the created topic
     */
    public function createTopic($topicName = null) {
        $topicArn = null;

        //check necessary variables
        if (is_null($topicName) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return $topicArn;
        }

        try {
            $result = $this->sns->createTopic(array('Name' => $topicName));
            $topicArn = isset($result['TopicArn']) ? $result['TopicArn'] : null;
        } catch(SnsException $e) {
            //catch error in attempt to unsubscribe
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". func_get_arg(0));
        }

        return $topicArn;
    }

    /**
     * deletes a topic and all its subscriptions
     * Note: deleting a topic might prevent some messages previously sent to the topic from being delivered to its subscribers
     * @param  String $topicArn the ARN of the topic you want to delete
     * @return void     the result of this action is always null
     */
    public function deleteTopic($topicArn = null) {
        //check necessary variables
        if (is_null($topicArn) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return $topicArn;
        }

        try {
            $this->sns->deleteTopic(array('TopicArn' => $topicArn));
            //log action
            $this->__log_debug("Deleted Topic: ". $topicArn);
        } catch(SnsException $e) {
            //catch error in attempt to delete topic
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". func_get_arg(0));
        }
    }

    /**
     * set the attributes of the topic with a new value
     * @param String $topicArn   the ARN of the topic to modify
     * @param array  $attributes the array of attributes to be set
     * @return void the result of this action is alwyas null
     */
    public function setTopicAttributes($topicArn = null, $attributes = array()) {
        //check necessary variables
        if (is_null($topicArn) || !is_array($attributes) || empty($attributes) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return;
        }

        //loop thru each attribute and set
        foreach ($attributes as $name => $value) {            
            try{
                $this->sns->setTopicAttributes(array(
                    'AttributeName' => $name,
                    'AttributeValue' => $value,
                    'TopicArn' => $topicArn
                ));

            } catch(SnsException $e) {
                //catch error in attempt to set attribute
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". json_encode(func_get_args()));
            }
        }
    }

    /**
     * returns all of the properties of a topic
     * Note: topic properties might differ based on authorization of user
     * @param  String $topicArn the ARN of the topic whose properties you want to get
     * @return array   the properties of the topic
     */
    public function getTopicAttributes($topicArn = null) {
        $attributes = array();

        //check necessary variables
        if (is_null($topicArn) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return $attributes;
        }

        try {
            $result = $this->sns->getTopicAttributes(array('TopicArn' => $topicArn));
            $attributes = isset($result['Attributes']) ? $result['Attributes'] : array();
        } catch(SnsException $e) {
            //catch error in attempt to get attribute
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". func_get_arg(0));
        }

        return $attributes;
    }

    public function listTopics($nextToken = null) {
        $result = false;
        //check necessary variables
        if (!$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". func_get_arg(0));
            return;
        }

        if ($nextToken) {
            try {
                $result = $this->sns->listTopics(array('NextToken' => $nextToken));
            } catch(SnsException $e) {
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". func_get_arg(0));
            }
        } else {
            try {
                $result = $this->sns->listTopics();
            } catch(SnsException $e) {
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". func_get_arg(0));
            }
        }

        if ($result){            
            $nextToken = isset($result['NextToken']) ? $result['NextToken'] : null;
            echo "NextToken: ".$nextToken."\n";
            if (isset($result['Topics'])) {
                print_r($result['Topics']);
            }
        } else {
            echo "No Result";
        }
    }

    public function listSubscriptionsByTopic($topicArn = null, $nextToken = null) {
        //check necessary variables
        if (is_null($topicArn) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return;
        }

        $params = array('TopicArn' => $topicArn);

        if ($nextToken) {
            $params['NextToken'] = $nextToken;
        }

        try {
            $result = $this->sns->listSubscriptionsByTopic($params);
        } catch(SnsException $e) {
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". json_encode(func_get_args()));
        }

        if ($result){            
            $nextToken = isset($result['NextToken']) ? $result['NextToken'] : null;
            echo "NextToken: ".$nextToken."\n";
            if (isset($result['Subscriptions'])) {
                print_r($result['Subscriptions']);
            }
        } else {
            echo "No Result";
        }
    } 

    /**
     * send push notification to topic
     * @param  String $topicArn the ARN of the topic you want to send notification
     * @param  String $platform the platform of the topic
     * @param  array  $payloads  array of platforms with their corresponding payload structures and data
     * @return Bool   value if the action was successful or not
     */
    public function publishToTopic($topicArn = null, $payloads = array()) {
        $result = false;

        //check necessary variables
        if (is_null($topicArn) || !is_array($payloads) || empty($payloads) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $result;
        }

        $message = array('default' => 'NativeCamp. Notification');

        //loop thru payloads array to prepare for multiple platform sending
        foreach ($payloads as $platform => $payload) {
            if (is_array($payload) && !empty($payload)) {
                if ($platform == 'APNS' || $platform == 'APNS_SANDBOX') {
                    //TODO: change to APNS on release
                    $platform = (Configure::read('ENVIRONMENT') === "PRODUCTION") ? 'APNS' : 'APNS_SANDBOX';
                    $message[$platform] = json_encode($payload);
                } else if ($platform == 'GCM') {
                    $message[$platform] = json_encode($payload);
                } else {
                    //do nothing
                }
            }
        }

        //check message array if there are platforms
        if (count($message) > 1) {
            try{
                $this->sns->publish(array(
                    'MessageStructure' => 'json',
                    'TopicArn' => $topicArn,
                    'Message' => json_encode($message)
                    ));
                $result = true;
            } catch(SnsException $e) {
                //log error
                $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". json_encode(func_get_args()));
            }
        } else {
            //log error
            $error = "No platforms to send.";
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
        }        

        return $result;
    }
    
    /**
     * send push notification to specific endpoint
     * @param  String $endpointArn 
     * @return Bool $result       success or failure of sending push notification
     */
    public function publishToEndpoint($endpointArn = null, $platform = null, $payload = array()) {
        $result = false;
        
        //check necessary variables
        if (is_null($endpointArn) || is_null($platform) || !is_array($payload) || empty($payload) || empty($payload[$platform]) || !$this->sns) {
            $error = 'Invalid arguments passed.';
            $result['error'] = $error;
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $result;
        }
        
        //send push notification
        try {
            $this->sns->publish(array(
                'MessageStructure' => 'json',
                'TargetArn' => $endpointArn,
                'Message' => json_encode(array(
                    'default' => 'NativeCamp Notification',
                    $platform => json_encode($payload[$platform])
                ))
            ));
            
            $result['success'] = true;
        } catch (SnsException $e) {
            //to check if success, awssns will throw exception if it fails
            //referenced from: http://docs.aws.amazon.com/aws-sdk-php/v2/guide/migration-guide.html#exceptions 
            //log error
            $result['error'] = $e->getMessage();
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage() ." || Arguments: ". json_encode(func_get_args()));
        }
        
        return $result;
    }
    
    /**
     * build payload for specific push notification type and platform
     * @param  String $platform mobile platform of push notificaiton such as APNS for iOS and etc.
     * @param  String $type     type of push notification such as for reserved lesson notification
     * @param  array  $custom   custom keys to be included into payload that is not included in standard payload format
     * @return array           payload to be sent
     */
    public function setupPayload($platform = null, $type = null, $custom = array()) {
        $payload = array();
        
        if (is_null($platform) || is_null($type) || !is_array($custom)) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $payload;
        }

        //init notification message
        $message = "";
        
        //get notification message according to push notification type
        if ($type == Configure::read('push_notification_type.reservation')) {
            $message = 'まもなく予約レッスンのお時間です。';
        } else if ($type == Configure::read('push_notification_type.cancelled_reservation')) {
            $message = '申し訳御座いませんが、予約レッスンがキャンセルされました。';
        } else if (isset($custom['message'])) {
            $message = $custom['message'];
        }
        
        //when no message, return
        if (empty($message)) {
            $error = 'Message is empty.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $payload;
        }
        
        //check platform
        if ($platform == 'APNS' || $platform == 'APNS_SANDBOX') {
            $standard = array(
                'alert' => $message,
                'sound' => 'default'
                // 'badge' =>  1               
            );
            
            //merge standard payload keys with custom keys
            $payloadContent = array_merge($standard, $custom);
            
            //setup payload for iOS
            $payload = array('aps' => $payloadContent);
        } else if ($platform == 'GCM') {
            //set up array for notification
            $notification = array(
                'title' => 'NativeCamp. ',
                'body' => $message
            );

            if (count($custom) > 0) {
                $payload['data'] = $custom;
            }

            $payload['notification'] = $notification;
        } else {
            //when platform does not match any
        }
        
        return $payload = array($platform => $payload);
        
    }
    
    /**
     * get push notification platform depending on device type passed
     */
    public function getPlatform($deviceType = null) {
        $platform = isset($this->platform[$deviceType]) ? $this->platform[$deviceType] : null;
        if ($platform == "APNS" || $platform == "APNS_SANDBOX") {
            $platform = (Configure::read('ENVIRONMENT') === "PRODUCTION") ? "APNS" : "APNS_SANDBOX";
        }
        return $platform;
    }
    
    /**
     * common function to send push notification according to type
     */
    public static function sendPushNotif($type = null, $deviceType = null, $endpoint = null, $custom = array()) {
        $result = false;
        $pn = new pushNotification();
        $platform = $pn->getPlatform($deviceType);
        
        if (is_null($type) || is_null($platform) || is_null($endpoint)) {
            $error = 'Invalid arguments passed.';
            $this->__log_error(__METHOD__, "Error: ". $error ." || Arguments: ". json_encode(func_get_args()));
            return $result;
        }
        
        try {
            $payload = $pn->setupPayload($platform, $type, $custom);
            $result = $pn->publishToEndpoint($endpoint, $platform, $payload);
            
        } catch (Exception $e) {            
            //log error
            $this->__log_error(__METHOD__, "Error: ". $e->getMessage());
            $this->__log_error(__METHOD__, "Details: ". isset($e->queryString) ? $e->queryString : null);
        }

        //update count
        if (!$result) {
            //update count for failed notifications
            $type = Configure::read('push_notification_type.failed');
        }
        self::updateNotificationCounter($type, $deviceType);
        
        return $result;
    }
    
    /*
     * update push notification counter in memcache
     */
    public static function updateNotificationCounter($type = null, $deviceType = null) {
        if (is_null($type)) {
            return;
        }

        $memcached = new myMemcached();
        $pushData = $memcached->get('PUSH_NOTIFICATION');

        //increment count by platform
        if ($deviceType == 1) {
            $pushData['IOS'] = isset($pushData['IOS']) ? $pushData['IOS'] + 1 : 0;
        } else if ($deviceType == 2) {
            $pushData['ANDROID'] = isset($pushData['ANDROID']) ? $pushData['ANDROID'] + 1 : 0;
        }

        //increment count by notification type
        if ($type == Configure::read('push_notification_type.reservation')) {
            $pushData['RESERVATION_COUNT'] = isset($pushData['RESERVATION_COUNT']) ? $pushData['RESERVATION_COUNT'] + 1 : 0;
        } else if ($type == Configure::read('push_notification_type.cancelled_reservation')) {
            $pushData['CANCELLED_COUNT'] = isset($pushData['CANCELLED_COUNT']) ? $pushData['CANCELLED_COUNT'] + 1 : 0;
        } else if ($type == Configure::read('push_notification_type.failed')) {
            $pushData['FAILED_COUNT'] = isset($pushData['FAILED_COUNT']) ? $pushData['FAILED_COUNT'] + 1 : 0;
        }
        
        $memcached->set(array(
            'key' => 'PUSH_NOTIFICATION',
            'value' => $pushData,
            'expire' => 604800 //1 week
        ));
    }
}
?>