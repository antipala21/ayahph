 <?php 
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UserCampaignController extends AppController{
	public $uses = array('User','CampaignSettings','SettlementCurrency');

	public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('index'));
        $this->autoRender = false;
    }
    public function index() {
	    $response = array();
	    $data = json_decode($this->request->input(),true);
	   
	    if($data){
			if (isset($data['users_api_token']) && empty($data['users_api_token'])) {
		        $response['error']['id'] = Configure::read('error.users_api_token_is_required');
		        $response['error']['message'] = __('users_api_token is required');
		    } else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
		        $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
		        $response['error']['message'] = __('users_api_token must be string');
		    } else if (isset($data['device_type']) && $data['device_type'] < 0) {
		    	$response['error']['id'] = Configure::read('error.user_device_type_is_required');
		    	$response['error']['message'] = __('device_type is required');
		    } 
		    else if(isset($data['currency']) && ($data['device_type'] < 4 && $data['device_type'] > 0)) {
		    	$api = new ApiCommonController();
				$device = $api->validateDeviceType($data['device_type']);
		    	$sc = strtolower($data['currency']);
	    		switch($data['device_type']) {
	    			case 1: 
	    				$dtype = 2; //Campaign Settings IOS
	    				break;
	    			case 2:
	    				$dtype = 3; //Campaign Settings Android
	    				break;
	    			case 3: 
	    				$dtype = 1; //Campaign Settings PC
	    				break;
	    		}
        		if(strcmp($sc,"jpy") == 0) {
        			$cc = 1;
        		} else if(strcmp($sc,"krw") == 0) {
        			$cc = 2;
        		} else if($strcmp($sc,"thb") == 0) {
        			$cc = 3;
        		} else {
        			$response['error']['message'] = "Invalid Currency Type";
        		}
        		$fields = array(
        		'CampaignSettings.promo_event_type',
        		'CampaignSettings.display_type',
        		'CampaignSettings.detail_information',
        		);
        		$osCondition[] = array('CampaignSettings.promo_os LIKE' => '%'. $dtype .'%');
        		$osCondition[] = array('CampaignSettings.promo_os LIKE' => '%'. 0 .'%');
        		$currencyCondition[] = array('CampaignSettings.promo_currency_type LIKE' => '%'. $cc .'%'); 
        		$currencyCondition[] = array('CampaignSettings.promo_currency_type LIKE' => '%'. 0 .'%'); 
        		$allCurrency = array('OR'=> $currencyCondition);
        		$allOs = array('OR' => $osCondition);
        		$conditions = array('AND'=> array(
        		'CampaignSettings.status_flag =' => 1,
        		'CampaignSettings.promo_start <=' => date('Y-m-d H:i'),
        		'CampaignSettings.promo_end >=' => date('Y-m-d H:i'),
        		));
        		array_push($conditions, $allOs);
        		array_push($conditions, $allCurrency);
        		$result = $this->CampaignSettings->find('all', array(
        		'fields' => $fields,
        		'conditions' => $conditions,
        		'order' => array('id'=>'ASC')
        		));
        		if($result){
		        	foreach($result as $rs) {
			        		$td = $rs['CampaignSettings']['detail_information'];
			        		$data = json_decode($td);
			        		$dtype = $rs['CampaignSettings']['display_type'];
				        	if($dtype == 4) {
				        			$response[] = array(
				        					'webview_url' => $data->webview_url,
				        					'type' => $rs['CampaignSettings']['display_type'],
				        					'event'=> $rs['CampaignSettings']['promo_event_type'],
				        			);
			        		}
		        	}
	        	} else {
	        		$response['error']['message'] = "No Campaign Retrieved";
	        	}
			        	 
		    }	
		    else {		  	
			  	 	$api = new ApiCommonController();
			        $user['User'] = $api->validateToken($data['users_api_token']);
			       	$device = $api->validateDeviceType($data['device_type']);
			        if ($user['User'] && $device) {
			        	switch($data['device_type']) {
			    			case 1: 
			    				$dtype = 2; //Campaign Settings IOS
			    				break;
			    			case 2:
			    				$dtype = 3; //Campaign Settings Android
			    				break;
			    			case 3: 
			    				$dtype = 1; //Campaign Settings PC
			    				break;
			    		}
			        	$fields = array(
			        		'CampaignSettings.promo_event_type',
			        		'CampaignSettings.display_type',
			        		'CampaignSettings.detail_information',
			        	);
			        	$osCondition[] = array('CampaignSettings.promo_os LIKE' => '%'. $dtype .'%');
			        	$osCondition[] =array('CampaignSettings.promo_os LIKE' => '%'. 0 .'%');
        				$allOs = array('OR', $osCondition);
			        	$conditions = array('AND'=> array(
			        		'CampaignSettings.status_flag =' => 1,
			        		'CampaignSettings.promo_start <=' => date('Y-m-d H:i'),
			        		'CampaignSettings.promo_end >=' => date('Y-m-d H:i'),
			        	));
			        	$result = $this->CampaignSettings->find('all', array(
			        		'fields' => $fields,
			        		'conditions' => $conditions,
			        		'order' => array('id'=>'ASC')
			        	));
			        	if($result){
				        	foreach($result as $rs) {
				        		$td = $rs['CampaignSettings']['detail_information'];
				        		$data = json_decode($td);
				        		$dtype = $rs['CampaignSettings']['display_type'];
					        	if($dtype == 4) {
					        			$response[] = array(
					        					'webview_url' => $data->webview_url,
					        					'type' => $rs['CampaignSettings']['display_type'],
					        					'event'=> $rs['CampaignSettings']['promo_event_type'],
					        			);
				        		}	
				        	}
			        	} else {
				        		 $response['error']['message'] = "No Campaign retreived";
				        	}

			        }	
			}
		return json_encode($response, true);
		}
	}
}
