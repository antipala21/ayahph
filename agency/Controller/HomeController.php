<?php
App::uses('AppController', 'Controller');
class HomeController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('index');
	}

	public function index(){
		// exit('adf');
		// $data = $this->Auth->user('id');
		// $data = $this->Session->read('Auth.User');
		// myTools::display($data);
		// exit;
	}

}
