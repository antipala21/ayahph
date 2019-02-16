<?php
App::uses('AppController', 'Controller');
class AccountController extends AppController {

	// https://www.formget.com/upload-multiple-images-using-php-and-jquery/

	public $uses = array('User');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
		$this->Auth->allow(
			'index',
			'update',
			'update_image'
		);
	}

	public function index () {
		$this->autoRender = false;

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$this->User->virtualFields['total_transaction'] = "SELECT COUNT(*) FROM `transactions` WHERE `user_id` = `User`.`id`";
		$user = $this->User->find('first', array(
			'conditions' => array('User.id' => $data['user_id'])
		));

		$response = array();

		if ($user) {
			$response = $user['User'];
		}
		return json_encode($response);
	}

	public function update () {
		$this->autoRender = false;

		if (!isset($this->request->data['params'])) {
			$response['success'] = false;
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$this->log('[Account data] ' . json_encode($data), 'debug');

		$response['success'] = false;

		if (!$data) {
			$response['error']['message'] = 'Invalid request';
		}
		else if (!isset($data['user_id']) || empty($data['user_id'])) {
			$response['error']['message'] = 'User id is required';
		} else {
			unset($data['image_url']);
			$this->User->clear();
			$this->User->read(array(
				'display_name',
				'fname',
				'lname',
				'phone_number',
				'address'
			), $data['user_id']);
			$this->User->set($data);
			if ($this->User->save()) {
				$response['success'] = true;
				$response['Success'] = "Success";
			}
		}
		return json_encode($response);
	}

	public function update_image () {
		$this->autoRender = false;

		$response['success'] = false;

		$request_shit = $this->request->data;

		// file directory
		$fileDir = ROOT.'/user/webroot/images/';
		// create folder if not exist
		if (!is_dir($fileDir)) {
			mkdir($fileDir);
		}

		try {
			$decodedImage = base64_decode($request_shit['image']);
			file_put_contents($fileDir . $request_shit['user_id'] . '_profile' . ".jpg", $decodedImage);
			$response['success'] = true;
			return json_encode($response);
		} catch (Exception $e) {}
		return json_encode($response);
	}

}
