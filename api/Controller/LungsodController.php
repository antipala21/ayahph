<?php
App::uses('AppController', 'Controller');
class LungsodController extends AppController {

	public $uses = array('Lungsod');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;
		$address = $this->Lungsod->find('list', array(
			'fields' => array(
				'Lungsod.search_key',
				'Lungsod.name'
			)
		));
		return json_encode($address);
	}
}