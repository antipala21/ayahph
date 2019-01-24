<?php
App::uses('AppController', 'Controller');
class TransactionController extends AppController {

	public $uses = array(
		'Agency',
		'User',
		'Admin',
		'Transaction'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){

		$transactions = $this->Transaction->find('all');
		$this->set('transactions', $transactions);

	}

}
