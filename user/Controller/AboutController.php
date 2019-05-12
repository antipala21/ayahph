<?php
App::uses('AppController', 'Controller');
class AboutController extends AppController {

	// https://www.formget.com/upload-multiple-images-using-php-and-jquery/

	public $uses = array('User');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			'index'
		);
	}

	public function index () {
	}

}
