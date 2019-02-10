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

		if ($this->request->is('get')) {
			$get = array_map('trim',$this->request->query);
			foreach($get as $key => $val){
				$this->set($key,$val);
			}

			$this->set('sort_value', array_flip(Configure::read('sort_nursemaid')));
			$this->set('get',$get);
		}

		$order_key = Configure::read('sort_nursemaid');
		$order_by = 'id DESC';

		if (isset($get['order']) && !empty($get['order']) && in_array($get['order'], Configure::read('sort_nursemaid'))) {
			$order_by = $get['order'] . ' DESC';
		}

		$this->NurseMaid->virtualFields['rating'] = "SELECT AVG(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`nurse_maid_id` = `NurseMaid`.`id`";
		$this->NurseMaid->virtualFields['total_hire'] = "SELECT COUNT(*) FROM `transactions` WHERE `nurse_maid_id` = `NurseMaid`.`id`";

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
			'conditions' => array('NurseMaid.status'=> 1),
			'order' => $order_by
		));

		$this->set('nurse_maids', $nurse_maids);
	}

}
