<?php
App::uses('AppController', 'Controller');
class NurseMaidRequestController extends AppController {

	public $uses = array(
		'Agency',
		'NurseMaid',
		'NurseMaidRating',
		'Lungsod'
	);

	/************************************/
	/******** THIS IS USER SIDE *********/
	/************************************/

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index () {

		$agency_id = isset($this->params['agency_id']) ? $this->params['agency_id'] : null;
		$nurse_maids = array();

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$data['NurseMaidRequest']['agency_id'] = $agency_id;
			$conditions = $this->getConditions($data['NurseMaidRequest']);

			$nurse_maids = $this->NurseMaid->find('all', array(
				'fields' => array(
					'NurseMaid.*'
				),
				'limit' => 4,
				'conditions' => $conditions
			));

			if (empty($nurse_maids)) {
				$nurse_maids = $this->NurseMaid->find('all', array(
					'fields' => array(
						'NurseMaid.*'
					),
					'limit' => 4,
					'conditions' =>  array(
						'NurseMaid.agency_id' => $agency_id,
						'NurseMaid.status' => 1
					)
				));
			}
		}

		$address = $this->Lungsod->find('list', array(
			'fields' => array(
				'Lungsod.name',
				'Lungsod.name'
			)
		));
		$this->set('agency_id', $agency_id);
		$this->set('address', $address);
		$this->set('nurse_maids', $nurse_maids);
	}

	private function getConditions ($params = array()) {

		$conditions = array(
			'NurseMaid.agency_id' => $params['agency_id'],
			'NurseMaid.status' => 1
		);

		if (isset($params['gender'])) {
			$conditions['NurseMaid.gender'] = $params['gender'];
		}

		if (isset($params['marital_status'])) {
			$conditions['NurseMaid.marital_status'] = $params['marital_status'];
		}

		if (isset($params['years_experience'])) {
			if ($params['years_experience'] == 0) {
				$conditions['NurseMaid.years_experience <='] = 1;
			} else {
				$conditions['NurseMaid.years_experience >'] = 1;
			}
		}

		if (isset($params['education'])) {
			$conditions['NurseMaid.education'] = $params['education'];
		}

		if (isset($params['address'])) {
			$conditions['NurseMaid.address'] = $params['address'];
		}

		if (isset($params['skills']) && !empty($params['skills'])) {
			$conditions['NurseMaid.skills LIKE'] = "%" . $params['skills'] . "%";
		}

		if (isset($params['jobs_experience']) && !empty($params['jobs_experience'])) {
			$conditions['NurseMaid.jobs_experience LIKE'] = "%" . $params['jobs_experience'] . "%";
		}

		return $conditions;

	}

}
