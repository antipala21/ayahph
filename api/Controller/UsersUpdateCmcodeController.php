<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersUpdateCmcodeController extends AppController {

	public $uses = array(
		'User', 
		'CampaignMaster',
		'CmcodeUrl'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
		$this->autoRender = false;
	}

	public function index() {
		//save parameters
		$this->CmcodeUrl->saveParameter(array(
			'url' => $_SERVER['QUERY_STRING'],
			'server_name' => $_SERVER['SERVER_NAME'],
			'controller' => $this->params['controller']
		));	

		$idfa = isset($_GET['idfa']) ? $_GET['idfa'] : null;
		$adid = isset($_GET['adid']) ? $_GET['adid'] : null;
		//cm code
		$tracker_id = isset($_GET['tracker_id']) ? $_GET['tracker_id'] : null;
		//tracker
		$tracker = isset($_GET['tracker']) ? $_GET['tracker'] : null;
		//default result
		$response = array('success' => "success!");

		//assign adid to idfa
		if (!$idfa && $adid) {
			$idfa = $adid;
		}

		//check if both parameter exist
		if ($idfa && ($tracker_id || $tracker)) {
			$cc = null;
			if ($tracker) {
				//fetch campaign code using tracker
				$cc = $this->CampaignMaster->find('first', array(
					'fields' => 'CampaignMaster.id',
					'conditions' => array(
						'CampaignMaster.tracker LIKE' => $tracker,
						'CampaignMaster.status = 1'
					),
					'recursive' => -1
				));
			}
			//use tracker_id
			if (!$cc && $tracker_id) {
				//fetch campaign code using tracker
				$cc = $this->CampaignMaster->find('first', array(
					'fields' => 'CampaignMaster.id',
					'conditions' => array(
						'CampaignMaster.title LIKE' => $tracker_id,
						'CampaignMaster.status = 1'
					),
					'recursive' => -1
				));				
			}
			//set cmcode
			$updateData = array();
			//check campaign_code
			if (isset($cc['CampaignMaster']['id'])) {
				$updateData = array('campaign_id' => $cc['CampaignMaster']['id']);
			} elseif (empty($_GET['idfa'])) {
				//android
				$updateData['campaign_id'] = 326;
			} else {
				//ios
				$updateData['campaign_id'] = 126;					
			}
			//search user by idfa
			$userUpdateCmcode = $this->User->useReplica()->find('first', array(
				'conditions' => array('User.idfa' => $idfa),
			  	'fields'=>array('User.id'),
			  	'order' => array('User.created' => 'desc'),
			  	'recursive' => -1
			));
			if ($userUpdateCmcode) {
				//update cmcode
				$this->User->recursive = -1;
				$this->User->validate = false;
				$this->User->read(null, $userUpdateCmcode['User']['id']);
	 			$this->User->set($updateData);
	 			if (!$this->User->save()) {
	 				return json_encode(array('error' => __('saving campaign_id failed!')));
	 			}	 	
			} else {
			 	return json_encode(array('error' => __("no user has such idfa ...")));
			}
		} else {
			//validate
			if (!$idfa && !$tracker_id) {
				$response['error'] = __("no idfa and tracker_id");
			}else if (!$idfa) {
				$response['error'] = __("no idfa");
			}else if (!$tracker_id) {
				$response['error'] = __("no tracker_id");
			}
		}
		return json_encode($response);
	}
}
