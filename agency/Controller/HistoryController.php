<?php
App::uses('AppController', 'Controller');
class HistoryController extends AppController {

	public $uses = array(
		'Transaction',
		'NurseMaidRating',
		'User'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {

		$history = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'User.id',
				'User.display_name',
				'NurseMaid.id',
				'NurseMaid.first_name',
				'Rating.rate',
				'Rating.comment'
			),
			'joins' => array(
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => 'User.id = Transaction.user_id'
				),
				array(
					'table' => 'nurse_maids',
					'alias' => 'NurseMaid',
					'type' => 'LEFT',
					'conditions' => 'NurseMaid.id = Transaction.nurse_maid_id'
				),
				array(
					'table' => 'nurse_maid_ratings',
					'alias' => 'Rating',
					'type' => 'LEFT',
					'conditions' => 'Rating.transaction_id = Transaction.id'
				)
			),
			'conditions' => array(
				'Transaction.status' => 3,
				'Transaction.agency_id' => $this->Auth->user('id')
			),
			'order' => 'Transaction.id DESC'
		));

		$this->set('history', $history);
	}

}
