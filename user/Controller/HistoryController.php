<?php
App::uses('AppController', 'Controller');
class HistoryController extends AppController {

	public $uses = array(
		'Transaction',
		'NurseMaidRating'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {

		$history = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'Agency.id',
				'Agency.name',
				'NurseMaid.id',
				'NurseMaid.first_name',
				'Rating.rate',
				'Rating.comment'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = Transaction.agency_id'
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
				'Transaction.user_id' => $this->Auth->user('id')
			),
			'order' => 'Transaction.id DESC'
		));

		$this->set('history', $history);
	}

	public function detail () {

		$id = isset($this->params['id']) ? $this->params['id'] : null;

		$history = $this->Transaction->find('first', array(
			'fields' => array(
				'Transaction.*',
				'Agency.id',
				'Agency.name',
				'NurseMaid.id',
				'NurseMaid.image_url',
				'NurseMaid.first_name',
				'NurseMaid.phone_number',
				'NurseMaid.address',
				'Rating.id',
				'Rating.rate',
				'Rating.comment'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = Transaction.agency_id'
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
				'Transaction.user_id' => $this->Auth->user('id'),
				'Transaction.id' => $id
			),
			'order' => 'Transaction.id DESC'
		));

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->NurseMaidRating->clear();
			$this->NurseMaidRating->read(array('rate', 'comment'), $data['Rating']['rating_id']);
			$this->NurseMaidRating->set(array(
				'rate' => $data['rate'],
				'comment' =>  $data['Rating']['comment']
			));
			$this->NurseMaidRating->save();
			return $this->redirect('detail/' . $id);
		}

		$this->set('value', $history);
	}

}
