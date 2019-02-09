<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::uses('myMemcached', 'Lib');

class CheckUniqueTimeController extends AppController {
	public $uses = array(
		'User',
		'UsersLoginHistory'
	);
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('index');
    }

    public function index() {
        $this->autoRender = false;

        $data = json_decode($this->request->input(),true);

        $apiCommon = new ApiCommonController();

        $devices = array(1, 2, 3);
        $devices_name = array(
        	'1' => 'ACTIVE_USER_PC',
        	'2' => 'ACTIVE_USER_iOS',
        	'3' => 'ACTIVE_USER_Android'
        );

        /* if no users_api_token, device_type found and device_type invalid */
        if (empty($data) || 
        	!isset($data['users_api_token']) || empty($data['users_api_token']) ||
        	!isset($data['device_type']) || empty($data['device_type']) ||
        	!in_array($data['device_type'], $devices)) { 
        	return; 
        }

        $user = $apiCommon->validateToken($data['users_api_token']);

        /* if invalid users_api_token */
        if (empty($user)) { return; }

        $device_no = $data['device_type'];

        // add user to memcached array
        $memcached = new myMemcached();
        $memcached->set(array(
        	'key' => $devices_name[$device_no].'_'.$user['id'],
        	'value' => $user['nickname'],
        	'expire' => 1800
        ));

		// update last_login_time of user
		$this->User->validate = array();
		$this->User->clear();
		$this->User->read(array('last_login_time'), $user['id']);
		$this->User->set(array('last_login_time' => myTools::myDate()));
		$this->User->save();

		// log login
		$this->UsersLoginHistory->create();
		$this->UsersLoginHistory->set(array(
			'user_id' => $user['id'],
			'login_time' => myTools::myDate()
		));
		$this->UsersLoginHistory->save();

		//log ip
		IpLogTable::add($user['id'],2);
    }
}