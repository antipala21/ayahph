<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array(
		'NurseMaid'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index', 'detail');
	}

	public function index() {
		$this->autoRender = false;
		$response = array();

		$nursemaid_filter_key = Configure::read('nursemaid_filter_key');
		$sort_nursemaid = Configure::read('sort_nursemaid');
		$filter_address = Configure::read('filter_address');

		$data = null;
		if (isset($this->request->data['params'])) {
			$request_data = json_decode(stripslashes($this->request->data['params']));
			$data = (array) $request_data;
		}

		$order_by = 'id DESC';
		$conditions = array('NurseMaid.status' => 1);

		if (isset($data['order']) && !empty($data['order'])) {
			if ($data['order'] == 'Years experience') {
				$data['order'] = 'Years of experience';
			}
			$_order = $sort_nursemaid[$data['order']];
			$order_by = $_order . ' DESC';
		}

		if (isset($data['filter']) && !empty($data['filter'])) {
			$_filter = $nursemaid_filter_key[$data['filter']];
			switch ($_filter) {
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

		if (isset($data['address']) && !empty($data['address'])) {
			$_address = $filter_address[$data['address']];
			$conditions['NurseMaid.address_key'] = strtolower(str_replace(array(' ', '-', '/'), '_', $data['address']));
		}

		$this->NurseMaid->virtualFields['rating'] = "SELECT ROUND(AVG(`rate`), 2) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`nurse_maid_id` = `NurseMaid`.`id`";
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

	public function detail () {

		$this->autoRender = false;
		$response = array();

		$data = null;
		if (isset($this->request->data['params'])) {
			$request_data = json_decode(stripslashes($this->request->data['params']));
			$data = (array) $request_data;
		}

		if (!$data) {
			$response['success'] = false;
			return json_encode($response);
		}

		$nursemaid_id = isset($data['nursemaid_id']) ? $data['nursemaid_id'] : NULL;

		$this->NurseMaid->virtualFields['rating'] = "SELECT ROUND(AVG(`rate`), 2) FROM `nurse_maid_ratings` WHERE `nurse_maid_id` = $nursemaid_id";
		$this->NurseMaid->virtualFields['agency_name'] = "SELECT `name` FROM `agencies` WHERE `id` = `NurseMaid`.`agency_id`";
		$nurse_maid = $this->NurseMaid->find('first', array(
			'fields' => array(
				'NurseMaid.*'
			),
			'conditions' => array(
				'NurseMaid.id' => $nursemaid_id,
				'NurseMaid.status' => 1
			)
		));

		if ($nurse_maid) {
			$response['nurse_maid'] = $nurse_maid['NurseMaid'];
		}

		return json_encode($response['nurse_maid']);

	}

	private function birthday($years){
		return date('Y-m-d', strtotime($years . ' years ago'));
	}

}
