<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::import('Helper', 'Xml');
App::uses('ApiCommonController', 'Controller');
class VersionController extends AppController {
	
	public $uses = array('DeviceToken', 'Maintenance', 'CountryCode');
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('show'));
	}
	
	/**
	 * Retrieve App version 
	 * @return boolean
	 */
	public function show(){
		
		$version = '';
		$force = '';
		
		//initialize Api validation controller
		$Validate = new ApiCommonController();
		$this->autoRender = false;
						
		$data = @json_decode($this->request->input(), true);
		if ($this->request->is('post')) {
			if (!isset($data) || 
				empty($data) || 
				!isset($data['device_type'])
			) {
				return false;
			}
			
			if (!$Validate->validateDeviceType($data['device_type'])) {
				return false;
			}
			
			// this will retrieve and read the version xml file	in the directory
			if (file_exists('../version.xml')) {
				$filexml = file_get_contents('../version.xml');
				$xml = simplexml_load_string($filexml); // load xml to simplexmlelement object
				if (empty($xml))  return false;
			} else {
				return false;
			}
			
			$user = (isset($data['users_api_token']) && $data['users_api_token']) ? $Validate->validateToken($data['users_api_token']) : null;
			if (isset($user) && $user) {
				if ( strlen($user['native_language2']) > 0 ) {
					$version_description = ( $user['native_language2'] != 'ja') ? 'version-description-'.$user['native_language2'] : 'version-description' ;
				} else {
					$version_description = 'version-description' ;
				}
			} elseif ( isset($data['user_language']) && strlen($data['user_language']) > 0 ) {
				if ($data['user_language'] == "ja") {
					$version_description = 'version-description';
				} else {
					$version_description = !in_array( $data['user_language'], array("ko","th") ) ? "version-description-en" : 'version-description-'.$data['user_language'] ;
				}
			} else {
				$version_description = 'version-description';
			}
				
			if ($data['device_type'] == 1) {
				if (!isset($xml->{'ios'})) return false;
				$version = (string) $xml->{'ios'}->version; // IOS version
				$force = (int) $xml->{'ios'}->{'force-update'};
				$version_description = (string) $xml->{'ios'}->{$version_description};
				$store_url = (string) $xml->{'ios'}->{'store-url'};
			} elseif ($data['device_type'] == 2) {
				if (!isset($xml->{'android'})) return false;
				$version = (string) $xml->{'android'}->version; // ANDROID version 
				$force = (int) $xml->{'android'}->{'force-update'};
				$version_description = (string) $xml->{'android'}->{$version_description};
				$store_url = (string) $xml->{'android'}->{'store-url'};
			}
			
			if (!$version && !$force) return false;
			
			//Response version and force update for mobile
			$json['Version'] =  $version;
			$json['force'] = $force;
			//check if maintenance
			$maintenance = $this->Maintenance->find('first',array(
						'conditions' => array(
							'Maintenance.start_date <=' => date('Y-m-d H:i:s'),
							'Maintenance.end_date >' => date('Y-m-d H:i:s')
						),
						'fields' => array(
							'is_active',
							'start_date',
							'end_date'
						)
					)
				);
			if(!empty($maintenance)) {
				$json['is_maintenance'] = ((int)$maintenance['Maintenance']['is_active']) ? true : false;
				if($json['is_maintenance']) {
					$json['start_date'] = $maintenance['Maintenance']['start_date'];
					$json['end_date'] = $maintenance['Maintenance']['end_date'];
				}
			} else {
				$json['is_maintenance'] = false;
			}
			$json['version_description'] = $version_description;
			$json['store_url'] = $store_url;
			return json_encode($json);
		}
		
	}
	
	
}