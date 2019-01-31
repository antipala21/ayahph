<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array(
		'NurseMaid'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index() {

		$this->NurseMaid->virtualFields['rating'] = "SELECT AVG(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`nurse_maid_id` = `NurseMaid`.`id`";

		$nurse_maids = $this->NurseMaid->find('all', array(
			'fields' => array(
				'NurseMaid.*',
				'Agency.name'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = NurseMaid.agency_id'
				)
			),
			'conditions' => array('NurseMaid.status'=> 1)
		));

		$this->set('nurse_maids', $nurse_maids);
	}

}
