<?php
App::uses('AppController', 'Controller');
class NurseMaidDetailController extends AppController {

	public $uses = array(
		'Agency',
		'NurseMaid',
		'NurseMaidRating'
	);

	/************************************/
	/******** THIS IS USER SIDE *********/
	/************************************/

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('detail');
	}

	public function index () {

		$agency_id = isset($this->params['agency_id']) ? $this->params['agency_id'] : null;
		

		// trap if no id
		if (!isset($agency_id)) {
			return $this->redirect('/');
		}

		$this->Agency->virtualFields['total_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $agency_id";
		$this->Agency->virtualFields['male_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $agency_id AND `gender` = 1";
		$this->Agency->virtualFields['female_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $agency_id AND `gender` = 0";
		$this->Agency->virtualFields['current_available'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $agency_id AND `status` = 1";

		$agency = $this->Agency->find('first', array(
			'fields'=> array(
				'Agency.id',
				'Agency.name',
				'Agency.email',
				'Agency.address',
				'Agency.image_url',
				'Agency.description',
				'Agency.phone_number',
				'Agency.short_description',
				'Agency.representative_name',
				'Agency.total_nursemaid',
				'Agency.male_nursemaid',
				'Agency.female_nursemaid',
				'Agency.current_available',
			),
			'conditions' => array(
				'Agency.id' => $agency_id,
				'Agency.status' => 1,
				'Agency.display_flg' => 1
			)
		));

		$nurse_maids = $this->NurseMaid->find('all', array(
			'fields' => array(
				'NurseMaid.*'
			),
			'conditions' => array(
				'NurseMaid.agency_id' => $agency_id,
				'NurseMaid.status' => 1
			)
		));

		// trap if agency not found
		if (!isset($agency['Agency'])) {
			return $this->redirect('/');
		}

		$this->set('agency', $agency['Agency']);
		$this->set('nurse_maids', $nurse_maids);

	}

	public function detail () {
		$nursemaid_id = isset($this->params['nursemaid_id']) ? $this->params['nursemaid_id'] : null;

		$this->NurseMaid->virtualFields['rating'] = "SELECT AVG(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_id` = $nursemaid_id";

		$nurse_maid = $this->NurseMaid->find('first', array(
			'fields' => array(
				'NurseMaid.*',
				'Agency.id',
				'Agency.name',
				'Agency.address',
				'Agency.phone_number',
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = NurseMaid.agency_id'
				)
			),
			'conditions' => array(
				'NurseMaid.id' => $nursemaid_id,
				'NurseMaid.status' => 1
			)
		));

		// trap if agency not found
		if (empty($nurse_maid)) {
			return $this->redirect('/');
		}

		// comments
		$comments = $this->NurseMaidRating->find('all', array(
			'fields' => array(
				'NurseMaidRating.comment',
				'NurseMaidRating.created'
			),
			'conditions' => array('NurseMaidRating.nurse_maid_id' => $nursemaid_id)
		));

		$this->set('comments', $comments);
		$this->set('nurse_maid', isset($nurse_maid['NurseMaid']) ? $nurse_maid['NurseMaid'] : null);
		$this->set('agency', isset($nurse_maid['Agency']) ? $nurse_maid['Agency'] : null);

	}

}
