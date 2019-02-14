<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array(
		'NurseMaid',
		'Lungsod'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
	}

	public function index() {

		if ($this->request->is('get')) {
			$get = array_map('trim',$this->request->query);
			foreach($get as $key => $val){
				$this->set($key,$val);
			}

			$this->set('sort_value', array_flip(Configure::read('sort_nursemaid')));
			$this->set('get', $get);
		}

		$order_by = 'id DESC';
		$conditions = array('NurseMaid.status' => 1);

		if (isset($get['order']) && !empty($get['order']) && in_array($get['order'], Configure::read('sort_nursemaid'))) {
			$order_by = $get['order'] . ' DESC';
		}

		if (isset($get['filter']) && !empty($get['filter'])) {

			switch ($get['filter']) {
				case 'age_1':
					$conditions['DATE(NurseMaid.birthdate) >='] = $this->birthday(19);
					break;
				case 'age_2':
					$conditions['DATE(NurseMaid.birthdate) <='] = $this->birthday(20);
					break;
				case 'single':
					$conditions['NurseMaid.marital_status'] = 0;
					break;
				case 'married':
					$conditions['NurseMaid.marital_status'] = 1;
					break;
				case 'female':
					$conditions['NurseMaid.gender'] = 0;
					break;
				case 'male':
					$conditions['NurseMaid.gender'] = 1;
					break;
				default:
					break;
			}
		}

		if (isset($get['address']) && !empty($get['address'])) {
			$conditions['NurseMaid.address_key'] = strtolower(str_replace(array(' ', '-', '/'), '_', $get['address']));
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
			'conditions' => $conditions,
			'order' => $order_by
		));

		$this->set('nurse_maids', $nurse_maids);

		$address = $this->Lungsod->find('list', array(
			'fields' => array(
				'Lungsod.search_key',
				'Lungsod.name'
			)
		));
		$this->set('address', $address);
	}

	private function birthday($years){
		return date('Y-m-d', strtotime($years . ' years ago'));
	}

}
