<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array(
		'NurseMaid'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;
		$response = array();

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
			'conditions' => array('NurseMaid.status' => 1)
		));

		if ($nurse_maids) {

			$_nurse_maids = array();
			foreach ($nurse_maids as $key => $value) {
				$_nurse_maids[] = $value['NurseMaid'];
				if (isset($value['Agency']['name'])) {
					$_nurse_maids[$key]['agency_name'] = $value['Agency']['name'];
				}
			}
			$response['nurse_maids'] = $_nurse_maids;
		}
		return json_encode($response);
	}

}
