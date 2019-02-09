<?php
App::uses('AppController', 'Controller');
class LessonNowtimeController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('nowtime'));
	}

	public function nowtime() {
		$this->viewClass = 'Json';
		$this->set('result', array('nowtime' => time()));
		$this->set('_serialize', 'result');
	}
}