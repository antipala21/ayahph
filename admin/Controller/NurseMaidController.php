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
				'Agency.id',
				'Agency.name',
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = NurseMaid.agency_id'
				)
			)
		));

		$this->set('nurse_maids', $nurse_maids);
	}

	public function detail () {
		$id = isset($this->params['id']) ? $this->params['id'] : null;
		if (!$id) {
			return $this->redirect('/admin/nursemaids');
		}
		$this->NurseMaid->virtualFields['rating'] = "SELECT AVG(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`nurse_maid_id` = `NurseMaid`.`id`";
		$nurse_maid = $this->NurseMaid->find('first', array(
			'fields' => array(
				'NurseMaid.*',
				'Agency.id',
				'Agency.name',
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
				'NurseMaid.id' => $id
			)
		));

		if ($this->request->is('post')) {
			$data = $this->request->data;
			$new_status = isset($data['data_value']) ? $data['data_value'] : 0;
			$this->NurseMaid->clear();
			$this->NurseMaid->read(array('status'), $id);
			$this->NurseMaid->set(array('status' => $new_status));
			$this->NurseMaid->save();
			$nurse_maid['NurseMaid']['status'] = $new_status;
		}

		$this->set('nurse_maid', $nurse_maid);

	}

}
