<?php
App::uses('AppController', 'Controller');
class NurseMaidRequestController extends AppController {

	public $uses = array(
		'Agency',
		'NurseMaid',
		'NurseMaidRating',
		'Lungsod'
	);

	/************************************/
	/******** THIS IS USER SIDE *********/
	/************************************/

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index () {
		
	}

}
