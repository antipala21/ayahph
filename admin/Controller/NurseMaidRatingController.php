<?php
App::uses('AppController', 'Controller');
class NurseMaidRatingController extends AppController {

	public $uses = array(
		'NurseMaidRating'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){

		$nursemaid_ratings = $this->NurseMaidRating->find('all', array(
			'fields' => array(
				'NurseMaidRating.*',
				'User.id',
				'User.display_name',
				'Agency.id',
				'Agency.name',
				'Nursemaid.id',
				'Nursemaid.first_name'
			),
			'joins' => array(
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => 'User.id = NurseMaidRating.user_id'
				),
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = NurseMaidRating.agency_id'
				),
				array(
					'table' => 'nurse_maids',
					'alias' => 'Nursemaid',
					'type' => 'LEFT',
					'conditions' => 'Nursemaid.id = NurseMaidRating.nurse_maid_id'
				)
			)
		));
		$this->set('nursemaid_ratings', $nursemaid_ratings);
	}

	public function detail () {
		$id = isset($this->params['id']) ? $this->params['id'] : null;
		$nursemaid_rating = $this->NurseMaidRating->find('first', array(
			'fields' => array(
				'NurseMaidRating.*',
				'User.id',
				'User.display_name',
				'Agency.id',
				'Agency.name',
				'Nursemaid.id',
				'Nursemaid.first_name'
			),
			'joins' => array(
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => 'User.id = NurseMaidRating.user_id'
				),
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = NurseMaidRating.agency_id'
				),
				array(
					'table' => 'nurse_maids',
					'alias' => 'Nursemaid',
					'type' => 'LEFT',
					'conditions' => 'Nursemaid.id = NurseMaidRating.nurse_maid_id'
				)
			),
			'conditions' => array('NurseMaidRating.id' => $id)
		));
		$this->set('nursemaid_rating', $nursemaid_rating);

		if ($this->request->is('post')) {
			$data = $this->request->data;

			if (isset($data['data_value']) && $data['data_value'] == 'delete') {
				$this->NurseMaidRating->delete($id);
				return $this->redirect('/nursemaid_ratings');
			} else {
				$data_value = isset($data['data_value']) ? $data['data_value'] : 1;
				$this->NurseMaidRating->clear();
				$this->NurseMaidRating->read(array('status', 'display_flg'), $id);
				$this->NurseMaidRating->set(array('status' => $data_value));
				if ($this->NurseMaidRating->save()) {
					return $this->redirect('/nursemaid_ratings');
				} else {
					return $this->redirect('/nursemaid_ratings');
				}
			}
		}

	}

}
